<?php
/**
 * Contact Property
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * 
 * RFC 2445 Definition
 * 
 * Property Name: CONTACT
 * 
 * Purpose: The property is used to represent contact information or
 * alternately a reference to contact information associated with the
 * calendar component.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard, alternate text representation and
 * language property parameters can be specified on this property.
 * 
 * Conformance: The property can be specified in a "VEVENT", "VTODO",
 * "VJOURNAL" or "VFREEBUSY" calendar component.
 * 
 * Description: The property value consists of textual contact
 * information. An alternative representation for the property value can
 * also be specified that refers to a URI pointing to an alternate form,
 * such as a vCard [RFC 2426], for the contact information.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   contact    = "CONTACT" contparam ":" text CRLF
 * 
 *   contparam  = *(
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
 * Example: The following is an example of this property referencing
 * textual contact information:
 * 
 *   CONTACT:Jim Dolittle\, ABC Industries\, +1-919-555-1234
 * 
 * The following is an example of this property with an alternate
 * representation of a LDAP URI to a directory entry containing the
 * contact information:
 * 
 *   CONTACT;ALTREP="ldap://host.com:6666/o=3DABC%20Industries\,
 *    c=3DUS??(cn=3DBJim%20Dolittle)":Jim Dolittle\, ABC Industries\,
 *    +1-919-555-1234
 * 
 * The following is an example of this property with an alternate
 * representation of a MIME body part containing the contact
 * information, such as a vCard [RFC 2426] embedded in a [MIME-DIR]
 * content-type:
 * 
 *   CONTACT;ALTREP="CID=<part3.msg970930T083000SILVER@host.com>":Jim
 *     Dolittle\, ABC Industries\, +1-919-555-1234
 * 
 * The following is an example of this property referencing a network
 * resource, such as a vCard [RFC 2426] object containing the contact
 * information:
 * 
 *   CONTACT;ALTREP="http://host.com/pdi/jdoe.vcf":Jim
 *     Dolittle\, ABC Industries\, +1-919-555-1234
 */
class qCal_Property_Contact extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL','VFREEBUSY');

}