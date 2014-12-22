<?php
/**
 * Product Identifier Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Find a way to make sure that this is a globally unique id
 * @todo I don't want my name in the default for this. Actually, I'm not even
 * sure I want this property to have a default.
 * 
 * RFC 2445 Definition
 * 
 * Property Name: PRODID
 * 
 * Purpose: This property specifies the identifier for the product that
 * created the iCalendar object.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard property parameters can be
 * specified on this property.
 * 
 * Conformance: The property MUST be specified once in an iCalendar
 * object.
 * 
 * Description: The vendor of the implementation SHOULD assure that this
 * is a globally unique identifier; using some technique such as an FPI
 * value, as defined in [ISO 9070].
 * 
 * This property SHOULD not be used to alter the interpretation of an
 * iCalendar object beyond the semantics specified in this memo. For
 * example, it is not to be used to further the understanding of non-
 * standard properties.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   prodid     = "PRODID" pidparam ":" pidvalue CRLF
 * 
 *   pidparam   = *(";" xparam)
 * 
 *   pidvalue   = text
 *   ;Any text that describes the product and version
 *   ;and that is generally assured of being unique.
 * 
 * Example: The following is an example of this property. It does not
 * imply that English is the default language.
 * 
 *   PRODID:-//ABC Corporation//NONSGML My Product//EN
 */
class qCal_Property_Prodid extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VCALENDAR');
	protected $default = "-//Luke Visinoni//qCal v0.1//EN";

}