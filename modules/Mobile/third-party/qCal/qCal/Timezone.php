<?php
class qCal_Timezone {

	protected $format = "e";
	
	protected $name;
	
	protected $offsetSeconds;
	
	protected $abbreviation;
	
	protected $isDaylightSavings;
	
	protected $formatArray = array();
	
	protected static $timezones = array();
	
	/**
	 * Class constructor
	 * A timezone must have a name, offset (in seconds), and optionsally an abbreviation. Daylight savings defaults to false.
	 * @todo When $abbreviation isn't specified, and $name is a valid pre-defined PHP timezone identifier, use its
	 * 		 corresponding abbreviation rather than the name itself
	 * @todo When $offset isn't provided and $name is a valid timezone, use its corresponding offset, but if $name is not
	 * 		 a valid timezone identifier and no offset is provided, throw an exception
	 */
	public function __construct($name, $offset, $abbreviation = null, $daylightsavings = null) {
	
		$this->setName($name);
		$this->setOffsetSeconds($offset);
		if (is_null($abbreviation)) $abbreviation = $name;
		$this->setAbbreviation($abbreviation);
		$this->setIsDaylightSavings($daylightsavings);
		$this->formatArray = array(
			'e' => $this->getName(),
			'I' => (integer) $this->isDaylightSavings(),
			'O' => $this->getOffsetHours(),
			'P' => $this->getOffset(),
			'T' => $this->getAbbreviation(),
			'Z' => $this->getOffsetSeconds(),
		);
	
	}
	public function setName($name) {
	
		$this->name = (string) $name;
	
	}
	public function setOffsetSeconds($offset) {
	
		$this->offsetSeconds = (integer) $offset;
	
	}
	public function setAbbreviation($abbreviation) {
	
		$this->abbreviation = (string) $abbreviation;
	
	}
	public function setIsDaylightSavings($daylightSavings = null) {
	
		$this->isDaylightSavings = (boolean) $daylightSavings;
	
	}
	/**
	 * Generate a timezone from either an array of parameters, or a timezone
	 * name such as "America/Los_Angeles".
	 * @link http://php.net/manual/en/timezones.php A directory of valid timezones
	 * @todo This method is FUGLY. Rewrite it and make it make sense. This is sort of nonsensical.
	 */
	public static function factory($timezone = null) {
	
		if (is_array($timezone)) {
			// remove anything irrelevant
			$vals = array_intersect_key($timezone, array_flip(array('name','offsetSeconds','abbreviation','isDaylightSavings')));
			if (!array_key_exists("name", $vals)) {
				// @todo throw an exception or something
			}
			if (!array_key_exists("offsetSeconds", $vals)) {
				// @todo throw an exception or something
			}
			$name = $vals['name'];
			$offsetSeconds = $vals['offsetSeconds'];
			$abbreviation = (array_key_exists('abbreviation', $vals)) ? $vals['abbreviation'] : null;
			$isDaylightSavings = (array_key_exists('isDaylightSavings', $vals)) ? $vals['isDaylightSavings'] : null;
			$timezone = new qCal_Timezone($name, $offsetSeconds, $abbreviation, $isDaylightSavings);
		} else {
			// get the timezone information out of the string
			$defaultTz = date_default_timezone_get();
			
			if (is_null($timezone)) $timezone = $defaultTz;
			
			// if the timezone being set is invalid, we will get a PHP notice, so error is suppressed here
			// @todo It would be more clean and probably more efficient to use php's error handling to throw an exception here...
			if (is_string($timezone)) {
				@date_default_timezone_set($timezone);
				// if the function above didn't work, this will be true
				if (date_default_timezone_get() != $timezone) {
					// if the timezone requested is registered, use it
					if (array_key_exists($timezone, self::$timezones)) {
						$timezone = self::$timezones[$timezone];
					} else {
						// otherwise, throw an exception
						throw new qCal_DateTime_Exception_InvalidTimezone("'$timezone' is not a valid timezone.");
					}
				} else {
					// if the timezone specified was a valid (native php) timezone, use it
					$name = date("e");
					$offset = date("Z");
					$abbr = date("T");
					$ds = date("I");
					$timezone = new qCal_Timezone($name, $offset, $abbr, $ds);
				}
			}
			
			// now set it back to what it was...
			date_default_timezone_set($defaultTz);
		}
		return $timezone;
	
	}
	
	public static function register(qCal_Timezone $timezone) {
	
		self::$timezones[$timezone->getName()] = $timezone;
	
	}
	
	public static function unregister($timezone) {
	
		unset(self::$timezones[(string) $timezone]);
	
	}
	
	public function getName() {
	
		return $this->name;
	
	}
	
	public function getOffset() {
	
		$seconds = $this->getOffsetSeconds();
		$negpos = "+";
		if ($seconds < 0) {
			$negpos = "-";
		}
		$hours = (integer) ($seconds / 60 / 60);
		$minutes = $hours * 60;
		$minutes = ($seconds / 60) - $minutes;
		return sprintf("%s%02d:%02d", $negpos, abs($hours), abs($minutes));
	
	}
	
	public function getOffsetHours() {
	
		$seconds = $this->getOffsetSeconds();
		$negpos = "+";
		if ($seconds < 0) {
			$negpos = "-";
		}
		$hours = (integer) ($seconds / 60 / 60);
		$minutes = $hours * 60;
		$minutes = ($seconds / 60) - $minutes;
		return sprintf("%s%02d%02d", $negpos, abs($hours), abs($minutes));
	
	}
	
	public function getOffsetSeconds() {
	
		return $this->offsetSeconds;
	
	}
	
	public function getAbbreviation() {
	
		return $this->abbreviation;
	
	}
	
	public function isDaylightSavings() {
	
		return $this->isDaylightSavings;
	
	}
	
	/**
	 * Set the format that should be used when calling either __toString() or format() without an argument.
	 * @param string $format
	 */
	public function setFormat($format) {
	
		$this->format = (string) $format;
		return $this;
	
	}
	
	public function format($format) {
	
		$escape = false;
		$meta = str_split($format);
		$output = array();
		foreach($meta as $char) {
			if ($char == '\\') {
				$escape = true;
				continue;
			}
			if (!$escape && array_key_exists($char, $this->formatArray)) {
				$output[] = $this->formatArray[$char];
			} else {
				$output[] = $char;
			}
			// reset this to false after every iteration that wasn't "continued"
			$escape = false;
		}
		return implode($output);
	
	}
	
	public function __toString() {
	
		return $this->format($this->format);
	
	}

}