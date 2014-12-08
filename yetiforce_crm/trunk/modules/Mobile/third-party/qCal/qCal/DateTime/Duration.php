<?php
/**
 * Date duration
 * 
 * @package qCal
 * @subpackage qCal_DateTime
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 */
class qCal_DateTime_Duration {

	// an array of how manys seconds are in a minute, hour, day, etc.
	// IMPORTANT - don't change the order of these
	protected $durations = array ('W' => 604800, 'D' => 86400, 'H' => 3600, 'M' => 60, 'S' => 1);
	/**
	 * Duration in seconds
	 */
	protected $duration;
	/**
	 * If this is negative, this will be a minus symbol. Positive doesn't need a sign, so it is just null
	 */
	protected $sign;
	/**
	 * Constructor
	 */
	public function __construct($duration = null) {
	
		$this->setDuration($duration);
	
	}
	/**
	 * Set duration - accepts an integer (amount of seconds) or an icalendar-formatted duration string
	 */
	public function setDuration($duration) {
	
		$duration = strtoupper($duration);
		// if plus or minus precedes number, remove it set in class
		if (preg_match("/^[+-]/", (string) $duration, $matches)) {
			if ($matches[0] == "-") $this->sign = "-";
			$duration = str_split($duration);
			array_shift($duration);
			$duration = implode("", $duration);
		}
		if (ctype_digit($duration)) {
			$this->duration = $duration;
		} else {
			// convert value to duration in seconds
			preg_match('/^P([0-9]+[W])?([0-9]+[D])?T?([0-9]+[H])?([0-9]+[M])?([0-9]+[S])?$/i', $duration, $matches);
			// remove first element (which is just entire the matched string)
			array_shift($matches);
			$seconds = 0;
			foreach ($matches as $duration) {
				if (empty($duration)) continue;
				$seconds += $this->calculateSeconds($duration);
			}
			$this->duration = $seconds;
		}
		return $this;
	
	}
	/**
	 * Pass in a string like "15W" or "1D" and this will return how many seconds are in it
	 */
	protected function calculateSeconds($duration) {
	
		$amnt = preg_replace("/[^0-9]/i", "", $duration);
		$inc = preg_replace("/[^A-Z]/i", "", $duration);
		return $this->durations[$inc] * $amnt;
	
	}
	/**
	 * Converts seconds to an icalendar-formatted duration string
	 */
	public function toICal() {
	
		$total = $this->duration;
		$return = "P";
		// this is why order is important when defining $this->durations
		foreach ($this->durations as $dur => $amnt) {
			// how many "weeks" are in the value?
			$quotient = (int) ($total / $amnt);
			// get the remainder of the division
			$remainder = $total - ($quotient*$amnt);
			// now if we got a whole number as quotient, add this duration to the return string
			if ($quotient) {
				// if this is the first "time" duration, add the required T char
				if ($dur == "H" || $dur == "M" || $dur == "S") {
					if (!strpos($return, "T")) $return .= "T";
				}
				$return .= $quotient . $dur;
			}
			$total = $remainder;
		}
		return $this->sign . $return;
	
	}
	/**
	 * @todo Should this be the string representation? I dont really know.
	 */
	public function __toString() {
	
		return $this->toICal();
	
	}
	/**
	 * Get duration in seconds
	 */
	public function getSeconds() {
	
		return (integer) $this->sign . $this->duration;
	
	}

}