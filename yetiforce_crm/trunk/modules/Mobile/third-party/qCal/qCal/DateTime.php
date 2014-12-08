<?php
/**
 * qCal_DateTime
 * 
 * In order to perform all the complex date/time based math and logic required to
 * implement the iCalendar spec, we need a complex date/time class. This class represents
 * a specific point in time, including the time. Internally it makes use of qCal_Date and
 * qCal_Time. If only a date or only a time needs to be represented, then one of those
 * classes should be used.
 * 
 * @package qCal_Date
 * @
 */
class qCal_DateTime {

	/**
	 * @var qCal_Date An object that represents the date
	 */
	protected $date;
	/**
	 * @var qCal_Time An object that represents the time
	 */
	protected $time;
	/**
	 * @var string The default string representation of datetime is a direct
	 * correlation to the date function's "c" metacharacter
	 */
	protected $format = "Y-m-d\TH:i:sP";
	/**
	 * Class constructor
	 * @todo Make this default to "now"
	 * @todo It is possible that the timezone could put the date back (or forward?) a day. This does not account for that
	 */
	public function __construct($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null, $timezone = null, $rollover = null) {
	
		$date = new qCal_Date($year, $month, $day, $rollover);
		$time = new qCal_Time($hour, $minute, $second, $timezone, $rollover);
		$this->setDate($date);
		$this->setTime($time);
	
	}
	/**
	 * Generate a datetime object via string
	 * @todo Should this accept qCal_Date and qCal_DateTime objects?
	 */
	public static function factory($datetime, $timezone = null) {
	
		if (is_null($timezone) || !($timezone instanceof qCal_Timezone)) {
			// @todo Make sure this doesn't cause any issues 
			// detect if we're working with a UTC string like "19970101T180000Z", where the Z means use UTC time
			if (strtolower(substr($datetime, -1)) == "z") {
				$timezone = "UTC";
			}
			$timezone = qCal_Timezone::factory($timezone);
		}
		// get the default timezone so we can set it back to it later
		$tz = date_default_timezone_get();
		// set the timezone to GMT temporarily
		date_default_timezone_set("GMT");
		
		// handles unix timestamp
		if (is_integer($datetime) || ctype_digit((string) $datetime)) {
			$timestamp = $datetime;
		} else {
			// handles just about any string representation of date/time (strtotime)
			if (is_string($datetime) || empty($datetime)) {
				if (!$timestamp = strtotime($datetime)) {
					// if unix timestamp can't be created throw an exception
					throw new qCal_DateTime_Exception("Invalid or ambiguous date/time string passed to qCal_DateTime::factory()");
				}
			}
		}
		
		if (!isset($timestamp)) {
			throw new qCal_DateTime_Exception("Could not generate a qCal_DateTime object.");
		}
		
		list($year, $month, $day, $hour, $minute, $second) = explode("|", gmdate("Y|m|d|H|i|s", $timestamp));
		
		// set the timezone back to what it was
		date_default_timezone_set($tz);
		
		return new qCal_DateTime($year, $month, $day, $hour, $minute, $second, $timezone);
	
	}
	/**
	 * Set the date component
	 */
	protected function setDate(qCal_Date $date) {
	
		$this->date = $date;
	
	}
	/**
	 * Set the time component
	 */
	protected function setTime(qCal_Time $time) {
	
		$this->time = $time;
	
	}
	/**
	 * Get time portion as object
	 */
	public function getTime() {
	
		return $this->time;
	
	}
	/**
	 * Get date portion as object
	 */
	public function getDate() {
	
		return $this->date;
	
	}
	/**
	 * Get unix timestamp
	 */
	public function getUnixTimestamp($useOffset = true) {
	
		return $this->date->getUnixTimestamp() + $this->time->getTimestamp($useOffset);
	
	}
	/**
	 * Set the format to use when outputting as a string
	 */
	public function setFormat($format) {
	
		$this->format = (string) $format;
		return $this;
	
	}
	/**
	 * Format the date/time using PHP's date() function's meta-characters
	 * @todo It's obvious I need to find a better solution to formatting since I have repeated this method
	 * in three classes now...
	 */
	public function format($format) {
	
		$escape = false;
		$meta = str_split($format);
		$output = array();
		foreach($meta as $char) {
			if ($char == '\\') {
				$escape = true;
				continue;
			}
			if (!$escape && $this->convertChar($char) != $char) {
				$output[] = $this->convertChar($char);
			} else {
				$output[] = $char;
			}
			// reset this to false after every iteration that wasn't "continued"
			$escape = false;
		}
		return implode($output);
	
	}
	/**
	 * convert character
	 */
	protected function convertChar($char) {
	
		$char = $this->date->format($char);
		$char = $this->time->format($char);
		$char = $this->time->getTimezone()->format($char);
		return $char;
	
	}
	/**
	 * Output date/time as string
	 */
	public function __toString() {
	
		return $this->format($this->format);
	
	}
	/**
	 * Get date/time as UTC
	 */
	public function getUtc($humanReadable = false) {
	
		if ($humanReadable) return gmdate('Y-m-d', $this->date->getUnixTimestamp()) . gmdate('\TH:i:s\Z', $this->time->getTimestamp());
		else return gmdate('Ymd', $this->date->getUnixTimestamp()) . gmdate('\THis\Z', $this->time->getTimestamp());
	
	}

}