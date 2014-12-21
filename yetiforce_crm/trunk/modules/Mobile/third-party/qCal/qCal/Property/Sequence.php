<?php
/**
 * Sequence Number Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo A lot of the conformance below relates more to the application making
 * use of this library, but make sure to conform however possible.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: SEQUENCE
 * 
 * Purpose: This property defines the revision sequence number of the
 * calendar component within a sequence of revisions.
 * Value Type: integer
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: The property can be specified in "VEVENT", "VTODO" or
 * "VJOURNAL" calendar component.
 * 
 * Description: When a calendar component is created, its sequence
 * number is zero (US-ASCII decimal 48). It is monotonically incremented
 * by the "Organizer's" CUA each time the "Organizer" makes a
 * significant revision to the calendar component. When the "Organizer"
 * makes changes to one of the following properties, the sequence number
 * MUST be incremented:
 * 
 *   .  "DTSTART"
 * 
 *   .  "DTEND"
 * 
 *   .  "DUE"
 * 
 *   .  "RDATE"
 * 
 *   .  "RRULE"
 * 
 *   .  "EXDATE"
 * 
 *   .  "EXRULE"
 * 
 *   .  "STATUS"
 * 
 * In addition, changes made by the "Organizer" to other properties can
 * also force the sequence number to be incremented. The "Organizer" CUA
 * MUST increment the sequence number when ever it makes changes to
 * properties in the calendar component that the "Organizer" deems will
 * jeopardize the validity of the participation status of the
 * "Attendees". For example, changing the location of a meeting from one
 * locale to another distant locale could effectively impact the
 * participation status of the "Attendees".
 * 
 * The "Organizer" includes this property in an iCalendar object that it
 * sends to an "Attendee" to specify the current version of the calendar
 * component.
 * 
 * The "Attendee" includes this property in an iCalendar object that it
 * sends to the "Organizer" to specify the version of the calendar
 * component that the "Attendee" is referring to.
 * 
 * A change to the sequence number is not the mechanism that an
 * "Organizer" uses to request a response from the "Attendees". The
 * "RSVP" parameter on the "ATTENDEE" property is used by the
 * "Organizer" to indicate that a response from the "Attendees" is
 * requested.
 * 
 * Format Definition: This property is defined by the following
 * notation:
 * 
 *   seq = "SEQUENCE" seqparam ":" integer CRLF
 *   ; Default is "0"
 * 
 *   seqparam   = *(";" xparam)
 * 
 * Example: The following is an example of this property for a calendar
 * component that was just created by the "Organizer".
 * 
 *   SEQUENCE:0
 * 
 * The following is an example of this property for a calendar component
 * that has been revised two different times by the "Organizer".
 * 
 *   SEQUENCE:2
 */
class qCal_Property_Sequence extends qCal_Property {

	protected $type = 'INTEGER';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL');

}