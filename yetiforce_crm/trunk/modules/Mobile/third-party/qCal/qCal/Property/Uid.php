<?php
/**
 * Unique Identifier Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo The default value of this could be generated (so that unless
 * 	     otherwise specified, the uid would be generated)
 * @todo Look into the RFC 822 spec and implement it if possible.
 * @todo Several properties make use of a domain. Maybe there should be a method
 * of globally specifying a domain.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: UID
 * 
 * Purpose: This property defines the persistent, globally unique
 * identifier for the calendar component.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: The property MUST be specified in the "VEVENT", "VTODO",
 * "VJOURNAL" or "VFREEBUSY" calendar components.
 * 
 * Description: The UID itself MUST be a globally unique identifier. The
 * generator of the identifier MUST guarantee that the identifier is
 * unique. There are several algorithms that can be used to accomplish
 * this. The identifier is RECOMMENDED to be the identical syntax to the
 * [RFC 822] addr-spec. A good method to assure uniqueness is to put the
 * domain name or a domain literal IP address of the host on which the
 * identifier was created on the right hand side of the "@", and on the
 * left hand side, put a combination of the current calendar date and
 * time of day (i.e., formatted in as a DATE-TIME value) along with some
 * other currently unique (perhaps sequential) identifier available on
 * the system (for example, a process id number). Using a date/time
 * value on the left hand side and a domain name or domain literal on
 * the right hand side makes it possible to guarantee uniqueness since
 * no two hosts should be using the same domain name or IP address at
 * the same time. Though other algorithms will work, it is RECOMMENDED
 * that the right hand side contain some domain identifier (either of
 * the host itself or otherwise) such that the generator of the message
 * identifier can guarantee the uniqueness of the left hand side within
 * the scope of that domain.
 * 
 * This is the method for correlating scheduling messages with the
 * referenced "VEVENT", "VTODO", or "VJOURNAL" calendar component.
 * 
 * The full range of calendar components specified by a recurrence set
 * is referenced by referring to just the "UID" property value
 * corresponding to the calendar component. The "RECURRENCE-ID" property
 * allows the reference to an individual instance within the recurrence
 * set.
 * 
 * This property is an important method for group scheduling
 * applications to match requests with later replies, modifications or
 * deletion requests. Calendaring and scheduling applications MUST
 * generate this property in "VEVENT", "VTODO" and "VJOURNAL" calendar
 * components to assure interoperability with other group scheduling
 * applications. This identifier is created by the calendar system that
 * generates an iCalendar object.
 * 
 * Implementations MUST be able to receive and persist values of at
 * least 255 characters for this property.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   uid        = "UID" uidparam ":" text CRLF
 * 
 *   uidparam   = *(";" xparam)
 * 
 * Example: The following is an example of this property:
 * 
 *   UID:19960401T080045Z-4000F192713-0052@host1.com
 */
class qCal_Property_Uid extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VFREEBUSY');

}