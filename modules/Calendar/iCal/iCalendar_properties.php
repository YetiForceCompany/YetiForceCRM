<?php

// $Id: iCalendar_properties.php,v 1.13 2005/07/21 22:42:13 defacer Exp $

class iCalendar_property
{

	// Properties can have parameters, but cannot have other properties or components

	public $parent_component = NULL;
	public $value = NULL;
	public $parameters = NULL;
	public $valid_parameters = NULL;
	// These are common for 95% of properties, so define them here and override as necessary
	public $val_multi = false;
	public $val_default = NULL;

	public function __construct()
	{
		$this->construct();
	}

	public function construct()
	{
		$this->parameters = array();
	}

	// If some property needs extra care with its parameters, override this
	// IMPORTANT: the parameter name MUST BE CAPITALIZED!
	public function is_valid_parameter($parameter, $value)
	{

		if (is_array($value)) {
			if (!iCalendar_parameter::multiple_values_allowed($parameter)) {
				return false;
			}
			foreach ($value as $item) {
				if (!iCalendar_parameter::is_valid_value($this, $parameter, $item)) {
					return false;
				}
			}
			return true;
		}

		return iCalendar_parameter::is_valid_value($this, $parameter, $value);
	}

	public function invariant_holds()
	{
		return true;
	}

	// If some property is very picky about its values, it should do the work itself
	// Only data type validation is done here
	public function is_valid_value($value)
	{
		if (is_array($value)) {
			if (!$this->val_multi) {
				return false;
			} else {
				foreach ($value as $oneval) {
					if (!rfc2445_is_valid_value($oneval, $this->val_type)) {
						return false;
					}
				}
			}
			return true;
		}
		return rfc2445_is_valid_value($value, $this->val_type);
	}

	public function default_value()
	{
		return $this->val_default;
	}

	public function set_parent_component($componentname)
	{
		if (class_exists('iCalendar_' . strtolower(substr($componentname, 1)))) {
			$this->parent_component = strtoupper($componentname);
			return true;
		}

		return false;
	}

	public function set_value($value)
	{
		if ($this->is_valid_value($value)) {
			// This transparently formats any value type according to the iCalendar specs
			if (is_array($value)) {
				foreach ($value as $key => $item) {
					$value[$key] = rfc2445_do_value_formatting($item, $this->val_type);
				}
				$this->value = implode(',', $value);
			} else {
				$this->value = rfc2445_do_value_formatting($value, $this->val_type);
			}

			return true;
		}
		return false;
	}

	public function get_value()
	{
		// First of all, assume that we have multiple values
		$valarray = explode('\\,', $this->value);

		// Undo transparent formatting
		$replace_function = create_function('$a', 'return rfc2445_undo_value_formatting($a, ' . $this->val_type . ');');
		$valarray = array_map($replace_function, $valarray);

		// Now, if this property cannot have multiple values, don't return as an array
		if (!$this->val_multi) {
			return $valarray[0];
		}

		// Otherwise return an array even if it has one element, for uniformity
		return $valarray;
	}

	public function set_parameter($name, $value)
	{

		// Uppercase
		$name = strtoupper($name);

		// Are we trying to add a valid parameter?
		$xname = false;
		if (!isset($this->valid_parameters[$name])) {
			// If not, is it an x-name as per RFC 2445?
			if (!rfc2445_is_xname($name)) {
				return false;
			}
			// No more checks -- all components are supposed to allow x-name parameters
			$xname = true;
		}

		if (!$this->is_valid_parameter($name, $value)) {
			return false;
		}

		if (is_array($value)) {
			foreach ($value as $key => $element) {
				$value[$key] = iCalendar_parameter::do_value_formatting($name, $element);
			}
		} else {
			$value = iCalendar_parameter::do_value_formatting($name, $value);
		}

		$this->parameters[$name] = $value;

		// Special case: if we just changed the VALUE parameter, reflect this
		// in the object's status so that it only accepts correct type values
		if ($name == 'VALUE') {
			$this->val_type = constant('RFC2445_TYPE_' . str_replace('-', '_', $value));
		}

		return true;
	}

	public function get_parameter($name)
	{

		// Uppercase
		$name = strtoupper($name);

		if (isset($this->parameters[$name])) {
			// If there are any double quotes in the value, invisibly strip them
			if (is_array($this->parameters[$name])) {
				foreach ($this->parameters[$name] as $key => $value) {
					if (substr($value, 0, 1) == '"') {
						$this->parameters[$name][$key] = substr($value, 1, strlen($value) - 2);
					}
				}
				return $this->parameters[$name];
			} else {
				if (substr($this->parameters[$name], 0, 1) == '"') {
					return substr($this->parameters[$name], 1, strlen($this->parameters[$name]) - 2);
				}
			}
		}

		return NULL;
	}

	public function serialize()
	{
		$string = $this->name;

		if (!empty($this->parameters)) {
			foreach ($this->parameters as $name => $value) {
				$string .= ';' . $name . '=';
				if (is_array($value)) {
					$string .= implode(',', $value);
				} else {
					$string .= $value;
				}
			}
		}

		$string .= ':' . $this->value;

		return rfc2445_fold($string) . RFC2445_CRLF;
	}
}

// 4.7 Calendar Properties
// -----------------------

class iCalendar_property_calscale extends iCalendar_property
{

	public $name = 'CALSCALE';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		// This is case-sensitive
		return ($value === 'GREGORIAN');
	}
}

class iCalendar_property_method extends iCalendar_property
{

	public $name = 'METHOD';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		// This is case-sensitive
		// Methods from RFC 2446
		$methods = array('PUBLISH', 'REQUEST', 'REPLY', 'ADD', 'CANCEL', 'REFRESH', 'COUNTER', 'DECLINECOUNTER');
		return in_array($value, $methods);
	}
}

class iCalendar_property_prodid extends iCalendar_property
{

	public $name = 'PRODID';
	public $val_type = RFC2445_TYPE_TEXT;
	public $val_default = NULL;

	public function construct()
	{
		$this->val_default = '-//YetiForce CRM//YetiForce CRM ' . \App\Version::get() . '//EN';

		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_version extends iCalendar_property
{

	public $name = 'VERSION';
	public $val_type = RFC2445_TYPE_TEXT;
	public $val_default = '2.0';

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		return($value === '2.0' || $value === 2.0);
	}
}

// 4.8.1 Descriptive Component Properties
// --------------------------------------

class iCalendar_property_attach extends iCalendar_property
{

	public $name = 'ATTACH';
	public $val_type = RFC2445_TYPE_URI;

	public function construct()
	{
		$this->valid_parameters = array(
			'FMTTYPE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ENCODING' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function invariant_holds()
	{
		if (isset($this->parameters['ENCODING']) && !isset($this->parameters['VALUE'])) {
			return false;
		}
		if (isset($this->parameters['VALUE']) && !isset($this->parameters['ENCODING'])) {
			return false;
		}

		return true;
	}

	public function is_valid_parameter($parameter, $value)
	{

		$parameter = strtoupper($parameter);

		if (!parent::is_valid_parameter($parameter, $value)) {
			return false;
		}

		if ($parameter === 'ENCODING' && strtoupper($value) != 'BASE64') {
			return false;
		}

		if ($parameter === 'VALUE' && strtoupper($value) != 'BINARY') {
			return false;
		}

		return true;
	}
}

class iCalendar_property_categories extends iCalendar_property
{

	public $name = 'CATEGORIES';
	public $val_type = RFC2445_TYPE_TEXT;
	public $val_multi = true;

	public function construct()
	{
		$this->valid_parameters = array(
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_class extends iCalendar_property
{

	public $name = 'CLASS';
	public $val_type = RFC2445_TYPE_TEXT;
	public $val_default = 'PUBLIC';

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		$value = strtoupper($value);
		// If this is not an xname, it is case-sensitive
		return ($value === 'PUBLIC' || $value === 'PRIVATE' || $value === 'CONFIDENTIAL' || rfc2445_is_xname(strtoupper($value)));
	}
}

class iCalendar_property_comment extends iCalendar_property
{

	public $name = 'COMMENT';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_description extends iCalendar_property
{

	public $name = 'DESCRIPTION';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_geo extends iCalendar_property
{

	public $name = 'GEO';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		// This MUST be two floats separated by a semicolon
		if (!is_string($value)) {
			return false;
		}

		$floats = explode(';', $value);
		if (count($floats) != 2) {
			return false;
		}

		return rfc2445_is_valid_value($floats[0], RFC2445_TYPE_FLOAT) && rfc2445_is_valid_value($floats[1], RFC2445_TYPE_FLOAT);
	}

	public function set_value($value)
	{
		// Must override this, otherwise the semicolon separating
		// the two floats would get auto-quoted, which is illegal
		if ($this->is_valid_value($value)) {
			$this->value = $value;
			return true;
		}

		return false;
	}
}

class iCalendar_property_location extends iCalendar_property
{

	public $name = 'LOCATION';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_percent_complete extends iCalendar_property
{

	public $name = 'PERCENT-COMPLETE';
	public $val_type = RFC2445_TYPE_INTEGER;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		// Only integers between 0 and 100 inclusive allowed
		if (!parent::is_valid_value($value)) {
			return false;
		}
		$value = intval($value);
		return ($value >= 0 && $value <= 100);
	}
}

class iCalendar_property_priority extends iCalendar_property
{

	public $name = 'PRIORITY';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		// Only integers between 0 and 9 inclusive allowed        
		if (!parent::is_valid_value($value)) {
			return false;
		}
		return true;
	}
}

class iCalendar_property_resources extends iCalendar_property
{

	public $name = 'RESOURCES';
	public $val_type = RFC2445_TYPE_TEXT;
	public $val_multi = true;

	public function construct()
	{
		$this->valid_parameters = array(
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_status extends iCalendar_property
{

	public $name = 'STATUS';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
	/*    function is_valid_value($value) {
	  // This is case-sensitive
	  switch ($this->parent_component) {
	  case 'VEVENT':
	  $allowed = array('TENTATIVE', 'CONFIRMED', 'CANCELLED');
	  break;
	  case 'VTODO':
	  $allowed = array('NEEDS-ACTION', 'COMPLETED', 'IN-PROCESS', 'CANCELLED');
	  break;
	  case 'VJOURNAL':
	  $allowed = array('DRAFT', 'FINAL', 'CANCELLED');
	  break;
	  }
	  return in_array($value, $allowed);

	  }
	 */
}

class iCalendar_property_summary extends iCalendar_property
{

	public $name = 'SUMMARY';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

// 4.8.2 Date and Time Component Properties
// ----------------------------------------

class iCalendar_property_completed extends iCalendar_property
{

	public $name = 'COMPLETED';
	public $val_type = RFC2445_TYPE_DATE_TIME;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}
		// Time MUST be in UTC format
		return(substr($value, -1) == 'Z');
	}
}

class iCalendar_property_dtend extends iCalendar_property
{

	public $name = 'DTEND';
	public $val_type = RFC2445_TYPE_DATE_TIME;

	public function construct()
	{
		$this->valid_parameters = array(
			'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'TZID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}

		// If present in a FREEBUSY component, must be in UTC format
		if ($this->parent_component == 'VFREEBUSY' && substr($value, -1) != 'Z') {
			return false;
		}

		return true;
	}

	public function is_valid_parameter($parameter, $value)
	{

		$parameter = strtoupper($parameter);

		if (!parent::is_valid_parameter($parameter, $value)) {
			return false;
		}
		if ($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
			return false;
		}

		return true;
	}
}

class iCalendar_property_due extends iCalendar_property
{

	public $name = 'DUE';
	public $val_type = RFC2445_TYPE_DATE_TIME;

	public function construct()
	{
		$this->valid_parameters = array(
			'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'TZID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}

		// If present in a FREEBUSY component, must be in UTC format
		if ($this->parent_component == 'VFREEBUSY' && substr($value, -1) != 'Z') {
			return false;
		}

		return true;
	}

	public function is_valid_parameter($parameter, $value)
	{

		$parameter = strtoupper($parameter);

		if (!parent::is_valid_parameter($parameter, $value)) {
			return false;
		}
		if ($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
			return false;
		}

		return true;
	}
}

class iCalendar_property_dtstart extends iCalendar_property
{

	public $name = 'DTSTART';
	public $val_type = RFC2445_TYPE_DATE_TIME;

	public function construct()
	{
		$this->valid_parameters = array(
			'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'TZID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}
		// If present in a FREEBUSY component, must be in UTC format
		if ($this->parent_component == 'VFREEBUSY' && substr($value, -1) != 'Z') {
			return false;
		}

		return true;
	}

	public function is_valid_parameter($parameter, $value)
	{
		$parameter = strtoupper($parameter);

		if (!parent::is_valid_parameter($parameter, $value)) {
			return false;
		}
		if ($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
			return false;
		}

		return true;
	}
}

class iCalendar_property_duration extends iCalendar_property
{

	public $name = 'DURATION';
	public $val_type = RFC2445_TYPE_DURATION;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}

		// Value must be positive
		return ($value{0} != '-');
	}
}

class iCalendar_property_freebusy extends iCalendar_property
{

	public $name = 'FREEBUSY';
	public $val_type = RFC2445_TYPE_PERIOD;
	public $val_multi = true;

	public function construct()
	{
		$this->valid_parameters = array(
			'FBTYPE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}

		$pos = strpos($value, '/'); // We know there's only one / in there
		if ($value{$pos - 1} != 'Z') {
			// Start time MUST be in UTC
			return false;
		}
		if ($value{$pos + 1} != 'P' && $substr($value, -1) != 'Z') {
			// If the second part is not a period, it MUST be in UTC
			return false;
		}

		return true;
	}
}

class iCalendar_property_transp extends iCalendar_property
{

	public $name = 'TRANSP';
	public $val_type = RFC2445_TYPE_TEXT;
	public $val_default = 'OPAQUE';

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		return ($value === 'TRANSPARENT' || $value === 'OPAQUE');
	}
}

class iCalendar_property_attendee extends iCalendar_property
{

	public $name = 'ATTENDEE';
	public $val_type = RFC2445_TYPE_CAL_ADDRESS;

	public function construct()
	{
		$this->valid_parameters = array(
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'CN' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ROLE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'PARTSTAT' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'RSVP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'CUTYPE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'MEMBER' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DELEGATED-TO' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DELEGATED-FROM' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SENT-BY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DIR' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function set_parent_component($componentname)
	{
		if (!parent::set_parent_component($componentname)) {
			return false;
		}

		if ($this->parent_component == 'VFREEBUSY' || $this->parent_component == 'VALARM') {
			// Most parameters become invalid in this case, the full allowed set is now:
			$this->valid_parameters = array(
				'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
				RFC2445_XNAME => RFC2445_OPTIONAL
			);
		}

		return false;
	}
}

class iCalendar_property_contact extends iCalendar_property
{

	public $name = 'CONTACT';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			'ALTREP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_organizer extends iCalendar_property
{

	public $name = 'ORGANIZER';
	public $val_type = RFC2445_TYPE_CAL_ADDRESS;

	public function construct()
	{
		$this->valid_parameters = array(
			'CN' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DIR' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SENT-BY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_recurrence_id extends iCalendar_property
{

	public $name = 'RECURRENCE-ID';
	public $val_type = RFC2445_TYPE_DATE_TIME;

	public function construct()
	{
		$this->valid_parameters = array(
			'RANGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'TZID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_parameter($parameter, $value)
	{

		$parameter = strtoupper($parameter);

		if (!parent::is_valid_parameter($parameter, $value)) {
			return false;
		}
		if ($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
			return false;
		}

		return true;
	}
}

class iCalendar_property_related_to extends iCalendar_property
{

	public $name = 'RELATED-TO';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			'RELTYPE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_url extends iCalendar_property
{

	public $name = 'URL';
	public $val_type = RFC2445_TYPE_URI;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_uid extends iCalendar_property
{

	public $name = 'UID';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);

		// The exception to the rule: this is not a static value, so we
		// generate it on-the-fly here. Guaranteed to be different for
		// each instance of this property, too. Nice.
		$this->val_default = rfc2445_guid();
	}
}

// 4.8.5 Recurrence Component Properties
// -------------------------------------

class iCalendar_property_exdate extends iCalendar_property
{

	public $name = 'EXDATE';
	public $val_type = RFC2445_TYPE_DATE_TIME;
	public $val_multi = true;

	public function construct()
	{
		$this->valid_parameters = array(
			'TZID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_parameter($parameter, $value)
	{

		$parameter = strtoupper($parameter);

		if (!parent::is_valid_parameter($parameter, $value)) {
			return false;
		}
		if ($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
			return false;
		}

		return true;
	}
}

class iCalendar_property_exrule extends iCalendar_property
{

	public $name = 'EXRULE';
	public $val_type = RFC2445_TYPE_RECUR;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_rdate extends iCalendar_property
{

	public $name = 'RDATE';
	public $val_type = RFC2445_TYPE_DATE_TIME;
	public $val_multi = true;

	public function construct()
	{
		$this->valid_parameters = array(
			'TZID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_parameter($parameter, $value)
	{

		$parameter = strtoupper($parameter);

		if (!parent::is_valid_parameter($parameter, $value)) {
			return false;
		}
		if ($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME' || $value == 'PERIOD')) {
			return false;
		}

		return true;
	}
}

class iCalendar_property_rrule extends iCalendar_property
{

	public $name = 'RRULE';
	public $val_type = RFC2445_TYPE_RECUR;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_created extends iCalendar_property
{

	public $name = 'CREATED';
	public $val_type = RFC2445_TYPE_DATE_TIME;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}
		// Time MUST be in UTC format
		return(substr($value, -1) == 'Z');
	}
}

class iCalendar_property_dtstamp extends iCalendar_property
{

	public $name = 'DTSTAMP';
	public $val_type = RFC2445_TYPE_DATE_TIME;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}
		// Time MUST be in UTC format
		return(substr($value, -1) == 'Z');
	}
}

class iCalendar_property_last_modified extends iCalendar_property
{

	public $name = 'LAST-MODIFIED';
	public $val_type = RFC2445_TYPE_DATE_TIME;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}
		// Time MUST be in UTC format
		return(substr($value, -1) == 'Z');
	}
}

class iCalendar_property_sequence extends iCalendar_property
{

	public $name = 'SEQUENCE';
	public $val_type = RFC2445_TYPE_INTEGER;
	public $val_default = 0;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!parent::is_valid_value($value)) {
			return false;
		}
		$value = intval($value);
		return ($value >= 0);
	}
}

// 4.8.8 Miscellaneous Component Properties
// ----------------------------------------

class iCalendar_property_x extends iCalendar_property
{

	public $name = RFC2445_XNAME;
	public $val_type = NULL;

	public function construct()
	{
		$this->valid_parameters = array(
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function set_name($name)
	{

		$name = strtoupper($name);

		if (rfc2445_is_xname($name)) {
			$this->name = $name;
			return true;
		}

		return false;
	}
}

class iCalendar_property_request_status extends iCalendar_property
{

	// IMPORTANT NOTE: This property value includes TEXT fields
	// separated by semicolons. Unfortunately, auto-value-formatting
	// cannot be used in this case. As an exception, the value passed
	// to this property MUST be already escaped.

	public $name = 'REQUEST-STATUS';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}

	public function is_valid_value($value)
	{
		if (!is_string($value) || empty($value)) {
			return false;
		}

		$len = strlen($value);
		$parts = array();
		$from = 0;
		$escch = false;

		for ($i = 0; $i < $len; ++$i) {
			if ($value{$i} == ';' && !$escch) {
				// Token completed
				$parts[] = substr($value, $from, $i - $from);
				$from = $i + 1;
				continue;
			}
			$escch = ($value{$i} == '\\');
		}
		// Add one last token with the remaining text; if the value
		// ended with a ';' it was illegal, so check that this token
		// is not the empty string.
		$parts[] = substr($value, $from);

		$count = count($parts);

		// May have 2 or 3 tokens (last one is optional)
		if ($count != 2 && $count != 3) {
			return false;
		}

		// REMEMBER: if ANY part is empty, we have an illegal value
		// First token must be hierarchical numeric status (3 levels max)
		if (strlen($parts[0]) == 0) {
			return false;
		}

		if ($parts[0]{0} < '1' || $parts[0]{0} > '4') {
			return false;
		}

		$len = strlen($parts[0]);

		// Max 3 levels, and can't end with a period
		if ($len > 5 || $parts[0]{$len - 1} == '.') {
			return false;
		}

		for ($i = 1; $i < $len; ++$i) {
			if (($i & 1) == 1 && $parts[0]{$i} != '.') {
				// Even-indexed chars must be periods
				return false;
			} else if (($i & 1) == 0 && ($parts[0]{$i} < '0' || $parts[0]{$i} > '9')) {
				// Odd-indexed chars must be numbers
				return false;
			}
		}

		// Second and third tokens must be TEXT, and already escaped, so
		// they are not allowed to have UNESCAPED semicolons, commas, slashes,
		// or any newlines at all

		for ($i = 1; $i < $count; ++$i) {
			if (strpos($parts[$i], "\n") !== false) {
				return false;
			}

			$len = strlen($parts[$i]);
			if ($len == 0) {
				// Cannot be empty
				return false;
			}

			$parts[$i] .= '#'; // This guard token saves some conditionals in the loop

			for ($j = 0; $j < $len; ++$j) {
				$thischar = $parts[$i]{$j};
				$nextchar = $parts[$i]{$j + 1};
				if ($thischar == '\\') {
					// Next char must now be one of ";,\nN"
					if ($nextchar != ';' && $nextchar != ',' && $nextchar != '\\' &&
						$nextchar != 'n' && $nextchar != 'N') {
						return false;
					}

					// OK, this escaped sequence is correct, bypass next char
					++$j;
					continue;
				}
				if ($thischar == ';' || $thischar == ',' || $thischar == '\\') {
					// This wasn't escaped as it should
					return false;
				}
			}
		}

		return true;
	}

	public function set_value($value)
	{
		// Must override this, otherwise the value would be quoted again
		if ($this->is_valid_value($value)) {
			$this->value = $value;
			return true;
		}

		return false;
	}
}

class iCalendar_property_trigger extends iCalendar_property
{

	public $name = 'TRIGGER';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_action extends iCalendar_property
{

	public $name = 'ACTION';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			'DISPLAY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_x_wr_alarmuid extends iCalendar_property
{

	public $name = 'X_WR_ALARMUID';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_tzoffsetto extends iCalendar_property
{

	public $name = 'TZOFFSETTO';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_daylightc extends iCalendar_property
{

	public $name = 'DAYLIGHTC';
	public $val_type = RFC2445_TYPE_INTEGER;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_standardc extends iCalendar_property
{

	public $name = 'STANDARDC';
	public $val_type = RFC2445_TYPE_INTEGER;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_OPTIONAL
		);
	}
}

class iCalendar_property_tzid extends iCalendar_property
{

	public $name = 'TZID';
	public $val_type = RFC2445_TYPE_TEXT;

	public function construct()
	{
		$this->valid_parameters = array(
			RFC2445_XNAME => RFC2445_REQUIRED
		);
	}
}

?>
