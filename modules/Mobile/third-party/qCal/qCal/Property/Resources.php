<?php
/**
 * Resources Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: RESOURCES
 * 
 * Purpose: This property defines the equipment or resources anticipated
 * for an activity specified by a calendar entity..
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard, alternate text representation and
 * language property parameters can be specified on this property.
 * 
 * Conformance: This property can be specified in "VEVENT" or "VTODO"
 * calendar component.
 * 
 * Description: The property value is an arbitrary text. More than one
 * resource can be specified as a list of resources separated by the
 * COMMA character (US-ASCII decimal 44).
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   resources  = "RESOURCES" resrcparam ":" text *("," text) CRLF
 * 
 *   resrcparam = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" altrepparam) / (";" languageparam) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 * 
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 * Example: The following is an example of this property:
 * 
 *   RESOURCES:EASEL,PROJECTOR,VCR
 * 
 *   RESOURCES;LANGUAGE=fr:1 raton-laveur
 */
class qCal_Property_Resources extends qCal_Property_MultiValue {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO');

}