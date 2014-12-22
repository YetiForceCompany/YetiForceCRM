<?php
/**
 * Related To Id Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: RELATED-TO
 * 
 * Purpose: The property is used to represent a relationship or
 * reference between one calendar component and another.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard and relationship type property
 * parameters can be specified on this property.
 * 
 * Conformance: The property can be specified one or more times in the
 * "VEVENT", "VTODO" or "VJOURNAL" calendar components.
 * 
 * Description: The property value consists of the persistent, globally
 * unique identifier of another calendar component. This value would be
 * represented in a calendar component by the "UID" property.
 * 
 * By default, the property value points to another calendar component
 * that has a PARENT relationship to the referencing object. The
 * "RELTYPE" property parameter is used to either explicitly state the
 * default PARENT relationship type to the referenced calendar component
 * or to override the default PARENT relationship type and specify
 * either a CHILD or SIBLING relationship. The PARENT relationship
 * indicates that the calendar component is a subordinate of the
 * referenced calendar component. The CHILD relationship indicates that
 * the calendar component is a superior of the referenced calendar
 * component. The SIBLING relationship indicates that the calendar
 * component is a peer of the referenced calendar component.
 * 
 * 
 * Changes to a calendar component referenced by this property can have
 * an implicit impact on the related calendar component. For example, if
 * a group event changes its start or end date or time, then the
 * related, dependent events will need to have their start and end dates
 * changed in a corresponding way. Similarly, if a PARENT calendar
 * component is canceled or deleted, then there is an implied impact to
 * the related CHILD calendar components. This property is intended only
 * to provide information on the relationship of calendar components. It
 * is up to the target calendar system to maintain any property
 * implications of this relationship.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   related    = "RELATED-TO" [relparam] ":" text CRLF
 * 
 *   relparam   = *(
 * 
 *              ; the following is optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" reltypeparam) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparm)
 * 
 *              )
 * 
 * The following is an example of this property:
 * 
 *   RELATED-TO:<jsmith.part7.19960817T083000.xyzMail@host3.com>
 * 
 *   RELATED-TO:<19960401-080045-4000F192713-0052@host1.com>
 */
class qCal_Property_RecurrenceId extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL');
	protected $allowMultiple = true;

}