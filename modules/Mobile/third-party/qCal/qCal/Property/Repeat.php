<?php
/**
 * Repeat Count Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Make sure that if the alarm triggers more than once, that this 
 * specifies a duration property.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: REPEAT
 * 
 * Purpose: This property defines the number of time the alarm should be
 * repeated, after the initial trigger.
 * Value Type: INTEGER
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: This property can be specified in a "VALARM" calendar
 * component.
 * 
 * Description: If the alarm triggers more than once, then this property
 * MUST be specified along with the "DURATION" property.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   repeatcnt  = "REPEAT" repparam ":" integer CRLF
 *   ;Default is "0", zero.
 * 
 *   repparam   = *(";" xparam)
 * 
 * Example: The following is an example of this property for an alarm
 * that repeats 4 additional times with a 5 minute delay after the
 * initial triggering of the alarm:
 * 
 *   REPEAT:4
 *   DURATION:PT5M
 */
class qCal_Property_Repeat extends qCal_Property {

	protected $type = 'INTEGER';
	protected $allowedComponents = array('VALARM');

}