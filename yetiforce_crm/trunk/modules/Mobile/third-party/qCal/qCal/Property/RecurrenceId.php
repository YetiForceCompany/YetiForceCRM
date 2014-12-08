<?php
/**
 * Recurrence Id Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo This has some pretty specific rules as to how it is to be used.
 * Make sure that all of them are considered.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: RECURRENCE-ID
 * 
 * Purpose: This property is used in conjunction with the "UID" and
 * "SEQUENCE" property to identify a specific instance of a recurring
 * "VEVENT", "VTODO" or "VJOURNAL" calendar component. The property
 * value is the effective value of the "DTSTART" property of the
 * recurrence instance.
 * 
 * Value Type: The default value type for this property is DATE-TIME.
 * The time format can be any of the valid forms defined for a DATE-TIME
 * value type. See DATE-TIME value type definition for specific
 * interpretations of the various forms. The value type can be set to
 * DATE.
 * 
 * Property Parameters: Non-standard property, value data type, time
 * zone identifier and recurrence identifier range parameters can be
 * specified on this property.
 * 
 * Conformance: This property can be specified in an iCalendar object
 * containing a recurring calendar component.
 * 
 * Description: The full range of calendar components specified by a
 * recurrence set is referenced by referring to just the "UID" property
 * value corresponding to the calendar component. The "RECURRENCE-ID"
 * property allows the reference to an individual instance within the
 * recurrence set.
 * 
 * If the value of the "DTSTART" property is a DATE type value, then the
 * value MUST be the calendar date for the recurrence instance.
 * 
 * The date/time value is set to the time when the original recurrence
 * instance would occur; meaning that if the intent is to change a
 * Friday meeting to Thursday, the date/time is still set to the
 * original Friday meeting.
 * 
 * The "RECURRENCE-ID" property is used in conjunction with the "UID"
 * and "SEQUENCE" property to identify a particular instance of a
 * recurring event, to-do or journal. For a given pair of "UID" and
 * "SEQUENCE" property values, the "RECURRENCE-ID" value for a
 * recurrence instance is fixed. When the definition of the recurrence
 * set for a calendar component changes, and hence the "SEQUENCE"
 * property value changes, the "RECURRENCE-ID" for a given recurrence
 * instance might also change.The "RANGE" parameter is used to specify
 * the effective range of recurrence instances from the instance
 * specified by the "RECURRENCE-ID" property value. The default value
 * for the range parameter is the single recurrence instance only. The
 * value can also be "THISANDPRIOR" to indicate a range defined by the
 * given recurrence instance and all prior instances or the value can be
 * "THISANDFUTURE" to indicate a range defined by the given recurrence
 * instance and all subsequent instances.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   recurid    = "RECURRENCE-ID" ridparam ":" ridval CRLF
 * 
 *   ridparam   = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" "VALUE" "=" ("DATE-TIME" / "DATE)) /
 *              (";" tzidparam) / (";" rangeparam) /
 *           ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 *   ridval     = date-time / date
 *   ;Value MUST match value type
 * 
 * Example: The following are examples of this property:
 * 
 *   RECURRENCE-ID;VALUE=DATE:19960401
 * 
 *   RECURRENCE-ID;RANGE=THISANDFUTURE:19960120T120000Z
 */
class qCal_Property_RecurrenceId extends qCal_Property {

	protected $type = 'DATE-TIME';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL');

}