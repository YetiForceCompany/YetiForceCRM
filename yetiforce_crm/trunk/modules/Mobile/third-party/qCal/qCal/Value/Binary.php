<?php
/**
 * Binary Value
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo Should integer/float/boolean be related somehow? Inheritance?
 * 
 * RFC 2445 Definition
 *
 * Value Name: BINARY
 * 
 * Purpose: This value type is used to identify properties that contain
 * a character encoding of inline binary data. For example, an inline
 * attachment of an object code might be included in an iCalendar
 * object.
 * 
 * Formal Definition: The value type is defined by the following
 * notation:
 * 
 *   binary     = *(4b-char) [b-end]
 *   ; A "BASE64" encoded character string, as defined by [RFC 2045].
 * 
 *   b-end      = (2b-char "==") / (3b-char "=")
 * 
 *   b-char = ALPHA / DIGIT / "+" / "/"
 * 
 * Description: Property values with this value type MUST also include
 * the inline encoding parameter sequence of ";ENCODING=BASE64". That
 * is, all inline binary data MUST first be character encoded using the
 * "BASE64" encoding method defined in [RFC 2045]. No additional content
 * value encoding (i.e., BACKSLASH character encoding) is defined for
 * this value type.
 * 
 * Example: The following is an abridged example of a "BASE64" encoded
 * binary value data.
 * 
 *   ATTACH;VALUE=BINARY;ENCODING=BASE64:MIICajCCAdOgAwIBAgICBEUwDQY
 *    JKoZIhvcNAQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlI
 *    ENvbW11bmljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZv
 *      <...remainder of "BASE64" encoded binary data...>
 * 
 * qCal_DataType_Binary
 * This object defines any binary object that may be attached to an
 * icalendar file.
 */
class qCal_Value_Binary extends qCal_Value {

	/**
	 * When the value of a binary property is requested, it will be returned as a base64 encoded string
	 * @todo Base64 is the only encoding supported by this standard, but the encoding=base64 parameter must be
	 * provided regardless.
	 */
	protected function toString($value) {
	
		return base64_encode($value);
	
	}
	/**
	 * Binary can be store as-is I believe, so don't change it
	 */
	protected function doCast($value) {
	
		return $value;
	
	}

}