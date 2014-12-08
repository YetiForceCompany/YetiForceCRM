<?php
/**
 * Duration Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: DURATION
 * 
 * Purpose: The property specifies a positive duration of time.
 * 
 * Value Type: DURATION
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: The property can be specified in "VEVENT", "VTODO",
 * "VFREEBUSY" or "VALARM" calendar components.
 * 
 * Description: In a "VEVENT" calendar component the property may be
 * used to specify a duration of the event, instead of an explicit end
 * date/time. In a "VTODO" calendar component the property may be used
 * to specify a duration for the to-do, instead of an explicit due
 * date/time. In a "VFREEBUSY" calendar component the property may be
 * used to specify the interval of free time being requested. In a
 * "VALARM" calendar component the property may be used to specify the
 * delay period prior to repeating an alarm.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   duration   = "DURATION" durparam ":" dur-value CRLF
 *                ;consisting of a positive duration of time.
 * 
 *   durparam   = *(";" xparam)
 * 
 * Example: The following is an example of this property that specifies
 * an interval of time of 1 hour and zero minutes and zero seconds:
 * 
 *   DURATION:PT1H0M0S
 * 
 * The following is an example of this property that specifies an
 * interval of time of 15 minutes.
 * 
 *   DURATION:PT15M
 */
class qCal_Property_Duration extends qCal_Property {

	protected $type = 'DURATION';
	protected $allowedComponents = array('VEVENT','VTODO','VFREEBUSY','VALARM');

}