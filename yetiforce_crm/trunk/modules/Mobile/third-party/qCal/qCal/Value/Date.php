<?php
/**
 * Date Value
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * Value Name: DATE
 * 
 * Purpose: This value type is used to identify values that contain a
 * calendar date.
 * 
 * Formal Definition: The value type is defined by the following
 * notation:
 * 
 *  date               = date-value
 *  date-value         = date-fullyear date-month date-mday
 *  date-fullyear      = 4DIGIT
 *  date-month         = 2DIGIT        ;01-12
 *  date-mday          = 2DIGIT        ;01-28, 01-29, 01-30, 01-31
 *                                     ;based on month/year
 * 
 * Description: If the property permits, multiple "date" values are
 * specified as a COMMA character (US-ASCII decimal 44) separated list
 * of values. The format for the value type is expressed as the [ISO
 * 8601] complete representation, basic format for a calendar date. The
 * textual format specifies a four-digit year, two-digit month, and
 * two-digit day of the month. There are no separator characters between
 * the year, month and day component text.
 * 
 * No additional content value encoding (i.e., BACKSLASH character
 * encoding) is defined for this value type.
 * 
 * Example: The following represents July 14, 1997:
 * 
 *  19970714
 */
class qCal_Value_Date extends qCal_Value {

	/**
	 * qCal_Date object
	 */
	protected $value;
	/**
	 * Convert the internal date storage to a string
	 */
	protected function toString($value) {
	
		return $value->format('Ymd');
	
	}
	/**
	 * This converts to a qCal_Date for internal storage
	 */
	protected function doCast($value) {
	
		$date = qCal_Date::factory($value);
		return $date;
	
	}

}