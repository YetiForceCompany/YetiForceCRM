<?php
/**
 * Date/Time End Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Find a way of making sure that if there is a DTSTART, that its date
 * is earlier than that of this property.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: DTEND
 * 
 * Purpose: This property specifies the date and time that a calendar
 * component ends.
 * 
 * Value Type: The default value type is DATE-TIME. The value type can
 * be set to a DATE value type.
 * 
 * Property Parameters: Non-standard, value data type, time zone
 * identifier property parameters can be specified on this property.
 * 
 * Conformance: This property can be specified in "VEVENT" or
 * "VFREEBUSY" calendar components.
 * 
 * Description: Within the "VEVENT" calendar component, this property
 * defines the date and time by which the event ends. The value MUST be
 * later in time than the value of the "DTSTART" property.
 * 
 * Within the "VFREEBUSY" calendar component, this property defines the
 * end date and time for the free or busy time information. The time
 * MUST be specified in the UTC time format. The value MUST be later in
 * time than the value of the "DTSTART" property.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   dtend      = "DTEND" dtendparam":" dtendval CRLF
 * 
 *   dtendparam = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" "VALUE" "=" ("DATE-TIME" / "DATE")) /
 *              (";" tzidparam) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 *   dtendval   = date-time / date
 *   ;Value MUST match value type
 * 
 * Example: The following is an example of this property:
 * 
 *   DTEND:19960401T235959Z
 * 
 *   DTEND;VALUE=DATE:19980704
 */
class qCal_Property_Dtend extends qCal_Property {

	protected $type = 'DATE-TIME';
	protected $allowedComponents = array('VEVENT','VFREEBUSY','DAYLIGHT','STANDARD');

}