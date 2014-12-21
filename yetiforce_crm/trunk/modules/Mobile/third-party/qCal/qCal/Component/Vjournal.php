<?php
/**
 * Journal Component
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Component Name: VJOURNAL
 * 
 * Purpose: Provide a grouping of component properties that describe a
 * journal entry.
 * 
 * Formal Definition: A "VJOURNAL" calendar component is defined by the
 * following notation:
 * 
 *   journalc   = "BEGIN" ":" "VJOURNAL" CRLF
 *                jourprop
 *                "END" ":" "VJOURNAL" CRLF
 * 
 *   jourprop   = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              class / created / description / dtstart / dtstamp /
 *              last-mod / organizer / recurid / seq / status /
 *              summary / uid / url /
 * 
 *              ; the following are optional,
 *              ; and MAY occur more than once
 * 
 *              attach / attendee / categories / comment /
 *              contact / exdate / exrule / related / rdate /
 *              rrule / rstatus / x-prop
 * 
 *              )
 * 
 * Description: A "VJOURNAL" calendar component is a grouping of
 * component properties that represent one or more descriptive text
 * notes associated with a particular calendar date. The "DTSTART"
 * property is used to specify the calendar date that the journal entry
 * is associated with. Generally, it will have a DATE value data type,
 * but it can also be used to specify a DATE-TIME value data type.
 * Examples of a journal entry include a daily record of a legislative
 * body or a journal entry of individual telephone contacts for the day
 * or an ordered list of accomplishments for the day. The "VJOURNAL"
 * calendar component can also be used to associate a document with a
 * calendar date.
 * 
 * The "VJOURNAL" calendar component does not take up time on a
 * calendar. Hence, it does not play a role in free or busy time
 * searches - - it is as though it has a time transparency value of
 * TRANSPARENT. It is transparent to any such searches.
 * 
 * The "VJOURNAL" calendar component cannot be nested within another
 * calendar component. However, "VJOURNAL" calendar components can be
 * related to each other or to a "VEVENT" or to a "VTODO" calendar
 * component, with the "RELATED-TO" property.
 * 
 * Example: The following is an example of the "VJOURNAL" calendar
 * component:
 * 
 *   BEGIN:VJOURNAL
 *   UID:19970901T130000Z-123405@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART;VALUE=DATE:19970317
 *   SUMMARY:Staff meeting minutes
 *   DESCRIPTION:1. Staff meeting: Participants include Joe\, Lisa
 *     and Bob. Aurora project plans were reviewed. There is currently
 *     no budget reserves for this project. Lisa will escalate to
 *     management. Next meeting on Tuesday.\n
 *     2. Telephone Conference: ABC Corp. sales representative called
 *     to discuss new printer. Promised to get us a demo by Friday.\n
 *     3. Henry Miller (Handsoff Insurance): Car was totaled by tree.
 *     Is looking into a loaner car. 654-2323 (tel).
 *   END:VJOURNAL
 */
class qCal_Component_Vjournal extends qCal_Component {

	protected $name = "VJOURNAL";
	protected $allowedComponents = array('VCALENDAR');

}