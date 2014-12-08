<?php
/**
 * Request Status Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo This allows some strange stuff in its value. Make sure that it won't
 * break the parser.
 * @todo This allows the specification of "components" within the text. I will
 * need to figure out how to deal with these.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: REQUEST-STATUS
 * 
 * Purpose: This property defines the status code returned for a
 * scheduling request.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard and language property parameters
 * can be specified on this property.
 * 
 * Conformance: The property can be specified in "VEVENT", "VTODO",
 * "VJOURNAL" or "VFREEBUSY" calendar component.
 * 
 * Description: This property is used to return status code information
 * related to the processing of an associated iCalendar object. The data
 * type for this property is TEXT.
 * 
 * The value consists of a short return status component, a longer
 * return status description component, and optionally a status-specific
 * data component. The components of the value are separated by the
 * SEMICOLON character (US-ASCII decimal 59).
 * The short return status is a PERIOD character (US-ASCII decimal 46)
 * separated 3-tuple of integers. For example, "3.1.1". The successive
 * levels of integers provide for a successive level of status code
 * granularity.
 * 
 * The following are initial classes for the return status code.
 * Individual iCalendar object methods will define specific return
 * status codes for these classes. In addition, other classes for the
 * return status code may be defined using the registration process
 * defined later in this memo.
 * 
 *   |==============+===============================================|
 *   | Short Return | Longer Return Status Description              |
 *   | Status Code  |                                               |
 *   |==============+===============================================|
 *   |    1.xx      | Preliminary success. This class of status     |
 *   |              | of status code indicates that the request has |
 *   |              | request has been initially processed but that |
 *   |              | completion is pending.                        |
 *   |==============+===============================================|
 *   |    2.xx      | Successful. This class of status code         |
 *   |              | indicates that the request was completed      |
 *   |              | successfuly. However, the exact status code   |
 *   |              | can indicate that a fallback has been taken.  |
 *   |==============+===============================================|
 *   |    3.xx      | Client Error. This class of status code       |
 *   |              | indicates that the request was not successful.|
 *   |              | The error is the result of either a syntax or |
 *   |              | a semantic error in the client formatted      |
 *   |              | request. Request should not be retried until  |
 *   |              | the condition in the request is corrected.    |
 *   |==============+===============================================|
 *   |    4.xx      | Scheduling Error. This class of status code   |
 *   |              | indicates that the request was not successful.|
 *   |              | Some sort of error occurred within the        |
 *   |              | calendaring and scheduling service, not       |
 *   |              | directly related to the request itself.       |
 *   |==============+===============================================|
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   rstatus    = "REQUEST-STATUS" rstatparam ":"
 *                statcode ";" statdesc [";" extdata]
 * 
 *   rstatparam = *(
 * 
 *              ; the following is optional,
 *              ; but MUST NOT occur more than once
 *           (";" languageparm) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 *   statcode   = 1*DIGIT *("." 1*DIGIT)
 *   ;Hierarchical, numeric return status code
 * 
 *   statdesc   = text
 *   ;Textual status description
 * 
 *   extdata    = text
 *   ;Textual exception data. For example, the offending property
 *   ;name and value or complete property line.
 * 
 * Example: The following are some possible examples of this property.
 * The COMMA and SEMICOLON separator characters in the property value
 * are BACKSLASH character escaped because they appear in a  text value.
 * 
 *   REQUEST-STATUS:2.0;Success
 * 
 *   REQUEST-STATUS:3.1;Invalid property value;DTSTART:96-Apr-01
 * 
 *   REQUEST-STATUS:2.8; Success\, repeating event ignored. Scheduled
 *    as a single event.;RRULE:FREQ=WEEKLY\;INTERVAL=2
 * 
 *   REQUEST-STATUS:4.1;Event conflict. Date/time is busy.
 * 
 *   REQUEST-STATUS:3.7;Invalid calendar user;ATTENDEE:
 *    MAILTO:jsmith@host.com
 */
class qCal_Property_Sequence extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VFREEBUSY');

}