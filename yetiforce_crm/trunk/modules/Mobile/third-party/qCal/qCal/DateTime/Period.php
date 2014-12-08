<?php
/**
 * Date period object - rather than a point in time, this object represents a PERIOD of time. So, 
 * it consists of a start and end point in time
 * 
 * @package qCal
 * @subpackage qCal_Date
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 */
class qCal_DateTime_Period {

	/**
	 * Start and end date/times
	 */
	protected $start, $end;
	/**
	 * Constructor
	 */
	public function __construct($start, $end) {
	
		if (!($start instanceof qCal_DateTime)) {
			$start = qCal_DateTime::factory($start);
		}
		if (!($end instanceof qCal_DateTime)) {
			$end = qCal_DateTime::factory($end);
		}
		$this->start = $start;
		$this->end = $end;
		if ($this->getSeconds() < 0) {
			throw new qCal_DateTime_Exception_InvalidPeriod("The start date must come before the end date.");
		}
	
	}
	/**
	 * Converts to how many seconds between the two. because this is the smallest increment
	 * used in this class, seconds are used to determine other increments
	 */
	public function getSeconds() {
	
		return $this->end->getUnixTimestamp() - $this->start->getUnixTimestamp();
	
	}
	/**
	 * Returns start date
	 */
	public function getStart() {
	
		return $this->start;
	
	}
	/**
	 * Returns end date
	 */
	public function getEnd() {
	
		return $this->end;
	
	}

}