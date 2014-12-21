<?php
/**
 * Event Component
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Component Name: "VEVENT"
 * 
 * Purpose: Provide a grouping of component properties that describe an
 * event.
 * 
 * Format Definition: A "VEVENT" calendar component is defined by the
 * following notation:
 * 
 *   eventc     = "BEGIN" ":" "VEVENT" CRLF
 *                eventprop *alarmc
 *                "END" ":" "VEVENT" CRLF
 * 
 *   eventprop  = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              class / created / description / dtstart / geo /
 *              last-mod / location / organizer / priority /
 *              dtstamp / seq / status / summary / transp /
 *              uid / url / recurid /
 * 
 *              ; either 'dtend' or 'duration' may appear in
 *              ; a 'eventprop', but 'dtend' and 'duration'
 *              ; MUST NOT occur in the same 'eventprop'
 * 
 *              dtend / duration /
 * 
 *              ; the following are optional,
 *              ; and MAY occur more than once
 * 
 *              attach / attendee / categories / comment /
 *              contact / exdate / exrule / rstatus / related /
 *              resources / rdate / rrule / x-prop
 * 
 *              )
 * 
 * Description: A "VEVENT" calendar component is a grouping of component
 * properties, and possibly including "VALARM" calendar components, that
 * represents a scheduled amount of time on a calendar. For example, it
 * can be an activity; such as a one-hour long, department meeting from
 * 8:00 AM to 9:00 AM, tomorrow. Generally, an event will take up time
 * on an individual calendar. Hence, the event will appear as an opaque
 * interval in a search for busy time. Alternately, the event can have
 * its Time Transparency set to "TRANSPARENT" in order to prevent
 * blocking of the event in searches for busy time.
 * 
 * The "VEVENT" is also the calendar component used to specify an
 * anniversary or daily reminder within a calendar. These events have a
 * DATE value type for the "DTSTART" property instead of the default
 * data type of DATE-TIME. If such a "VEVENT" has a "DTEND" property, it
 * MUST be specified as a DATE value also. The anniversary type of
 * "VEVENT" can span more than one date (i.e, "DTEND" property value is
 * set to a calendar date after the "DTSTART" property value).
 * 
 * The "DTSTART" property for a "VEVENT" specifies the inclusive start
 * of the event. For recurring events, it also specifies the very first
 * instance in the recurrence set. The "DTEND" property for a "VEVENT"
 * calendar component specifies the non-inclusive end of the event. For
 * cases where a "VEVENT" calendar component specifies a "DTSTART"
 * property with a DATE data type but no "DTEND" property, the events
 * non-inclusive end is the end of the calendar date specified by the
 * "DTSTART" property. For cases where a "VEVENT" calendar component
 * specifies a "DTSTART" property with a DATE-TIME data type but no
 * "DTEND" property, the event ends on the same calendar date and time
 * of day specified by the "DTSTART" property.
 * 
 * The "VEVENT" calendar component cannot be nested within another
 * calendar component. However, "VEVENT" calendar components can be
 * related to each other or to a "VTODO" or to a "VJOURNAL" calendar
 * component with the "RELATED-TO" property.
 * 
 * Example: The following is an example of the "VEVENT" calendar
 * component used to represent a meeting that will also be opaque to
 * searches for busy time:
 * 
 *   BEGIN:VEVENT
 *   UID:19970901T130000Z-123401@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19970903T163000Z
 *   DTEND:19970903T190000Z
 *   SUMMARY:Annual Employee Review
 *   CLASS:PRIVATE
 *   CATEGORIES:BUSINESS,HUMAN RESOURCES
 *   END:VEVENT
 * 
 * The following is an example of the "VEVENT" calendar component used
 * to represent a reminder that will not be opaque, but rather
 * transparent, to searches for busy time:
 * 
 *   BEGIN:VEVENT
 *   UID:19970901T130000Z-123402@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19970401T163000Z
 *   DTEND:19970402T010000Z
 *   SUMMARY:Laurel is in sensitivity awareness class.
 *   CLASS:PUBLIC
 *   CATEGORIES:BUSINESS,HUMAN RESOURCES
 *   TRANSP:TRANSPARENT
 *   END:VEVENT
 * 
 * The following is an example of the "VEVENT" calendar component used
 * to represent an anniversary that will occur annually. Since it takes
 * up no time, it will not appear as opaque in a search for busy time;
 * no matter what the value of the "TRANSP" property indicates:
 * 
 *   BEGIN:VEVENT
 *   UID:19970901T130000Z-123403@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19971102
 *   SUMMARY:Our Blissful Anniversary
 *   CLASS:CONFIDENTIAL
 *   CATEGORIES:ANNIVERSARY,PERSONAL,SPECIAL OCCASION
 *   RRULE:FREQ=YEARLY
 *   END:VEVENT
 */
class qCal_Component_Vevent extends qCal_Component {

	protected $name = "VEVENT";
	protected $allowedComponents = array('VCALENDAR');
	protected function doValidation() {
	
		$properties = $this->getProperties();
		$propnames = array_keys($properties);
		if (in_array('DTEND', $propnames) && in_array('DURATION', $propnames)) {
			throw new qCal_Exception_InvalidProperty('DTEND and DURATION cannot both occur in the same VEVENT component');
		}
		if (in_array('DTSTART', $propnames)) {
			$dtstart = $this->getProperty('dtstart');
			$dtstart = $dtstart[0];
			// check that if dtstart is a DATE that dtend is a DATE
			if ($dtstart->getType() == 'DATE') {
				if (in_array('DTEND', $propnames)) {
					$dtend = $this->getProperty('dtend');
					$dtend = $dtend[0];
					if ($dtend->getType() != 'DATE') {
						throw new qCal_Exception_InvalidProperty('If DTSTART property is specified as a DATE property, so must DTEND');
					}
				}
			}
			// check that dtstart comes before dtend
			if (in_array('DTEND', $propnames)) {
				$dtend = $this->getProperty('dtend');
				$dtend = $dtend[0];
				$startdate = strtotime($dtstart->getValue());
				$enddate = strtotime($dtend->getValue());
				if ($startdate > $enddate) {
					throw new qCal_Exception_InvalidProperty('DTSTART property must come before DTEND');
				}
			}
		}
	
	}

}