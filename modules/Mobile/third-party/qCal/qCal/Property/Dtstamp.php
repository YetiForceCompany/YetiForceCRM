<?php
/**
 * Dated/Time Stamp Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Make sure that this is specified in UTC format.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: DTSTAMP
 * 
 * Purpose: The property indicates the date/time that the instance of
 * the iCalendar object was created.
 * 
 * Value Type: DATE-TIME
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: This property MUST be included in the "VEVENT", "VTODO",
 * "VJOURNAL" or "VFREEBUSY" calendar components.
 * 
 * Description: The value MUST be specified in the UTC time format.
 * 
 * This property is also useful to protocols such as [IMIP] that have
 * inherent latency issues with the delivery of content. This property
 * will assist in the proper sequencing of messages containing iCalendar
 * objects.
 * 
 * This property is different than the "CREATED" and "LAST-MODIFIED"
 * properties. These two properties are used to specify when the
 * particular calendar data in the calendar store was created and last
 * modified. This is different than when the iCalendar object
 * representation of the calendar service information was created or
 * last modified.
 * Format Definition: The property is defined by the following notation:
 * 
 *   dtstamp    = "DTSTAMP" stmparam ":" date-time CRLF
 * 
 *   stmparam   = *(";" xparam)
 * 
 * Example:
 * 
 *   DTSTAMP:19971210T080000Z
 */
class qCal_Property_Dtstamp extends qCal_Property {

	protected $type = 'DATE-TIME';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VFREEBUSY');

}