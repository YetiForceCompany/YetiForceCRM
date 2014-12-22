<?php
/**
 * Time Zone Offset To Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Make sure that the various components that require this actually
 * require it.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: TZOFFSETTO
 * 
 * Purpose: This property specifies the offset which is in use in this
 * time zone observance.
 * 
 * Value Type: UTC-OFFSET
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: This property MUST be specified in a "VTIMEZONE"
 * calendar component.
 * 
 * Description: This property specifies the offset which is in use in
 * this time zone observance. It is used to calculate the absolute time
 * for the new observance. The property value is a signed numeric
 * indicating the number of hours and possibly minutes from UTC.
 * Positive numbers represent time zones east of the prime meridian, or
 * ahead of UTC. Negative numbers represent time zones west of the prime
 * meridian, or behind UTC.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   tzoffsetto = "TZOFFSETTO" toparam ":" utc-offset CRLF
 * 
 *   toparam    = *(";" xparam)
 * 
 * Example: The following are examples of this property:
 * 
 *   TZOFFSETTO:-0400
 * 
 *   TZOFFSETTO:+1245
 */
class qCal_Property_Tzoffsetto extends qCal_Property {

	protected $type = 'UTC-OFFSET';
	protected $allowedComponents = array('VTIMEZONE','DAYLIGHT','STANDARD');

}