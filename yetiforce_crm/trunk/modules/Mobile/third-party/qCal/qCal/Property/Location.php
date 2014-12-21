<?php
/**
 * Location Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * Property Name: LOCATION
 * 
 * Purpose: The property defines the intended venue for the activity
 * defined by a calendar component.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard, alternate text representation and
 * language property parameters can be specified on this property.
 * 
 * Conformance: This property can be specified in "VEVENT" or "VTODO"
 * calendar component.
 * 
 * Description: Specific venues such as conference or meeting rooms may
 * be explicitly specified using this property. An alternate
 * representation may be specified that is a URI that points to
 * directory information with more structured specification of the
 * location. For example, the alternate representation may specify
 * either an LDAP URI pointing to an LDAP server entry or a CID URI
 * pointing to a MIME body part containing a vCard [RFC 2426] for the
 * location.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   location   = "LOCATION locparam ":" text CRLF
 * 
 *   locparam   = *(
 * 
 *              ; the following are optional,
 *              ; but MUST NOT occur more than once
 * 
 *              (";" altrepparam) / (";" languageparam) /
 * 
 *              ; the following is optional,
 *              ; and MAY occur more than once
 * 
 *              (";" xparam)
 * 
 *              )
 * 
 * Example: The following are some examples of this property:
 * 
 *   LOCATION:Conference Room - F123, Bldg. 002
 * 
 *   LOCATION;ALTREP="http://xyzcorp.com/conf-rooms/f123.vcf":
 *    Conference Room - F123, Bldg. 002
 */
class qCal_Property_Location extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT', 'VTODO');

}