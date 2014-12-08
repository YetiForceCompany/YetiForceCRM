<?php
/**
 * Todo Component
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Component Name: VTODO
 * 
 * Purpose: Provide a grouping of calendar properties that describe a
 * to-do.
 * 
 * Formal Definition: A "VTODO" calendar component is defined by the
 * following notation:
 * 
 *   todoc      = "BEGIN" ":" "VTODO" CRLF
 *                todoprop *alarmc
 *                "END" ":" "VTODO" CRLF
 * 
 *   todoprop   = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              class / completed / created / description / dtstamp /
 *              dtstart / geo / last-mod / location / organizer /
 *              percent / priority / recurid / seq / status /
 *              summary / uid / url /
 * 
 *              ; either 'due' or 'duration' may appear in
 *              ; a 'todoprop', but 'due' and 'duration'
 *              ; MUST NOT occur in the same 'todoprop'
 * 
 *              due / duration /
 * 
 *              ; the following are optional,
 *              ; and MAY occur more than once
 *              attach / attendee / categories / comment / contact /
 *              exdate / exrule / rstatus / related / resources /
 *              rdate / rrule / x-prop
 * 
 *              )
 * 
 * Description: A "VTODO" calendar component is a grouping of component
 * properties and possibly "VALARM" calendar components that represent
 * an action-item or assignment. For example, it can be used to
 * represent an item of work assigned to an individual; such as "turn in
 * travel expense today".
 * 
 * The "VTODO" calendar component cannot be nested within another
 * calendar component. However, "VTODO" calendar components can be
 * related to each other or to a "VTODO" or to a "VJOURNAL" calendar
 * component with the "RELATED-TO" property.
 * 
 * A "VTODO" calendar component without the "DTSTART" and "DUE" (or
 * "DURATION") properties specifies a to-do that will be associated with
 * each successive calendar date, until it is completed.
 * 
 * Example: The following is an example of a "VTODO" calendar component:
 * 
 *   BEGIN:VTODO
 *   UID:19970901T130000Z-123404@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19970415T133000Z
 *   DUE:19970416T045959Z
 *   SUMMARY:1996 Income Tax Preparation
 *   CLASS:CONFIDENTIAL
 *   CATEGORIES:FAMILY,FINANCE
 *   PRIORITY:1
 *   STATUS:NEEDS-ACTION
 *   END:VTODO
 */
class qCal_Component_Vtodo extends qCal_Component {

	protected $name = "VTODO";
	protected $allowedComponents = array('VCALENDAR');

}