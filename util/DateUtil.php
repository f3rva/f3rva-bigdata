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
		
		$newDate = null;
		if (empty($date)) {
			$newDate = new \DateTime();
			$newDate->sub(new \DateInterval($dateInterval));
		}
		else {
			$newDate = \DateTime::createFromFormat('Y-m-d', $date);
		}

		$dateStr = $newDate->format('Y-m-d');
		
		return $dateStr;
	}
	
	public static function defaultTimezone() {
		date_default_timezone_set('America/New_York');
	}
}

?>