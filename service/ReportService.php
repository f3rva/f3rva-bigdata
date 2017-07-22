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

use F3\Model\ChartData;
use F3\Model\DayOfWeek;
use F3\Model\Summary;
use F3\Repo\MemberRepository;
use F3\Repo\WorkoutRepository;

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
		$this->defaultTimezone();
		
		$chartData = new ChartData();
		$labels = array();
		$series = array();
		
		// lookup the AO name
		$ao = $this->workoutRepo->findAo($aoId);
		array_push($labels, $ao['DESCRIPTION']);
		
		foreach (array_reverse($workouts) as $workout) {
			$dateArray = array();
			$date = new \DateTime($workout->getWorkoutDate());
			array_push($dateArray, $date->format("'n/j'"));
			array_push($dateArray, $workout->getPaxCount());
			array_push($series, $dateArray);
		}
		
		$chartData->setXLabels($labels);
		$chartData->setSeries($series);
		
		return $chartData;
	}
	
	public function getMemberDetailChartData($qWorkouts, $paxWorkouts) {
		$this->defaultTimezone();
		
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
	
	public function getWorkoutCountsChartData($startDate, $endDate) {
		$workoutCounts = $this->workoutRepo->findCount($startDate, $endDate);
		
		// loop over results to get a set of AOs
		$aos = array();
		foreach ($workoutCounts as $count) {
			$aoId = $count['AO_ID'];
			if (!empty($aoId)) {
				$aos[$aoId] = $count['AO'];
			}
		}
		// sort by name
		asort($aos);
				
		// create our chart data
		$chartData = new ChartData();
		$chartData->setXLabels($aos);
		
		// build our labels as a sequence of days from start to end
		$dates = array();
		$start = new \DateTime($startDate);
		$end = new \DateTime($endDate);
		$interval = new \DateInterval('P1D');
		while ($start <= $end) {
			$dateStr = $start->format('n/j');
			$dates[$dateStr] = $start;
			$start->add($interval);
		}
		
		// create a table with rows as the days and columns as the AO numbers
		$series = array_fill_keys(array_keys($dates), null);
		foreach ($series as $key => $value) {
			$series[$key] = array($key => "'" . $key . "'") + array_fill_keys(array_keys($aos), 'null');
		}
		
		foreach ($workoutCounts as $count) {
			$aoId = $count['AO_ID'];
			$paxCount = $count['PAX_COUNT'];
			$workoutDate = date_parse($count['WORKOUT_DATE']);
			$date = $workoutDate['month'] . '/' . $workoutDate['day'];
			
			if (!empty($aoId)) {
				$series[$date][$aoId] = $paxCount;
			}
		}
		
		$chartData->setSeries($series);
		
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
	
	public function getDefaultDate($date) {
		return $this->getDefaultDateSubtractInterval($date, 'P0M');
	}

	public function getDefaultDateSubtractInterval($date, $dateInterval) {
		$this->defaultTimezone();
		
		$dateDefault = $date;
		if (empty($date)) {
			$now = new \DateTime();
			$now->sub(new \DateInterval($dateInterval));
			$dateDefault = $now->format('Y-m-d');
		}
		
		return $dateDefault;
	}
	
	private function defaultTimezone() {
		date_default_timezone_set('America/New_York');
	}
}

?>