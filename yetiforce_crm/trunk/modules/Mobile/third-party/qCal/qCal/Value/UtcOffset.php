<?php
/**
 * TimeZone Offset Value
 * Some properties require a timezone offset. This specifies what that
 * offset is
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * Value Name: UTC-OFFSET
 * 
 * Purpose: This value type is used to identify properties that contain
 * an offset from UTC to local time.
 * 
 * Formal Definition: The data type is defined by the following
 * notation:
 * 
 *  utc-offset = time-numzone  ;As defined above in time data type
 * 
 *  time-numzone       = ("+" / "-") time-hour time-minute [time-
 *  second]
 * 
 * Description: The PLUS SIGN character MUST be specified for positive
 * UTC offsets (i.e., ahead of UTC). The value of "-0000" and "-000000"
 * are not allowed. The time-second, if present, may not be 60; if
 * absent, it defaults to zero.
 * 
 * No additional content value encoding (i.e., BACKSLASH character
 * encoding) is defined for this value type.
 * 
 * Example: The following UTC offsets are given for standard time for
 * New York (five hours behind UTC) and Geneva (one hour ahead of UTC):
 * 
 *  -0500
 * 
 *  +0100
 */
class qCal_Value_UtcOffset extends qCal_Value {

	/**
	 * @todo: implement this
	 */
	protected function doCast($value) {
	
		return $value;
	
	}

}