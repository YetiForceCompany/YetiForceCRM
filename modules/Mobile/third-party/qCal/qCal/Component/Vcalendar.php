<?php
/**
 * Calendar Component
 * This is the outer-most object in an icalendar file that represents
 * the calendar as a whole. All other components must be nested within
 * this component.
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Implement a method of NOT allowing more than one of properties
 *       such as METHOD to be set.
 * 
 * RFC 2445 Definition
 *
 * The Calendaring and Scheduling Core Object is a collection of
 * calendaring and scheduling information. Typically, this information
 * will consist of a single iCalendar object. However, multiple
 * iCalendar objects can be sequentially grouped together. The first
 * line and last line of the iCalendar object MUST contain a pair of
 * iCalendar object delimiter strings. The syntax for an iCalendar
 * object is as follows:
 * 
 *   icalobject = 1*("BEGIN" ":" "VCALENDAR" CRLF
 *                icalbody
 *                "END" ":" "VCALENDAR" CRLF)
 * 
 * The following is a simple example of an iCalendar object:
 * 
 *   BEGIN:VCALENDAR
 *   VERSION:2.0
 *   PRODID:-//hacksw/handcal//NONSGML v1.0//EN
 *   BEGIN:VEVENT
 *   DTSTART:19970714T170000Z
 *   DTEND:19970715T035959Z
 *   SUMMARY:Bastille Day Party
 *   END:VEVENT
 *   END:VCALENDAR
 */
class qCal_Component_Vcalendar extends qCal_Component {

	protected $name = "VCALENDAR";
	protected $requiredProperties = array('PRODID','VERSION');
	/**
	 * vcalendar objects have a number of requirements defined in the RFC just as most other
	 * components do. Each has a global set of validation rules as well as their own set. This
	 * is the set of rules defined by the vcalendar object. 
	 */
	public function doValidation() {
	
		// @todo make sure that all tzids that are specified have a corresponding vtimezone
		// look for tzids and make sure there are corresponding vtimezone components for each tzid
		// In order to be sure I find all tzids, I need to search through the entire tree, so either
		// I need a recursive getProperties() or I need to use a stack to find all of them.
		
	
	}

}