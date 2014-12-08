<?php
/**
 * Attendee Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Make sure that allowedComponents is correct. I am still a little
 *       confused about how this property works. It is apparent that it is
 *       used differently based on compoenent. I think the correct place
 *       to put logic like that is in the component itself.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: ATTENDEE
 * 
 * Purpose: The property defines an "Attendee" within a calendar
 * component.
 * 
 * Value Type: CAL-ADDRESS
 * 
 * Property Parameters: Non-standard, language, calendar user type,
 * group or list membership, participation role, participation status,
 * RSVP expectation, delegatee, delegator, sent by, common name or
 * directory entry reference property parameters can be specified on
 * this property.
 * 
 * Conformance: This property MUST be specified in an iCalendar object
 * that specifies a group scheduled calendar entity. This property MUST
 * NOT be specified in an iCalendar object when publishing the calendar
 * information (e.g., NOT in an iCalendar object that specifies the
 * publication of a calendar user's busy time, event, to-do or journal).
 * This property is not specified in an iCalendar object that specifies
 * only a time zone definition or that defines calendar entities that
 * are not group scheduled entities, but are entities only on a single
 * user's calendar.
 * 
 * Description: The property MUST only be specified within calendar
 * components to specify participants, non-participants and the chair of
 * a group scheduled calendar entity. The property is specified within
 * an "EMAIL" category of the "VALARM" calendar component to specify an
 * email address that is to receive the email type of iCalendar alarm.
 * 
 * The property parameter CN is for the common or displayable name
 * associated with the calendar address; ROLE, for the intended role
 * that the attendee will have in the calendar component; PARTSTAT, for
 * the status of the attendee's participation; RSVP, for indicating
 * whether the favor of a reply is requested; CUTYPE, to indicate the
 * type of calendar user; MEMBER, to indicate the groups that the
 * attendee belongs to; DELEGATED-TO, to indicate the calendar users
 * that the original request was delegated to; and DELEGATED-FROM, to
 * indicate whom the request was delegated from; SENT-BY, to indicate
 * whom is acting on behalf of the ATTENDEE; and DIR, to indicate the
 * URI that points to the directory information corresponding to the
 * attendee. These property parameters can be specified on an "ATTENDEE"
 * property in either a "VEVENT", "VTODO" or "VJOURNAL" calendar
 * component. They MUST not be specified in an "ATTENDEE" property in a
 * "VFREEBUSY" or "VALARM" calendar component. If the LANGUAGE property
 * parameter is specified, the identified language applies to the CN
 * parameter.
 * 
 * A recipient delegated a request MUST inherit the RSVP and ROLE values
 * from the attendee that delegated the request to them.
 * 
 * Multiple attendees can be specified by including multiple "ATTENDEE"
 * properties within the calendar component.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   attendee   = "ATTENDEE" attparam ":" cal-address CRLF
 * 
 *   attparam   = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" cutypeparam) / (";"memberparam) /
 *              (";" roleparam) / (";" partstatparam) /
 *              (";" rsvpparam) / (";" deltoparam) /
 *              (";" delfromparam) / (";" sentbyparam) /
 *              (";"cnparam) / (";" dirparam) /
 *              (";" languageparam) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 * Example: The following are examples of this property's use for a to-
 * do:
 * 
 *   ORGANIZER:MAILTO:jsmith@host1.com
 *   ATTENDEE;MEMBER="MAILTO:DEV-GROUP@host2.com":
 *    MAILTO:joecool@host2.com
 *   ATTENDEE;DELEGATED-FROM="MAILTO:immud@host3.com":
 *    MAILTO:ildoit@host1.com
 * 
 * The following is an example of this property used for specifying
 * multiple attendees to an event:
 * 
 *   ORGANIZER:MAILTO:jsmith@host1.com
 *   ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=TENTATIVE;CN=Henry Cabot
 *    :MAILTO:hcabot@host2.com
 *   ATTENDEE;ROLE=REQ-PARTICIPANT;DELEGATED-FROM="MAILTO:bob@host.com"
 *    ;PARTSTAT=ACCEPTED;CN=Jane Doe:MAILTO:jdoe@host1.com
 * 
 * The following is an example of this property with a URI to the
 * directory information associated with the attendee:
 * 
 *   ATTENDEE;CN=John Smith;DIR="ldap://host.com:6666/o=eDABC%
 *    20Industries,c=3DUS??(cn=3DBJim%20Dolittle)":MAILTO:jimdo@
 *    host1.com
 * 
 * The following is an example of this property with "delegatee" and
 * "delegator" information for an event:
 * 
 *   ORGANIZER;CN=John Smith:MAILTO:jsmith@host.com
 *   ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=TENTATIVE;DELEGATED-FROM=
 *    "MAILTO:iamboss@host2.com";CN=Henry Cabot:MAILTO:hcabot@
 *    host2.com
 *   ATTENDEE;ROLE=NON-PARTICIPANT;PARTSTAT=DELEGATED;DELEGATED-TO=
 *    "MAILTO:hcabot@host2.com";CN=The Big Cheese:MAILTO:iamboss
 *    @host2.com
 *   ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;CN=Jane Doe
 *    :MAILTO:jdoe@host1.com
 * 
 * Example: The following is an example of this property's use when
 * another calendar user is acting on behalf of the "Attendee":
 * 
 *   ATTENDEE;SENT-BY=MAILTO:jan_doe@host1.com;CN=John Smith:MAILTO:
 *    jsmith@host1.com
 */
class qCal_Property_Attendee extends qCal_Property {

	protected $type = 'CAL-ADDRESS';
	// If I'm reading the RFC correctly above, this property can be specified
	// on the following components, but I'm still a bit confused about it. I 
	// need to read up on it more to really understand
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VALARM');
	protected $allowMultiple = true;

}