<?php
/**
 * Categories Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: CATEGORIES
 * 
 * Purpose: This property defines the categories for a calendar
 * component.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard and language property parameters
 * can be specified on this property.
 * 
 * Conformance: The property can be specified within "VEVENT", "VTODO"
 * or "VJOURNAL" calendar components.
 * 
 * Description: This property is used to specify categories or subtypes
 * of the calendar component. The categories are useful in searching for
 * a calendar component of a particular type and category. Within the
 * "VEVENT", "VTODO" or "VJOURNAL" calendar components, more than one
 * category can be specified as a list of categories separated by the
 * COMMA character (US-ASCII decimal 44).
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   categories = "CATEGORIES" catparam ":" text *("," text)
 *                CRLF
 * 
 *   catparam   = *(
 * 
 *              ; the following is optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" languageparam ) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 * Example: The following are examples of this property:
 * 
 *   CATEGORIES:APPOINTMENT,EDUCATION
 * 
 *   CATEGORIES:MEETING
 */
class qCal_Property_Categories extends qCal_Property_MultiValue {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL');

}