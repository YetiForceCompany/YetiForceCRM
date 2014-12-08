<?php
/**
 * Completed Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: COMPLETED
 * 
 * Purpose: This property defines the date and time that a to-do was
 * actually completed.
 * 
 * Value Type: DATE-TIME
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: The property can be specified in a "VTODO" calendar
 * component.
 * 
 * Description: The date and time MUST be in a UTC format.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   completed  = "COMPLETED" compparam ":" date-time CRLF
 * 
 *   compparam  = *(";" xparam)
 * 
 * Example: The following is an example of this property:
 * 
 *   COMPLETED:19960401T235959Z
 */
class qCal_Property_Completed extends qCal_Property {

	protected $type = 'DATE-TIME';
	protected $allowedComponents = array('VTODO');

}