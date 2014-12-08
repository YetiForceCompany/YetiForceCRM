<?php
/**
 * Time Zone Name Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: TZNAME
 * 
 * Purpose: This property specifies the customary designation for a time
 * zone description.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard and language property parameters
 * can be specified on this property.
 * 
 * Conformance: This property can be specified in a "VTIMEZONE" calendar
 * component.
 * 
 * Description: This property may be specified in multiple languages; in
 * order to provide for different language requirements.
 * 
 * Format Definition: This property is defined by the following
 * notation:
 * 
 *   tzname     = "TZNAME" tznparam ":" text CRLF
 * 
 *   tznparam   = *(
 * 
 *              ; the following is optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" languageparam) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 * Example: The following are example of this property:
 * 
 *   TZNAME:EST
 * 
 * The following is an example of this property when two different
 * languages for the time zone name are specified:
 * 
 *   TZNAME;LANGUAGE=en:EST
 *   TZNAME;LANGUAGE=fr-CA:HNE
 */
class qCal_Property_Tzname extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VTIMEZONE','DAYLIGHT','STANDARD');
	protected $allowMultiple = true;

}