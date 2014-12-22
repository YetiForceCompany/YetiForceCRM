<?php
/**
 * Uniform Resource Locator Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: URL
 * 
 * Purpose: This property defines a Uniform Resource Locator (URL)
 * associated with the iCalendar object.
 * 
 * Value Type: URI
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * 
 * Conformance: This property can be specified once in the "VEVENT",
 * "VTODO", "VJOURNAL" or "VFREEBUSY" calendar components.
 * 
 * Description: This property may be used in a calendar component to
 * convey a location where a more dynamic rendition of the calendar
 * information associated with the calendar component can be found. This
 * memo does not attempt to standardize the form of the URI, nor the
 * format of the resource pointed to by the property value. If the URL
 * property and Content-Location MIME header are both specified, they
 * MUST point to the same resource.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   url        = "URL" urlparam ":" uri CRLF
 * 
 *   urlparam   = *(";" xparam)
 * 
 * Example: The following is an example of this property:
 * 
 *   URL:http://abc.com/pub/calendars/jsmith/mytime.ics
 */
class qCal_Property_Url extends qCal_Property {

	protected $type = 'URI';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VFREEBUSY');

}