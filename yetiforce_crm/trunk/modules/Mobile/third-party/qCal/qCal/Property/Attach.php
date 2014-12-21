<?php
/**
 * Attach Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: ATTACH
 * 
 * Purpose: The property provides the capability to associate a document
 * object with a calendar component.
 * 
 * Value Type: The default value type for this property is URI. The
 * value type can also be set to BINARY to indicate inline binary
 * encoded content information.
 * 
 * Property Parameters: Non-standard, inline encoding, format type and
 * value data type property parameters can be specified on this
 * property.
 * 
 * Conformance: The property can be specified in a "VEVENT", "VTODO",
 * "VJOURNAL" or "VALARM" calendar components.
 * 
 * Description: The property can be specified within "VEVENT", "VTODO",
 * "VJOURNAL", or "VALARM" calendar components. This property can be
 * specified multiple times within an iCalendar object.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   attach     = "ATTACH" attparam ":" uri  CRLF
 * 
 *   attach     =/ "ATTACH" attparam ";" "ENCODING" "=" "BASE64"
 *                 ";" "VALUE" "=" "BINARY" ":" binary
 * 
 *   attparam   = *(
 * 
 *              ; the following is optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" fmttypeparam) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 * Example: The following are examples of this property:
 * 
 *   ATTACH:CID:jsmith.part3.960817T083000.xyzMail@host1.com
 * 
 *   ATTACH;FMTTYPE=application/postscript:ftp://xyzCorp.com/pub/
 *    reports/r-960812.ps
 */
class qCal_Property_Attach extends qCal_Property {

	protected $type = 'URI';
	protected $allowedComponents = array('VALARM','VEVENT','VJOURNAL','VTODO');
	protected $allowMultiple = true;

}