<?php
namespace F3\Service;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require_once(__ROOT__ . '/model/ChartData.php');
require_once(__ROOT__ . '/model/DayOfWeek.php');
require_once(__ROOT__ . '/model/MemberStats.php');
require_once(__ROOT__ . '/model/Summary.php');
require_once(__ROOT__ . '/repo/MemberRepo.php');
require_once(__ROOT__ . '/repo/WorkoutRepo.php');
require_once(__ROOT__ . '/util/DateUtil.php');

use F3\Model\ChartData;
use F3\Model\DayOfWeek;
use F3\Model\MemberStats;
use F3\Model\Summary;
use F3\Repo\MemberRepository;
use F3\Repo\WorkoutRepository;
use F3\Util\DateUtil;

/**
 * Service class for all reporting.
 * 
 * @author bbischoff
 */
class ReportService {
	private $memberRepo;
	private $workoutRepo;

	public function __construct() {
		$this->memberRepo = new MemberRepository();
		$this->workoutRepo = new WorkoutRepository();
	}

	/**
	 * Retrieves all workouts by day of week
	 * 
	 * @return array of DayOfWeek
	 */
	public function getWorkoutsByDayOfWeek($startDate, $endDate) {
		$daysOfWeek = $this->workoutRepo->findWorkoutsGroupByDayOfWeek($startDate, $endDate);
		
		$daysArray = array();
		
		foreach ($daysOfWeek as $dayOfWeek) {
			$dayOfWeekObj = new DayOfWeek();
			$dayOfWeekObj->setCount($dayOfWeek['PAX_COUNT']);
			$dayOfWeekObj->setDayId($dayOfWeek['DAY_ID']);
			
			array_push($daysArray, $dayOfWeekObj);
		}
		
		return $daysArray;
	}

	/**
	 * Gets the streaking PAX members for an AO
	 * 
	 * @return array of Member
	 */
	public function getStreakingPAXMembersByAO($aoId) {
		$numMonths = 6;
		$recentWorkoutAttendees = $this->workoutRepo->findRecentWorkoutAttendeesByAO($aoId, $numMonths);

		// loop through the list of members, keeping track of members from the most recent workout
		// for each member that was in the most recent workout, recursively
		$streakers = array();
		$workouts = array();

		$mostRecentWorkout = '';
		if (count($recentWorkoutAttendees) > 0) {
			$mostRecentWorkout = $recentWorkoutAttendees[0]['WORKOUT_ID'];
		}

		foreach ($recentWorkoutAttendees as $attendee) {
			$workoutId = $attendee['WORKOUT_ID'];
			$workoutDate = $attendee['WORKOUT_DATE'];
			$memberId = $attendee['MEMBER_ID'];
			$pax = $attendee['PAX'];

			// if the workout array doesn't have this workoutId as a key, add it and initialize an array
			if (!array_key_exists($workoutId, $workouts)) {
				$workouts[$workoutId] = array();
			}
			$workouts[$workoutId][$memberId] = $pax;

			// if current workout is the most recent workout, add to the streakers array
			if ($workoutId == $mostRecentWorkout) {
				$summary = new Summary();
				$summary->setId($memberId);
				$summary->setValue(1);
				$summary->setDescription($pax);
				$streakers[$memberId] = $summary;
			}
		}

		// loop through all streakers and recursively check workouts array to see if the member id exists
		// in the next workout
		foreach ($streakers as $memberId => $streaker) {
			$streaker->setValue($this->getStreakingPAX($memberId, array_values($workouts), $streaker->getValue()));
		}

		// sort the $streakers array by the value in the Summary object
		usort($streakers, function($a, $b) {
			return $b->getValue() - $a->getValue();
		});

		return $streakers;
	}

	private function getStreakingPAX($memberId, $workouts, $streak) {
		// if we ran out of workouts, return the current streak
		if (count($workouts) <= $streak) {
			return $streak;
		}

		// if the member exists in the next workout, increment the streak and recursively call the function
		if (array_key_exists($memberId, $workouts[$streak])) {
			$streak++;
			return $this->getStreakingPAX($memberId, $workouts, $streak);
		}

		return $streak;
	}
	
	/**
	 * Gets the average attendance by AO
	 *
	 * @return array of Member
	 */
	public function getAverageAttendanceByAO($startDate, $endDate) {
		$aoAverages = $this->workoutRepo->findAverageAttendanceByAO($startDate, $endDate);
		
		$aoArray = array();
		
		foreach ($aoAverages as $aoAverage) {
			$summary = new Summary();
			$summary->setValue($aoAverage['AVERAGE']);
			$summary->setId($aoAverage['AO_ID']);
			$summary->setDescription($aoAverage['DESCRIPTION']);
			
			$aoArray[$aoAverage['AO_ID']] = $summary;
		}
		
		return $aoArray;
	}
	
	public function getTopQsByAO($aoId, $count, $offset) {
		$topQs = $this->workoutRepo->findTopQsByAO($aoId, $count, $offset);
		
		$qArray = array();
		
		foreach ($topQs as $top) {
			$summary = new Summary();
			$summary->setValue($top['Q_COUNT']);
			$summary->setDescription($top['Q']);
			
			$qArray[] = $summary;
		}
		
		return $qArray;
	}

	public function getTopPAXByAO($aoId, $count, $offset) {
		$topPax = $this->workoutRepo->findTopPAXByAO($aoId, $count, $offset);
		
		$paxArray = array();
		
		foreach ($topPax as $top) {
			$summary = new Summary();
			$summary->setValue($top['PAX_COUNT']);
			$summary->setDescription($top['PAX']);
			
			$paxArray[] = $summary;
		}
		
		return $paxArray;
	}

	public function getAoDetailChartData($aoId, $workouts) {
		DateUtil::defaultTimezone();
		
		$chartData = new ChartData();
		$labels = array();
		$series = array();
		
		// lookup the AO name
		$ao = $this->workoutRepo->findAo($aoId);
		
		foreach (array_reverse($workouts) as $workout) {
			$dateArray = array();
			$rawDate = new \DateTime($workout->getWorkoutDate());
			$year = $rawDate->format("Y");
			$labels[$year] = $year;
			$day = $rawDate->format("'m/d'");
			
			// if the day doesn't exist, create a new array to store multiple years
			if (!array_key_exists($day, $series)) {
				$series[$day] = array();
			}

			$series[$day][$year] = $workout->getPaxCount();
		}
		
		// fill the data with nulls as necessary
		foreach ($series as $key=>$day) {
			foreach ($labels as $year) {
				if (!array_key_exists($year, $day)) {
					$series[$key][$year] = 'null';
				}
			}
			// must sort so that they are represented in numerical order
			ksort($series[$key]);
		}
		ksort($series);

		$chartData->setXLabels($labels);
		$chartData->setSeries($series);
		
		return $chartData;
	}
	
	public function getMemberDetailChartData($qWorkouts, $paxWorkouts) {
		DateUtil::defaultTimezone();
		
		$chartData = new ChartData();
		$labels = array();
		$series = array();
		
		// this needs to be refactored.  Not very efficient
		foreach ($qWorkouts as $workout) {
			foreach ($workout->getAo() as $aoId => $ao) {
				if (is_null($series[$aoId])) {
					$series[$aoId] = array($ao, 0, 0);
				}
				
				$series[$aoId][1] = $series[$aoId][1] + 1;
			}
		}
		foreach ($paxWorkouts as $workout) {
			foreach ($workout->getAo() as $aoId => $ao) {
				if (is_null($series[$aoId])) {
					$series[$aoId] = array($ao, 0, 0);
				}
				
				$series[$aoId][2] = $series[$aoId][2] + 1;
			}
		}
		
		$chartData->setSeries(array_values($series));
		
		return $chartData;
	}
	
	public function getAttendanceCounts($startDate, $endDate, $order) {
		$paxTotals = $this->memberRepo->findAttendanceCounts($startDate, $endDate, $order);
		
		$totalsArray = array();
		
		foreach ($paxTotals as $total) {
			$stats = new MemberStats();
			$stats->setMemberId($total['MEMBER_ID']);
			$stats->setMemberName($total['F3_NAME']);
			$stats->setNumWorkouts($total['WORKOUT_COUNT']);
			$stats->setNumQs($total['Q_COUNT']);
			$stats->setQRatio($total['Q_RATIO']);
			
			$totalsArray[$stats->getMemberId()] = $stats;
		}
		
		return $totalsArray;
	}
}
