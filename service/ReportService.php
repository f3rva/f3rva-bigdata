<?php
namespace F3\Service;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}
require_once(__ROOT__ . '/model/DayOfWeek.php');
require_once(__ROOT__ . '/model/Summary.php');
require_once(__ROOT__ . '/repo/WorkoutRepo.php');
require_once(__ROOT__ . '/service/MemberService.php');

use F3\Model\DayOfWeek;
use F3\Model\Summary;
use F3\Repo\WorkoutRepository;
use F3\Service\MemberService;

/**
 * Service class for all reporting.
 * 
 * @author bbischoff
 */
class ReportService {
	private $workoutRepo;

	public function __construct() {
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
			
			array_push($aoArray, $summary);
		}
		
		return $aoArray;
	}
	
	public function getDefaultDate($date) {
		date_default_timezone_set('America/New_York');
		
		$dateDefault = $date;
		if (empty($date)) {
			$now = new \DateTime();
			$dateDefault = $now->format('Y-m-d');
		}
		
		return $dateDefault;
	}

	public function getDefaultDateSubtractInterval($date, $dateInterval) {
		date_default_timezone_set('America/New_York');
		
		$dateDefault = $date;
		if (empty($date)) {
			$now = new \DateTime();
			$now->sub(new \DateInterval($dateInterval));
			$dateDefault = $now->format('Y-m-d');
		}
		
		return $dateDefault;
	}
}

?>