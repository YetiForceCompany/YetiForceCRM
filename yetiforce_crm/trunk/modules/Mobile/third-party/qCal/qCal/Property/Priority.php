<?php
/**
 * Priority Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: PRIORITY
 * 
 * Purpose: The property defines the relative priority for a calendar
 * component.
 * 
 * Value Type: INTEGER
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: The property can be specified in a "VEVENT" or "VTODO"
 * calendar component.
 * 
 * Description: The priority is specified as an integer in the range
 * zero to nine. A value of zero (US-ASCII decimal 48) specifies an
 * undefined priority. A value of one (US-ASCII decimal 49) is the
 * highest priority. A value of two (US-ASCII decimal 50) is the second
 * highest priority. Subsequent numbers specify a decreasing ordinal
 * priority. A value of nine (US-ASCII decimal 58) is the lowest
 * priority.
 * 
 * A CUA with a three-level priority scheme of "HIGH", "MEDIUM" and
 * "LOW" is mapped into this property such that a property value in the
 * range of one (US-ASCII decimal 49) to four (US-ASCII decimal 52)
 * specifies "HIGH" priority. A value of five (US-ASCII decimal 53) is
 * the normal or "MEDIUM" priority. A value in the range of six (US-
 * ASCII decimal 54) to nine (US-ASCII decimal 58) is "LOW" priority.
 * 
 * A CUA with a priority schema of "A1", "A2", "A3", "B1", "B2", ...,
 * "C3" is mapped into this property such that a property value of one
 * (US-ASCII decimal 49) specifies "A1", a property value of two (US-
 * ASCII decimal 50) specifies "A2", a property value of three (US-ASCII
 * decimal 51) specifies "A3", and so forth up to a property value of 9
 * (US-ASCII decimal 58) specifies "C3".
 * 
 * Other integer values are reserved for future use.
 * 
 * Within a "VEVENT" calendar component, this property specifies a
 * priority for the event. This property may be useful when more than
 * one event is scheduled for a given time period.
 * 
 * Within a "VTODO" calendar component, this property specified a
 * priority for the to-do. This property is useful in prioritizing
 * multiple action items for a given time period.
 * 
 * Format Definition: The property is specified by the following
 * notation:
 * 
 *   priority   = "PRIORITY" prioparam ":" privalue CRLF
 *   ;Default is zero
 * 
 *   prioparam  = *(";" xparam)
 * 
 *   privalue   = integer       ;Must be in the range [0..9]
 *      ; All other values are reserved for future use
 * 
 * The following is an example of a property with the highest priority:
 * 
 *   PRIORITY:1
 * 
 * The following is an example of a property with a next highest
 * priority:
 * 
 *   PRIORITY:2
 * 
 * Example: The following is an example of a property with no priority.
 * This is equivalent to not specifying the "PRIORITY" property:
 * 
 *   PRIORITY:0
 */
class qCal_Property_Priority extends qCal_Property {

	protected $type = 'INTEGER';
	protected $allowedComponents = array('VEVENT','VTODO');
	protected $default = 0;

}