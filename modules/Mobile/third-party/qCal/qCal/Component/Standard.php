<?php
/**
 * Timezone Component
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Component Name: VTIMEZONE
 * 
 * Purpose: Provide a grouping of component properties that defines a
 * time zone.
 * 
 * Formal Definition: A "VTIMEZONE" calendar component is defined by the
 * following notation:
 * 
 *   timezonec  = "BEGIN" ":" "VTIMEZONE" CRLF
 * 
 *                2*(
 * 
 *                ; 'tzid' is required, but MUST NOT occur more
 *                ; than once
 * 
 *              tzid /
 * 
 *                ; 'last-mod' and 'tzurl' are optional,
 *              but MUST NOT occur more than once
 * 
 *              last-mod / tzurl /
 * 
 *                ; one of 'standardc' or 'daylightc' MUST occur
 *              ..; and each MAY occur more than once.
 * 
 *              standardc / daylightc /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *                x-prop
 * 
 *                )
 * 
 *                "END" ":" "VTIMEZONE" CRLF
 * 
 *   standardc  = "BEGIN" ":" "STANDARD" CRLF
 * 
 *                tzprop
 * 
 *                "END" ":" "STANDARD" CRLF
 * 
 *   daylightc  = "BEGIN" ":" "DAYLIGHT" CRLF
 * 
 *                tzprop
 * 
 *                "END" ":" "DAYLIGHT" CRLF
 * 
 *   tzprop     = 3*(
 * 
 *              ; the following are each REQUIRED,
 *              ; but MUST NOT occur more than once
 * 
 *              dtstart / tzoffsetto / tzoffsetfrom /
 * 
 *              ; the following are optional,
 *              ; and MAY occur more than once
 * 
 *              comment / rdate / rrule / tzname / x-prop
 * 
 *              )
 * 
 * Description: A time zone is unambiguously defined by the set of time
 * measurement rules determined by the governing body for a given
 * geographic area. These rules describe at a minimum the base  offset
 * from UTC for the time zone, often referred to as the Standard Time
 * offset. Many locations adjust their Standard Time forward or backward
 * by one hour, in order to accommodate seasonal changes in number of
 * daylight hours, often referred to as Daylight  Saving Time. Some
 * locations adjust their time by a fraction of an hour. Standard Time
 * is also known as Winter Time. Daylight Saving Time is also known as
 * Advanced Time, Summer Time, or Legal Time in certain countries. The
 * following table shows the changes in time zone rules in effect for
 * New York City starting from 1967. Each line represents a description
 * or rule for a particular observance.
 * 
 *   Effective Observance Rule
 * 
 *   Date       (Date/Time)             Offset  Abbreviation
 * 
 *   1967-*     last Sun in Oct, 02:00  -0500   EST
 * 
 *   1967-1973  last Sun in Apr, 02:00  -0400   EDT
 * 
 *   1974-1974  Jan 6,  02:00           -0400   EDT
 * 
 *   1975-1975  Feb 23, 02:00           -0400   EDT
 * 
 *   1976-1986  last Sun in Apr, 02:00  -0400   EDT
 * 
 *   1987-*     first Sun in Apr, 02:00 -0400   EDT
 * 
 *      Note: The specification of a global time zone registry is not
 *      addressed by this document and is left for future study.
 *      However, implementers may find the Olson time zone database [TZ]
 *      a useful reference. It is an informal, public-domain collection
 *      of time zone information, which is currently being maintained by
 *      volunteer Internet participants, and is used in several
 *      operating systems. This database contains current and historical
 *      time zone information for a wide variety of locations around the
 *      globe; it provides a time zone identifier for every unique time
 *      zone rule set in actual use since 1970, with historical data
 *      going back to the introduction of standard time.
 * 
 * Interoperability between two calendaring and scheduling applications,
 * especially for recurring events, to-dos or journal entries, is
 * dependent on the ability to capture and convey date and time
 * information in an unambiguous format. The specification of current
 * time zone information is integral to this behavior.
 * 
 * If present, the "VTIMEZONE" calendar component defines the set of
 * Standard Time and Daylight Saving Time observances (or rules) for a
 * particular time zone for a given interval of time. The "VTIMEZONE"
 * calendar component cannot be nested within other calendar components.
 * Multiple "VTIMEZONE" calendar components can exist in an iCalendar
 * object. In this situation, each "VTIMEZONE" MUST represent a unique
 * 
 * time zone definition. This is necessary for some classes of events,
 * such as airline flights, that start in one time zone and end in
 * another.
 * 
 * The "VTIMEZONE" calendar component MUST be present if the iCalendar
 * object contains an RRULE that generates dates on both sides of a time
 * zone shift (e.g. both in Standard Time and Daylight Saving Time)
 * unless the iCalendar object intends to convey a floating time (See
 * the section "4.1.10.11 Time" for proper interpretation of floating
 * time). It can be present if the iCalendar object does not contain
 * such a RRULE. In addition, if a RRULE is present, there MUST be valid
 * time zone information for all recurrence instances.
 * 
 * The "VTIMEZONE" calendar component MUST include the "TZID" property
 * and at least one definition of a standard or daylight component. The
 * standard or daylight component MUST include the "DTSTART",
 * "TZOFFSETFROM" and "TZOFFSETTO" properties.
 * 
 * An individual "VTIMEZONE" calendar component MUST be specified for
 * each unique "TZID" parameter value specified in the iCalendar object.
 * 
 * Each "VTIMEZONE" calendar component consists of a collection of one
 * or more sub-components that describe the rule for a particular
 * observance (either a Standard Time or a Daylight Saving Time
 * observance). The "STANDARD" sub-component consists of a collection of
 * properties that describe Standard Time. The "DAYLIGHT" sub-component
 * consists of a collection of properties that describe Daylight Saving
 * Time. In general this collection of properties consists of:
 * 
 *      - the first onset date-time for the observance
 * 
 *      - the last onset date-time for the observance, if a last onset
 *        is known.
 * 
 *      - the offset to be applied for the observance
 * 
 *      - a rule that describes the day and time when the observance
 *        takes effect
 * 
 *      - an optional name for the observance
 * 
 * For a given time zone, there may be multiple unique definitions of
 * the observances over a period of time. Each observance is described
 * using either a "STANDARD" or "DAYLIGHT" sub-component. The collection
 * of these sub-components is used to describe the time zone for a given
 * period of time. The offset to apply at any given time is found by
 * locating the observance that has the last onset date and time before
 * the time in question, and using the offset value from that
 * observance.
 * 
 * The top-level properties in a "VTIMEZONE" calendar component are:
 * 
 * The mandatory "TZID" property is a text value that uniquely
 * identifies the VTIMZONE calendar component within the scope of an
 * iCalendar object.
 * 
 * The optional "LAST-MODIFIED" property is a UTC value that specifies
 * the date and time that this time zone definition was last updated.
 * 
 * The optional "TZURL" property is url value that points to a published
 * VTIMEZONE definition. TZURL SHOULD refer to a resource that is
 * accessible by anyone who might need to interpret the object. This
 * SHOULD NOT normally be a file: URL or other URL that is not widely-
 * accessible.
 * 
 * The collection of properties that are used to define the STANDARD and
 * DAYLIGHT sub-components include:
 * 
 * The mandatory "DTSTART" property gives the effective onset date and
 * local time for the time zone sub-component definition. "DTSTART" in
 * this usage MUST be specified as a local DATE-TIME value.
 * 
 * The mandatory "TZOFFSETFROM" property gives the UTC offset which is
 * in use when the onset of this time zone observance begins.
 * "TZOFFSETFROM" is combined with "DTSTART" to define the effective
 * onset for the time zone sub-component definition. For example, the
 * following represents the time at which the observance of Standard
 * Time took effect in Fall 1967 for New York City:
 * 
 *   DTSTART:19671029T020000
 * 
 *   TZOFFSETFROM:-0400
 * 
 * The mandatory "TZOFFSETTO " property gives the UTC offset for the
 * time zone sub-component (Standard Time or Daylight Saving Time) when
 * this observance is in use.
 * 
 * The optional "TZNAME" property is the customary name for the time
 * zone. It may be specified multiple times, to allow for specifying
 * multiple language variants of the time zone names. This could be used
 * for displaying dates.
 * 
 * If specified, the onset for the observance defined by the time zone
 * sub-component is defined by either the "RRULE" or "RDATE" property.
 * If neither is specified, only one sub-component can be specified in
 * the "VTIMEZONE" calendar component and it is assumed that the single
 * observance specified is always in effect.
 * 
 * The "RRULE" property defines the recurrence rule for the onset of the
 * observance defined by this time zone sub-component. Some specific
 * requirements for the usage of RRULE for this purpose include:
 * 
 *      - If observance is known to have an effective end date, the
 *      "UNTIL" recurrence rule parameter MUST be used to specify the
 *      last valid onset of this observance (i.e., the UNTIL date-time
 *      will be equal to the last instance generated by the recurrence
 *      pattern). It MUST be specified in UTC time.
 * 
 *      - The "DTSTART" and the "TZOFFSETTO" properties MUST be used
 *      when generating the onset date-time values (instances) from the
 *      RRULE.
 * 
 * Alternatively, the "RDATE" property can be used to define the onset
 * of the observance by giving the individual onset date and times.
 * "RDATE" in this usage MUST be specified as a local DATE-TIME value in
 * UTC time.
 * 
 * The optional "COMMENT" property is also allowed for descriptive
 * explanatory text.
 * 
 * Example: The following are examples of the "VTIMEZONE" calendar
 * component:
 * 
 * This is an example showing time zone information for the Eastern
 * United States using "RDATE" property. Note that this is only suitable
 * for a recurring event that starts on or later than April 6, 1997 at
 * 03:00:00 EDT (i.e., the earliest effective transition date and time)
 * and ends no later than April 7, 1998 02:00:00 EST (i.e., latest valid
 * date and time for EST in this scenario). For example, this can be
 * used for a recurring event that occurs every Friday, 8am-9:00 AM,
 * starting June 1, 1997, ending December 31, 1997.
 * 
 *   BEGIN:VTIMEZONE
 *   TZID:US-Eastern
 *   LAST-MODIFIED:19870101T000000Z
 *   BEGIN:STANDARD
 *   DTSTART:19971026T020000
 *   RDATE:19971026T020000
 *   TZOFFSETFROM:-0400
 *   TZOFFSETTO:-0500
 *   TZNAME:EST
 *   END:STANDARD
 *   BEGIN:DAYLIGHT
 *   DTSTART:19971026T020000
 *   RDATE:19970406T020000
 *   TZOFFSETFROM:-0500
 *   TZOFFSETTO:-0400
 *   TZNAME:EDT
 *   END:DAYLIGHT
 *   END:VTIMEZONE
 * 
 * This is a simple example showing the current time zone rules for the
 * Eastern United States using a RRULE recurrence pattern. Note that
 * there is no effective end date to either of the Standard Time or
 * Daylight Time rules. This information would be valid for a recurring
 * event starting today and continuing indefinitely.
 * 
 *   BEGIN:VTIMEZONE
 *   TZID:US-Eastern
 *   LAST-MODIFIED:19870101T000000Z
 *   TZURL:http://zones.stds_r_us.net/tz/US-Eastern
 *   BEGIN:STANDARD
 *   DTSTART:19671029T020000
 *   RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
 *   TZOFFSETFROM:-0400
 *   TZOFFSETTO:-0500
 *   TZNAME:EST
 *   END:STANDARD
 *   BEGIN:DAYLIGHT
 *   DTSTART:19870405T020000
 *   RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4
 *   TZOFFSETFROM:-0500
 *   TZOFFSETTO:-0400
 *   TZNAME:EDT
 *   END:DAYLIGHT
 *   END:VTIMEZONE
 * 
 * This is an example showing a fictitious set of rules for the Eastern
 * United States, where the Daylight Time rule has an effective end date
 * (i.e., after that date, Daylight Time is no longer observed).
 * 
 *   BEGIN:VTIMEZONE
 *   TZID:US--Fictitious-Eastern
 *   LAST-MODIFIED:19870101T000000Z
 *   BEGIN:STANDARD
 *   DTSTART:19671029T020000
 *   RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
 *   TZOFFSETFROM:-0400
 *   TZOFFSETTO:-0500
 *   TZNAME:EST
 *   END:STANDARD
 * 
 * 
 *   BEGIN:DAYLIGHT
 *   DTSTART:19870405T020000
 *   RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4;UNTIL=19980404T070000Z
 *   TZOFFSETFROM:-0500
 *   TZOFFSETTO:-0400
 *   TZNAME:EDT
 *   END:DAYLIGHT
 *   END:VTIMEZONE
 * 
 * This is an example showing a fictitious set of rules for the Eastern
 * United States, where the first Daylight Time rule has an effective
 * end date. There is a second Daylight Time rule that picks up where
 * the other left off.
 * 
 *   BEGIN:VTIMEZONE
 *   TZID:US--Fictitious-Eastern
 *   LAST-MODIFIED:19870101T000000Z
 *   BEGIN:STANDARD
 *   DTSTART:19671029T020000
 *   RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
 *   TZOFFSETFROM:-0400
 *   TZOFFSETTO:-0500
 *   TZNAME:EST
 *   END:STANDARD
 *   BEGIN:DAYLIGHT
 *   DTSTART:19870405T020000
 *   RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=4;UNTIL=19980404T070000Z
 *   TZOFFSETFROM:-0500
 *   TZOFFSETTO:-0400
 *   TZNAME:EDT
 *   END:DAYLIGHT
 *   BEGIN:DAYLIGHT
 *   DTSTART:19990424T020000
 *   RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=4
 *   TZOFFSETFROM:-0500
 *   TZOFFSETTO:-0400
 *   TZNAME:EDT
 *   END:DAYLIGHT
 *   END:VTIMEZONE
 */
class qCal_Component_Standard extends qCal_Component {

	protected $name = "STANDARD";
	protected $allowedComponents = array('VTIMEZONE');
	protected $requiredProperties = array('DTSTART','TZOFFSETFROM','TZOFFSETTO');

}