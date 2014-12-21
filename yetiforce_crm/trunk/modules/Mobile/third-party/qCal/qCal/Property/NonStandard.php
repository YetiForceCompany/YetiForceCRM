<?php
/**
 * Non-standard Property(s)
 * @package qCal
 * @copyright Luke Visinoni (luke.visinoni@gmail.com)
 * @author Luke Visinoni (luke.visinoni@gmail.com)
 * @license GNU Lesser General Public License
 * @todo I am not sure exactly how I plan on dealing with non-standard
 *       properties, but for now, I'm representing them with this class
 * @todo Should this be a MultiValue?
 * @todo Should this allow multiple instances?
 * 
 * RFC 2445 Definition
 * 
 * Property Name: Any property name with a "X-" prefix
 * 
 * Purpose: This class of property provides a framework for defining
 * non-standard properties.
 * 
 * Value Type: TEXT
 * 
 * Property Parameters: Non-standard and language property parameters
 * can be specified on this property.
 * 
 * Conformance: This property can be specified in any calendar
 * component.
 * 
 * Description: The MIME Calendaring and Scheduling Content Type
 * provides a "standard mechanism for doing non-standard things". This
 * extension support is provided for implementers to "push the envelope"
 * on the existing version of the memo. Extension properties are
 * specified by property and/or property parameter names that have the
 * prefix text of "X-" (the two character sequence: LATIN CAPITAL LETTER
 * X character followed by the HYPEN-MINUS character). It is recommended
 * that vendors concatenate onto this sentinel another short prefix text
 * to identify the vendor. This will facilitate readability of the
 * extensions and minimize possible collision of names between different
 * vendors. User agents that support this content type are expected to
 * be able to parse the extension properties and property parameters but
 * can ignore them.
 * 
 * At present, there is no registration authority for names of extension
 * properties and property parameters. The data type for this property
 * is TEXT. Optionally, the data type can be any of the other valid data
 * types.
 * 
 * Format Definition: The property is defined by the following notation:
 * 
 *   x-prop     = x-name *(";" xparam) [";" languageparam] ":" text CRLF
 *      ; Lines longer than 75 octets should be folded
 * 
 * Example: The following might be the ABC vendor's extension for an
 * audio-clip form of subject property:
 * 
 *   X-ABC-MMSUBJ;X-ABC-MMSUBJTYPE=wave:http://load.noise.org/mysubj.wav
 */
class qCal_Property_NonStandard extends qCal_Property {

	protected $type = 'TEXT';
	protected $allowedComponents = array('VEVENT','VTODO','VJOURNAL',
		'VALARM','VTIMEZONE','VFREEBUSY','VCALENDAR');
	protected $allowMultiple = true;
	public function __construct($value, $params, $name) {
	
		parent::__construct($value, $params);
		$this->name = strtoupper($name);
	
	}

}