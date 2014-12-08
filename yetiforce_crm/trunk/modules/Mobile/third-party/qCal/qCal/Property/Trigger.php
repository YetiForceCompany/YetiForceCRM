<?php
/**
 * Trigger Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Make sure this behaves as expected when various other properties are
 * introduced.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: TRIGGER
 * 
 * Purpose: This property specifies when an alarm will trigger.
 * 
 * Value Type: The default value type is DURATION. The value type can be
 * set to a DATE-TIME value type, in which case the value MUST specify a
 * UTC formatted DATE-TIME value.
 * 
 * Property Parameters: Non-standard, value data type, time zone
 * identifier or trigger relationship property parameters can be
 * specified on this property. The trigger relationship property
 * parameter MUST only be specified when the value type is DURATION.
 * 
 * Conformance: This property MUST be specified in the "VALARM" calendar
 * component.
 * 
 * Description: Within the "VALARM" calendar component, this property
 * defines when the alarm will trigger. The default value type is
 * DURATION, specifying a relative time for the trigger of the alarm.
 * The default duration is relative to the start of an event or to-do
 * that the alarm is associated with. The duration can be explicitly set
 * to trigger from either the end or the start of the associated event
 * or to-do with the "RELATED" parameter. A value of START will set the
 * alarm to trigger off the start of the associated event or to-do. A
 * value of END will set the alarm to trigger off the end of the
 * associated event or to-do.
 * 
 * Either a positive or negative duration may be specified for the
 * "TRIGGER" property. An alarm with a positive duration is triggered
 * after the associated start or end of the event or to-do. An alarm
 * with a negative duration is triggered before the associated start or
 * end of the event or to-do.
 * 
 * The "RELATED" property parameter is not valid if the value type of
 * the property is set to DATE-TIME (i.e., for an absolute date and time
 * alarm trigger). If a value type of DATE-TIME is specified, then the
 * property value MUST be specified in the UTC time format. If an
 * absolute trigger is specified on an alarm for a recurring event or
 * to-do, then the alarm will only trigger for the specified absolute
 * date/time, along with any specified repeating instances.
 * 
 * If the trigger is set relative to START, then the "DTSTART" property
 * MUST be present in the associated "VEVENT" or "VTODO" calendar
 * component. If an alarm is specified for an event with the trigger set
 * relative to the END, then the "DTEND" property or the "DSTART" and
 * "DURATION' properties MUST be present in the associated "VEVENT"
 * calendar component. If the alarm is specified for a to-do with a
 * trigger set relative to the END, then either the "DUE" property or
 * the "DSTART" and "DURATION' properties MUST be present in the
 * associated "VTODO" calendar component.
 * 
 * Alarms specified in an event or to-do which is defined in terms of a
 * DATE value type will be triggered relative to 00:00:00 UTC on the
 * specified date. For example, if "DTSTART:19980205, then the duration
 * trigger will be relative to19980205T000000Z.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   trigger    = "TRIGGER" (trigrel / trigabs)
 * 
 *   trigrel    = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *                (";" "VALUE" "=" "DURATION") /
 *                (";" trigrelparam) /
 * 
 *              ; the following is optional,
 *           ; and MAY occur more than once
 * 
 *                (";" xparam)
 *                ) ":"  dur-value
 * 
 *   trigabs    = 1*(
 * 
 *              ; the following is REQUIRED,
 *              ; but MUST NOT occur more than once
 * 
 *                (";" "VALUE" "=" "DATE-TIME") /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *                (";" xparam)
 * 
 *                ) ":" date-time
 * 
 * Example: A trigger set 15 minutes prior to the start of the event or
 * to-do.
 * 
 *   TRIGGER:-P15M
 * 
 * A trigger set 5 minutes after the end of the event or to-do.
 * 
 *   TRIGGER;RELATED=END:P5M
 * 
 * A trigger set to an absolute date/time.
 * 
 *   TRIGGER;VALUE=DATE-TIME:19980101T050000Z
 */
class qCal_Property_Trigger extends qCal_Property {

	protected $type = 'DURATION';
	protected $allowedComponents = array('VALARM');

}