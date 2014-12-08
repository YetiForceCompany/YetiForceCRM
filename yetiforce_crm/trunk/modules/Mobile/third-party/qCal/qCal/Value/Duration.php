<?php
/**
 * Duration (of time) Value
 * This data type differs from "period" in that it does not specify start
 * and end time, just the duration (5 weeks, 1 day, etc)
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo I'm wondering if maybe I should make a qCal_Date_Span class
 * 
 * Value Name: DURATION
 * 
 * Purpose: This value type is used to identify properties that contain
 * a duration of time.
 * 
 * Formal Definition: The value type is defined by the following
 * notation:
 * 
 *  dur-value  = (["+"] / "-") "P" (dur-date / dur-time / dur-week)
 * 
 *  dur-date   = dur-day [dur-time]
 *  dur-time   = "T" (dur-hour / dur-minute / dur-second)
 *  dur-week   = 1*DIGIT "W"
 *  dur-hour   = 1*DIGIT "H" [dur-minute]
 *  dur-minute = 1*DIGIT "M" [dur-second]
 *  dur-second = 1*DIGIT "S"
 *  dur-day    = 1*DIGIT "D"
 * 
 * Description: If the property permits, multiple "duration" values are
 * specified by a COMMA character (US-ASCII decimal 44) separated list
 * of values. The format is expressed as the [ISO 8601] basic format for
 * the duration of time. The format can represent durations in terms of
 * weeks, days, hours, minutes, and seconds.
 * 
 * No additional content value encoding (i.e., BACKSLASH character
 * encoding) are defined for this value type.
 * 
 * Example: A duration of 15 days, 5 hours and 20 seconds would be:
 * 
 *  P15DT5H0M20S
 * 
 * A duration of 7 weeks would be:
 * 
 *  P7W
 */
class qCal_Value_Duration extends qCal_Value {

	/**
	 * Convert seconds to duration 
	 * @todo Some type of caching? This probably doesn't need to be "calculated" every time if it hasnt changed
	 */
	protected function toString($value) {
	
		return $value->toICal();
	
	}
	/**
	 * Convert to internal representation
	 */
	protected function doCast($value) {
	
		return new qCal_DateTime_Duration($value);
	
	}

}