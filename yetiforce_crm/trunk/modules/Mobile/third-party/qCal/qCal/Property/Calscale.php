<?php
/**
 * Calendar Scale Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: CALSCALE
 * 
 * Purpose: This property defines the calendar scale used for the
 * calendar information specified in the iCalendar object.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: Property can be specified in an iCalendar object. The
 * default value is "GREGORIAN".
 * 
 * Description: This memo is based on the Gregorian calendar scale. The
 * Gregorian calendar scale is assumed if this property is not specified
 * in the iCalendar object. It is expected that other calendar scales
 * will be defined in other specifications or by future versions of this
 * memo.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   calscale   = "CALSCALE" calparam ":" calvalue CRLF
 * 
 *   calparam   = *(";" xparam)
 * 
 *   calvalue   = "GREGORIAN" / iana-token
 * 
 * Example: The following is an example of this property:
 * 
 *   CALSCALE:GREGORIAN
 */
class qCal_Property_Calscale extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VCALENDAR');
	protected $default = "GREGORIAN";

}