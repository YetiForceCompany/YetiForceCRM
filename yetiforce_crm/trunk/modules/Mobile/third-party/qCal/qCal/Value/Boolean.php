<?php
/**
 * Boolean Value
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 *
 * Value Name: BOOLEAN
 * 
 * Purpose: This value type is used to identify properties that contain
 * either a "TRUE" or "FALSE" Boolean value.
 * 
 * Formal Definition: The value type is defined by the following
 * notation:
 * 
 * 
 *   boolean    = "TRUE" / "FALSE"
 * 
 * Description: These values are case insensitive text. No additional
 * content value encoding (i.e., BACKSLASH character encoding) is
 * defined for this value type.
 * 
 * Example: The following is an example of a hypothetical property that
 * has a BOOLEAN value type:
 * 
 * GIBBERISH:TRUE
 */
class qCal_Value_Boolean extends qCal_Value {

	protected function toString($value) {
	
		return ($value) ? "TRUE" : "FALSE";
	
	}
	/**
	 * Returns boolean of whatever you pass in (by PHP's rules)
	 */
	protected function doCast($value) {
	
		return (boolean) $value;
	
	}

}