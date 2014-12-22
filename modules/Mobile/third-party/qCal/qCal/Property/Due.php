<?php
/**
 * Date/Time Due Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Make sure that the date of this is equal to or after the DTSTART
 * date, if specified.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: DUE
 * 
 * Purpose: This property defines the date and time that a to-do is
 * expected to be completed.
 * 
 * Value Type: The default value type is DATE-TIME. The value type can
 * be set to a DATE value type.
 * 
 * Property Parameters: Non-standard, value data type, time zone
 * identifier property parameters can be specified on this property.
 * 
 * Conformance: The property can be specified once in a "VTODO" calendar
 * component.
 * 
 * Description: The value MUST be a date/time equal to or after the
 * DTSTART value, if specified.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   due        = "DUE" dueparam":" dueval CRLF
 * 
 *   dueparam   = *(
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" "VALUE" "=" ("DATE-TIME" / "DATE")) /
 *              (";" tzidparam) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *                *(";" xparam)
 * 
 *              )
 * 
 *   dueval     = date-time / date
 *   ;Value MUST match value type
 * 
 * Example: The following is an example of this property:
 * 
 *   DUE:19980430T235959Z
 */
class qCal_Property_Due extends qCal_Property {

	protected $type = 'DATE-TIME';
	protected $allowedComponents = array('VTODO');

}