<?php
/**
 * Time Value
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * Value Name: TIME
 * 
 * Purpose: This value type is used to identify values that contain a
 * time of day.
 * 
 * Formal Definition: The data type is defined by the following
 * notation:
 * 
 *  time               = time-hour time-minute time-second [time-utc]
 * 
 *  time-hour          = 2DIGIT        ;00-23
 *  time-minute        = 2DIGIT        ;00-59
 *  time-second        = 2DIGIT        ;00-60
 *  ;The "60" value is used to account for "leap" seconds.
 * 
 *  time-utc   = "Z"
 * 
 * Description: If the property permits, multiple "time" values are
 * specified by a COMMA character (US-ASCII decimal 44) separated list
 * of values. No additional content value encoding (i.e., BACKSLASH
 * character encoding) is defined for this value type.
 * 
 * The "TIME" data type is used to identify values that contain a time
 * of day. The format is based on the [ISO 8601] complete
 * representation, basic format for a time of day. The text format
 * consists of a two-digit 24-hour of the day (i.e., values 0-23), two-
 * digit minute in the hour (i.e., values 0-59), and two-digit seconds
 * in the minute (i.e., values 0-60). The seconds value of 60 MUST only
 * to be used to account for "leap" seconds. Fractions of a second are
 * not supported by this format.
 * 
 * In parallel to the "DATE-TIME" definition above, the "TIME" data type
 * expresses time values in three forms:
 * 
 * The form of time with UTC offset MUST NOT be used. For example, the
 * following is NOT VALID for a time value:
 * 
 *  230000-0800        ;Invalid time format
 * 
 * FORM #1 LOCAL TIME
 * 
 * The local time form is simply a time value that does not contain the
 * UTC designator nor does it reference a time zone. For example, 11:00
 * PM:
 * 
 *  230000
 * 
 * Time values of this type are said to be "floating" and are not bound
 * to any time zone in particular. They are used to represent the same
 * hour, minute, and second value regardless of which time zone is
 * currently being observed. For example, an event can be defined that
 * indicates that an individual will be busy from 11:00 AM to 1:00 PM
 * every day, no matter which time zone the person is in. In these
 * cases, a local time can be specified. The recipient of an iCalendar
 * object with a property value consisting of a local time, without any
 * relative time zone information, SHOULD interpret the value as being
 * fixed to whatever time zone the ATTENDEE is in at any given moment.
 * This means that two ATTENDEEs may participate in the same event at
 * different UTC times; floating time SHOULD only be used where that is
 * reasonable behavior.
 * 
 * In most cases, a fixed time is desired. To properly communicate a
 * fixed time in a property value, either UTC time or local time with
 * time zone reference MUST be specified.
 * 
 * The use of local time in a TIME value without the TZID property
 * parameter is to be interpreted as a local time value, regardless of
 * the existence of "VTIMEZONE" calendar components in the iCalendar
 * object.
 * 
 * FORM #2: UTC TIME
 * 
 * UTC time, or absolute time, is identified by a LATIN CAPITAL LETTER Z
 * suffix character (US-ASCII decimal 90), the UTC designator, appended
 * to the time value. For example, the following represents 07:00 AM
 * UTC:
 * 
 *  070000Z
 * 
 * The TZID property parameter MUST NOT be applied to TIME properties
 * whose time values are specified in UTC.
 * 
 * FORM #3: LOCAL TIME AND TIME ZONE REFERENCE
 * 
 * The local time with reference to time zone information form is
 * identified by the use the TZID property parameter to reference the
 * appropriate time zone definition. TZID is discussed in detail in the
 * section on Time Zone.
 * 
 * Example: The following represents 8:30 AM in New York in Winter, five
 * hours behind UTC, in each of the three formats using the "X-
 * TIMEOFDAY" non-standard property:
 * 
 *  X-TIMEOFDAY:083000
 * 
 *  X-TIMEOFDAY:133000Z
 * 
 *  X-TIMEOFDAY;TZID=US-Eastern:083000
 */
class qCal_Value_Time extends qCal_Value {

	/**
	 * qCal_Date object
	 */
	protected $value;
	/**
	 * Convert the internal date storage to a string
	 */
	protected function toString($value) {
	
		return $value->format('His');
	
	}
	/**
	 * This converts to a qCal_Date for internal storage
	 */
	protected function doCast($value) {
	
		$date = qCal_Time::factory($value);
		return $date;
	
	}

}