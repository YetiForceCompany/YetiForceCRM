<?php
/**
 * Dated/Time Created Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: CREATED
 * 
 * Purpose: This property specifies the date and time that the calendar
 * information was created by the calendar user agent in the calendar
 * store.
 * 
 *      Note: This is analogous to the creation date and time for a file
 *      in the file system.
 * 
 * Value Type: DATE-TIME
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: The property can be specified once in "VEVENT", "VTODO"
 * or "VJOURNAL" calendar components.
 * 
 * Description: The date and time is a UTC value.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   created    = "CREATED" creaparam ":" date-time CRLF
 * 
 *   creaparam  = *(";" xparam)
 * 
 * Example: The following is an example of this property:
 * 
 *   CREATED:19960329T133000Z
 */
class qCal_Property_Created extends qCal_Property {

	protected $type = 'DATE-TIME';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL');

}