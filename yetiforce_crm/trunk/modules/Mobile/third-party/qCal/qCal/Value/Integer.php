<?php
/**
 * Integer Value
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Should integer/float/boolean be related somehow? Inheritance?
 * 
 * Value Name:INTEGER
 * 
 * Purpose: This value type is used to identify properties that contain
 * a signed integer value.
 * 
 * Formal Definition: The value type is defined by the following
 * notation:
 * 
 *  integer    = (["+"] / "-") 1*DIGIT
 * 
 * Description: If the property permits, multiple "integer" values are
 * specified by a COMMA character (US-ASCII decimal 44) separated list
 * of values. The valid range for "integer" is -2147483648 to
 * 2147483647. If the sign is not specified, then the value is assumed
 * to be positive.
 * 
 * No additional content value encoding (i.e., BACKSLASH character
 * encoding) is defined for this value type.
 * 
 * Example:
 * 
 *  1234567890
 *  -1234567890
 *  +1234567890
 *  432109876
 */
class qCal_Value_Integer extends qCal_Value {

	/**
	 * Casts $value to integer and returns it
	 */
	protected function doCast($value) {
	
		return (integer) $value;
	
	}

}