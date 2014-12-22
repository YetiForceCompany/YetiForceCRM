<?php
/**
 * Method Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: METHOD
 * 
 * Purpose: This property defines the iCalendar object method associated
 * with the calendar object.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: The property can be specified in an iCalendar object.
 * 
 * Description: When used in a MIME message entity, the value of this
 * property MUST be the same as the Content-Type "method" parameter
 * value. This property can only appear once within the iCalendar
 * object. If either the "METHOD" property or the Content-Type "method"
 * parameter is specified, then the other MUST also be specified.
 * 
 * No methods are defined by this specification. This is the subject of
 * other specifications, such as the iCalendar Transport-independent
 * 
 * Interoperability Protocol (iTIP) defined by [ITIP].
 * 
 * If this property is not present in the iCalendar object, then a
 * scheduling transaction MUST NOT be assumed. In such cases, the
 * iCalendar object is merely being used to transport a snapshot of some
 * calendar information; without the intention of conveying a scheduling
 * semantic.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   method     = "METHOD" metparam ":" metvalue CRLF
 * 
 *   metparam   = *(";" xparam)
 * 
 *   metvalue   = iana-token
 * 
 * Example: The following is a hypothetical example of this property to
 * convey that the iCalendar object is a request for a meeting:
 * 
 *   METHOD:REQUEST
 */
class qCal_Property_Method extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VCALENDAR');

}