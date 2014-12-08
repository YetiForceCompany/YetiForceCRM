<?php
/**
 * Time Zone URL Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: TZURL
 * 
 * Purpose: The TZURL provides a means for a VTIMEZONE component to
 * point to a network location that can be used to retrieve an up-to-
 * date version of itself.
 * 
 * Value Type: URI
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: This property can be specified in a "VTIMEZONE" calendar
 * component.
 * 
 * Description: The TZURL provides a means for a VTIMEZONE component to
 * point to a network location that can be used to retrieve an up-to-
 * date version of itself. This provides a hook to handle changes
 * government bodies impose upon time zone definitions. Retrieval of
 * this resource results in an iCalendar object containing a single
 * VTIMEZONE component and a METHOD property set to PUBLISH.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   tzurl      = "TZURL" tzurlparam ":" uri CRLF
 * 
 *   tzurlparam = *(";" xparam)
 * 
 * Example: The following is an example of this property:
 * 
 *   TZURL:http://timezones.r.us.net/tz/US-California-Los_Angeles
 */
class qCal_Property_Tzurl extends qCal_Property {

	protected $type = 'URI';
	protected $allowedComponents = array('VTIMEZONE');

}