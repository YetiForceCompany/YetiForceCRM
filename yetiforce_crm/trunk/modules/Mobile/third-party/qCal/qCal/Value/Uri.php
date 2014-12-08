<?php
/**
 * URI Value
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * Value Name: URI
 * 
 * Purpose: This value type is used to identify values that contain a
 * uniform resource identifier (URI) type of reference to the property
 * value.
 * 
 * Formal Definition: The data type is defined by the following
 * notation:
 * 
 *  uri        = <As defined by any IETF RFC>
 * 
 * Description: This data type might be used to reference binary
 * information, for values that are large, or otherwise undesirable to
 * include directly in the iCalendar object.
 * 
 * The URI value formats in RFC 1738, RFC 2111 and any other IETF
 * registered value format can be specified.
 * 
 * Any IANA registered URI format can be used. These include, but are
 * not limited to, those defined in RFC 1738 and RFC 2111.
 * 
 * When a property parameter value is a URI value type, the URI MUST be
 * specified as a quoted-string value.
 * 
 * No additional content value encoding (i.e., BACKSLASH character
 * encoding) is defined for this value type.
 * 
 * Example: The following is a URI for a network file:
 * 
 *  http://host1.com/my-report.txt
 */
class qCal_Value_Uri extends qCal_Value {

	protected function toString($value) {
	
		return (string) $value;
	
	}
	/**
	 * @todo: implement this
	 */
	protected function doCast($value) {
	
		return $value;
	
	}

}