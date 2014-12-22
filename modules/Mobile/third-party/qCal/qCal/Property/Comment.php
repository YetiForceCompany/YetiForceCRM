<?php
/**
 * Comment Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Purpose: This property specifies non-processing information intended
 * to provide a comment to the calendar user.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard, alternate text representation and
 * language property parameters can be specified on this property.
 * 
 * Conformance: This property can be specified in "VEVENT", "VTODO",
 * "VJOURNAL", "VTIMEZONE" or "VFREEBUSY" calendar components.
 * 
 * Description: The property can be specified multiple times.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   comment    = "COMMENT" commparam ":" text CRLF
 * 
 *   commparam  = *(
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
 *   COMMENT:The meeting really needs to include both ourselves
 *     and the customer. We can't hold this  meeting without them.
 *     As a matter of fact\, the venue for the meeting ought to be at
 * 
 *     their site. - - John
 * 
 * The data type for this property is TEXT.
 */
class qCal_Property_Comment extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT', 'VTODO','VJOURNAL','VTIMEZONE','VFREEBUSY');
	protected $allowMultiple = true;

}