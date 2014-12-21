<?php
/**
 * Version Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: VERSION
 * 
 * Purpose: This property specifies the identifier corresponding to the
 * highest version number or the minimum and maximum range of the
 * iCalendar specification that is required in order to interpret the
 * iCalendar object.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: This property MUST be specified by an iCalendar object,
 * but MUST only be specified once.
 * 
 * Description: A value of "2.0" corresponds to this memo.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   version    = "VERSION" verparam ":" vervalue CRLF
 * 
 *   verparam   = *(";" xparam)
 * 
 *   vervalue   = "2.0"         ;This memo
 *              / maxver
 *              / (minver ";" maxver)
 * 
 *   minver     = <A IANA registered iCalendar version identifier>
 *   ;Minimum iCalendar version needed to parse the iCalendar object
 * 
 *   maxver     = <A IANA registered iCalendar version identifier>
 *   ;Maximum iCalendar version needed to parse the iCalendar object
 * 
 * Example: The following is an example of this property:
 * 
 *   VERSION:2.0
 */
class qCal_Property_Version extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VCALENDAR');
	protected $default = "2.0";

}