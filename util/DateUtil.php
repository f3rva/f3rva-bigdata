<?php
namespace F3\Util;

if (!defined('__ROOT__')) {
	define('__ROOT__', dirname(dirname(dirname(__FILE__))));
}

/**
 * Utility class for date functions.
 *
 * @author bbischoff
 */
class DateUtil {
	
	public static function getDefaultDate($date) {
		return self::getDefaultDateSubtractInterval($date, 'P0M');
	}
	
	public static function getDefaultDateSubtractInterval($date, $dateInterval) {
		self::defaultTimezone();
		
		$dateStr = $date;
		
		if (empty($dateStr)) {
			$newDate = new \DateTime();
			$newDate->sub(new \DateInterval($dateInterval));
			$dateStr = $newDate->format('Y-m-d');
		}
		
		return $dateStr;
	}
	
	public static function subtractInterval($date, $dateInterval) {
		self::defaultTimezone();
		
		$newDate = \DateTime::createFromFormat('Y-m-d', $date);
		$newDate->sub(new \DateInterval($dateInterval));
		$dateStr = $newDate->format('Y-m-d');
		
		return $dateStr;
	}
	
	public static function defaultTimezone() {
		date_default_timezone_set('America/New_York');
	}
}
