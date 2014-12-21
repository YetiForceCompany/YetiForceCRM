<?php
/**
 * Summary Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: SUMMARY
 * 
 * Purpose: This property defines a short summary or subject for the
 * calendar component.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard, alternate text representation and
 * language property parameters can be specified on this property.
 * 
 * Conformance: The property can be specified in "VEVENT", "VTODO",
 * "VJOURNAL" or "VALARM" calendar components.
 * 
 * Description: This property is used in the "VEVENT", "VTODO" and
 * "VJOURNAL" calendar components to capture a short, one line summary
 * about the activity or journal entry.
 * 
 * This property is used in the "VALARM" calendar component to capture
 * the subject of an EMAIL category of alarm.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   summary    = "SUMMARY" summparam ":" text CRLF
 * 
 *   summparam  = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" altrepparam) / (";" languageparam) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 * Example: The following is an example of this property:
 * 
 *   SUMMARY:Department Party
 */
class qCal_Property_Summary extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VALARM');

}