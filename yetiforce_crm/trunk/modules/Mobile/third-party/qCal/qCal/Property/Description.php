<?php
/**
 * Description Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: DESCRIPTION
 * 
 * Purpose: This property provides a more complete description of the
 * calendar component, than that provided by the "SUMMARY" property.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard, alternate text representation and
 * language property parameters can be specified on this property.
 * 
 * Conformance: The property can be specified in the "VEVENT", "VTODO",
 * "VJOURNAL" or "VALARM" calendar components. The property can be
 * specified multiple times only within a "VJOURNAL" calendar component.
 * 
 * Description: This property is used in the "VEVENT" and "VTODO" to
 * capture lengthy textual decriptions associated with the activity.
 * 
 * This property is used in the "VJOURNAL" calendar component to capture
 * one more textual journal entries.
 * 
 * This property is used in the "VALARM" calendar component to capture
 * the display text for a DISPLAY category of alarm, to capture the body
 * text for an EMAIL category of alarm and to capture the argument
 * string for a PROCEDURE category of alarm.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   description        = "DESCRIPTION" descparam ":" text CRLF
 * 
 *   descparam  = *(
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
 * Example: The following is an example of the property with formatted
 * line breaks in the property value:
 * 
 *   DESCRIPTION:Meeting to provide technical review for "Phoenix"
 *     design.\n Happy Face Conference Room. Phoenix design team
 *     MUST attend this meeting.\n RSVP to team leader.
 * 
 * The following is an example of the property with folding of long
 * lines:
 * 
 *   DESCRIPTION:Last draft of the new novel is to be completed
 *     for the editor's proof today.
 */
class qCal_Property_Description extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT', 'VTODO','VJOURNAL','VALARM');

}