<?php
/**
 * Status Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Make sure that if this doesn't allow arbitrary status values that
 * the use can't specify arbitrary values.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: STATUS
 * 
 * Purpose: This property defines the overall status or confirmation for
 * the calendar component.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: This property can be specified in "VEVENT", "VTODO" or
 * "VJOURNAL" calendar components.
 * 
 * Description: In a group scheduled calendar component, the property is
 * used by the "Organizer" to provide a confirmation of the event to the
 * "Attendees". For example in a "VEVENT" calendar component, the
 * "Organizer" can indicate that a meeting is tentative, confirmed or
 * cancelled. In a "VTODO" calendar component, the "Organizer" can
 * indicate that an action item needs action, is completed, is in
 * process or being worked on, or has been cancelled. In a "VJOURNAL"
 * calendar component, the "Organizer" can indicate that a journal entry
 * is draft, final or has been cancelled or removed.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   status     = "STATUS" statparam] ":" statvalue CRLF
 * 
 *   statparam  = *(";" xparam)
 * 
 *   statvalue  = "TENTATIVE"           ;Indicates event is
 *                                      ;tentative.
 *              / "CONFIRMED"           ;Indicates event is
 *                                      ;definite.
 *              / "CANCELLED"           ;Indicates event was
 *                                      ;cancelled.
 *      ;Status values for a "VEVENT"
 * 
 *   statvalue  =/ "NEEDS-ACTION"       ;Indicates to-do needs action.
 *              / "COMPLETED"           ;Indicates to-do completed.
 *              / "IN-PROCESS"          ;Indicates to-do in process of
 *              / "CANCELLED"           ;Indicates to-do was cancelled.
 *      ;Status values for "VTODO".
 * 
 *   statvalue  =/ "DRAFT"              ;Indicates journal is draft.
 *              / "FINAL"               ;Indicates journal is final.
 *              / "CANCELLED"           ;Indicates journal is removed.
 *      ;Status values for "VJOURNAL".
 * 
 * Example: The following is an example of this property for a "VEVENT"
 * calendar component:
 * 
 *   STATUS:TENTATIVE
 * 
 * The following is an example of this property for a "VTODO" calendar
 * component:
 * 
 *   STATUS:NEEDS-ACTION
 * 
 * The following is an example of this property for a "VJOURNAL"
 * calendar component:
 * 
 *   STATUS:DRAFT
 */
class qCal_Property_Status extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL');

}