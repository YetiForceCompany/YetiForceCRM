<?php
/**
 * Date-Time Value
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * Value Name: DATE-TIME
 * 
 * Purpose: This value type is used to identify values that specify a
 * precise calendar date and time of day.
 * 
 * Formal Definition: The value type is defined by the following
 * notation:
 * 
 *  date-time  = date "T" time ;As specified in the date and time
 *                             ;value definitions
 * 
 * Description: If the property permits, multiple "date-time" values are
 * specified as a COMMA character (US-ASCII decimal 44) separated list
 * of values. No additional content value encoding (i.e., BACKSLASH
 * character encoding) is defined for this value type.
 * 
 * The "DATE-TIME" data type is used to identify values that contain a
 * precise calendar date and time of day. The format is based on the
 * [ISO 8601] complete representation, basic format for a calendar date
 * and time of day. The text format is a concatenation of the "date",
 * followed by the LATIN CAPITAL LETTER T character (US-ASCII decimal
 * 84) time designator, followed by the "time" format.
 * 
 * The "DATE-TIME" data type expresses time values in three forms:
 * 
 * The form of date and time with UTC offset MUST NOT be used. For
 * example, the following is not valid for a date-time value:
 * 
 *  DTSTART:19980119T230000-0800       ;Invalid time format
 * 
 * FORM #1: DATE WITH LOCAL TIME
 * 
 * The date with local time form is simply a date-time value that does
 * not contain the UTC designator nor does it reference a time zone. For
 * example, the following represents Janurary 18, 1998, at 11 PM:
 * 
 *  DTSTART:19980118T230000
 * 
 * Date-time values of this type are said to be "floating" and are not
 * bound to any time zone in particular. They are used to represent the
 * same hour, minute, and second value regardless of which time zone is
 * currently being observed. For example, an event can be defined that
 * indicates that an individual will be busy from 11:00 AM to 1:00 PM
 * every day, no matter which time zone the person is in. In these
 * cases, a local time can be specified. The recipient of an iCalendar
 * object with a property value consisting of a local time, without any
 * relative time zone information, SHOULD interpret the value as being
 * fixed to whatever time zone the ATTENDEE is in at any given moment.
 * This means that two ATTENDEEs, in different time zones, receiving the
 * same event definition as a floating time, may be participating in the
 * event at different actual times. Floating time SHOULD only be used
 * where that is the reasonable behavior.
 * 
 * In most cases, a fixed time is desired. To properly communicate a
 * fixed time in a property value, either UTC time or local time with
 * time zone reference MUST be specified.
 * 
 * The use of local time in a DATE-TIME value without the TZID property
 * parameter is to be interpreted as floating time, regardless of the
 * existence of "VTIMEZONE" calendar components in the iCalendar object.
 * 
 * FORM #2: DATE WITH UTC TIME
 * 
 * The date with UTC time, or absolute time, is identified by a LATIN
 * CAPITAL LETTER Z suffix character (US-ASCII decimal 90), the UTC
 * designator, appended to the time value. For example, the following
 * represents January 19, 1998, at 0700 UTC:
 * 
 *  DTSTART:19980119T070000Z
 * 
 * The TZID property parameter MUST NOT be applied to DATE-TIME
 * properties whose time values are specified in UTC.
 * 
 * FORM #3: DATE WITH LOCAL TIME AND TIME ZONE REFERENCE
 * 
 * The date and local time with reference to time zone information is
 * identified by the use the TZID property parameter to reference the
 * appropriate time zone definition. TZID is discussed in detail in the
 * section on Time Zone. For example, the following represents 2 AM in
 * New York on Janurary 19, 1998:
 * 
 *       DTSTART;TZID=US-Eastern:19980119T020000
 * 
 * Example: The following represents July 14, 1997, at 1:30 PM in New
 * York City in each of the three time formats, using the "DTSTART"
 * property.
 * 
 *  DTSTART:19970714T133000            ;Local time
 *  DTSTART:19970714T173000Z           ;UTC time
 *  DTSTART;TZID=US-Eastern:19970714T133000    ;Local time and time
 *                     ; zone reference
 * 
 * A time value MUST ONLY specify 60 seconds when specifying the
 * periodic "leap second" in the time value. For example:
 * 
 *  COMPLETED:19970630T235960Z
 */
class qCal_Value_Datetime extends qCal_Value {

	/**
	 * qCal_Date object
	 */
	protected $value;
	/**
	 * Convert the internal date storage to a string
	 */
	protected function toString($value) {
	
		return $value->format('Ymd\THis');
	
	}
	/**
	 * This converts to a qCal_Date for internal storage
	 */
	protected function doCast($value) {
	
		// @todo This may be the wrong place to do this...
		if ($value instanceof qCal_DateTime) {
			return $value;
		}
		$date = qCal_DateTime::factory($value);
		return $date;
	
	}

}