<?php
/**
 * Period (of time) Value
 * This data type differs from the "duration" data type in that it
 * specifies the exact start and end time, whereas duration only specifies
 * the amount of time.
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo I'm wondering if maybe I should make a qCal_Date_Span class
 * 
 * Value Name: PERIOD
 * 
 * Purpose: This value type is used to identify values that contain a
 * precise period of time.
 * 
 * Formal Definition: The data type is defined by the following
 * notation:
 * 
 *   period     = period-explicit / period-start
 * 
 *   period-explicit = date-time "/" date-time
 *   ; [ISO 8601] complete representation basic format for a period of
 *   ; time consisting of a start and end. The start MUST be before the
 *   ; end.
 * 
 *   period-start = date-time "/" dur-value
 *   ; [ISO 8601] complete representation basic format for a period of
 *   ; time consisting of a start and positive duration of time.
 * 
 * Description: If the property permits, multiple "period" values are
 * specified by a COMMA character (US-ASCII decimal 44) separated list
 * of values. There are two forms of a period of time. First, a period
 * of time is identified by its start and its end. This format is
 * expressed as the [ISO 8601] complete representation, basic format for
 * "DATE-TIME" start of the period, followed by a SOLIDUS character
 * (US-ASCII decimal 47), followed by the "DATE-TIME" of the end of the
 * period. The start of the period MUST be before the end of the period.
 * Second, a period of time can also be defined by a start and a
 * positive duration of time. The format is expressed as the [ISO 8601]
 * complete representation, basic format for the "DATE-TIME" start of
 * 
 * the period, followed by a SOLIDUS character (US-ASCII decimal 47),
 * followed by the [ISO 8601] basic format for "DURATION" of the period.
 * 
 * Example: The period starting at 18:00:00 UTC, on January 1, 1997 and
 * ending at 07:00:00 UTC on January 2, 1997 would be:
 * 
 *   19970101T180000Z/19970102T070000Z
 * 
 * The period start at 18:00:00 on January 1, 1997 and lasting 5 hours
 * and 30 minutes would be:
 * 
 *   19970101T180000Z/PT5H30M
 * 
 * No additional content value encoding (i.e., BACKSLASH character
 * encoding) is defined for this value type.
 */
class qCal_Value_Period extends qCal_Value {

	protected $value;
	/**
	 * Cast a string value into a qCal_DateTime_Period object
	 */
	protected function doCast($value) {
	
		$parts = explode("/", $value);
		if (count($parts) !== 2) {
			throw new qCal_DateTime_Exception_InvalidPeriod("A period must contain a start date and either an end date, or a duration of time.");
		}
		$start = qCal_DateTime::factory($parts[0]);
		try {
			$end = qCal_DateTime::factory($parts[1]);
		} catch (qCal_DateTime_Exception $e) { // @todo This should probably be a more specific exception
			// invalid date, so try duration
			// @todo: I might want to create a qCal_Date object to represent a duration (not tied to any points in time)
			// using a qCal_Value object here is sort of inconsistent. Plus, I can see value in having that functionality
			// within the qCal_Date subcomponent
			// also, there is a difference in a period and a duration in that if you say start on feb 26 and end on march 2
			// that will be a different "duration" depending on the year. that goes for months with alternate amounts of days too
			$duration = new qCal_DateTime_Duration($parts[1]);
			$end = qCal_DateTime::factory($start->getUnixTimestamp() + $duration->getSeconds()); // @todo This needs to be updated once qCal_DateTime accepts timestamps 
		}
		return new qCal_DateTime_Period($start, $end);
	
	}
	/**
	 * Convert to string - this converts to string into the UTC/UTC format
	 */
	protected function toString($value) {
	
		return $value->getStart()->getUtc() . "/"
				 . $value->getEnd()->getUtc();
	
	}

}