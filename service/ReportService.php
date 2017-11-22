<?php
namespace F3\Service;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require_once(__ROOT__ . '/model/ChartData.php');
require_once(__ROOT__ . '/model/DayOfWeek.php');
require_once(__ROOT__ . '/model/Summary.php');
require_once(__ROOT__ . '/repo/MemberRepo.php');
require_once(__ROOT__ . '/repo/WorkoutRepo.php');
require_once(__ROOT__ . '/util/DateUtil.php');

use F3\Model\ChartData;
use F3\Model\DayOfWeek;
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
	
	public function getPAXAttendance($startDate, $endDate) {
		$paxTotals = $this->memberRepo->findPAXAttendance($startDate, $endDate);
		
		$totalsArray = array();
		
		foreach ($paxTotals as $total) {
			$summary = new Summary();
			$summary->setValue($total['COUNT']);
			$summary->setId($total['MEMBER_ID']);
			$summary->setDescription($total['F3_NAME']);
			
			array_push($totalsArray, $summary);
		}
		
		return $totalsArray;
	}
	
	public function getQTotals($startDate, $endDate) {
		$paxTotals = $this->memberRepo->findQTotals($startDate, $endDate);
		
		$totalsArray = array();
		
		foreach ($paxTotals as $total) {
			$summary = new Summary();
			$summary->setValue($total['COUNT']);
			$summary->setId($total['MEMBER_ID']);
			$summary->setDescription($total['F3_NAME']);
			
			array_push($totalsArray, $summary);
		}
		
		return $totalsArray;
	}
}

?>