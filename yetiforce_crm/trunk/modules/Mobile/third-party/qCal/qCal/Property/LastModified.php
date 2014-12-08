<?php
/**
 * Last Modified Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Make sure that the date is converted to UTC
 * 
 * RFC 2445 Definition
 * 
 * Property Name: LAST-MODIFIED
 * 
 * Purpose: The property specifies the date and time that the
 * information associated with the calendar component was last revised
 * in the calendar store.
 * 
 *      Note: This is analogous to the modification date and time for a
 *      file in the file system.
 * 
 * Value Type: DATE-TIME
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: This property can be specified in the "EVENT", "VTODO",
 * "VJOURNAL" or "VTIMEZONE" calendar components.
 * 
 * Description: The property value MUST be specified in the UTC time
 * format.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   last-mod   = "LAST-MODIFIED" lstparam ":" date-time CRLF
 * 
 *   lstparam   = *(";" xparam)
 * 
 * Example: The following is are examples of this property:
 * 
 *   LAST-MODIFIED:19960817T133000Z
 */
class qCal_Property_LastModified extends qCal_Property {

	protected $type = 'DATE-TIME';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VTIMEZONE');

}