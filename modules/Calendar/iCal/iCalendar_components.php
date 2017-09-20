<?php
// $Id: iCalendar_components.php,v 1.8 2005/07/21 22:31:44 defacer Exp $
require_once('include/utils/utils.php');

class IcalendarComponent
{

	public $name = NULL;
	public $properties = NULL;
	public $components = NULL;
	public $valid_properties = NULL;
	public $valid_components = NULL;

	public function __construct()
	{
		$this->construct();
	}

	public function construct()
	{
		// Initialize the components array
		if (empty($this->components)) {
			$this->components = [];
			foreach ($this->valid_components as $name) {
				$this->components[$name] = [];
			}
		}
	}

	public function getNameICal()
	{
		return $this->name;
	}

	public function addProperty($name, $value = NULL, $parameters = NULL)
	{

		// Uppercase first of all
		$name = strtoupper($name);
		// Are we trying to add a valid property?
		$xname = false;
		if (!isset($this->valid_properties[$name])) {
			// If not, is it an x-name as per RFC 2445?
			if (!rfc2445_is_xname($name)) {
				return false;
			}
			// Since this is an xname, all components are supposed to allow this property
			$xname = true;
		}

		// Create a property object of the correct class
		if ($xname) {
			$property = new IcalendarPropertyX;
			$property->setName($name);
		} else {
			$classname = 'IcalendarProperty' . ucfirst(strtolower(str_replace('-', '', $name)));
			$property = new $classname;
		}
		// If $value is NULL, then this property must define a default value.
		if ($value === NULL) {
			$value = $property->defaultValueICal();
			if ($value === NULL) {
				return false;
			}
		}

		// Set this property's parent component to ourselves, because some
		// properties behave differently according to what component they apply to.
		$property->setParentComponent($this->name);

		// Set parameters before value; this helps with some properties which
		// accept a VALUE parameter, and thus change their default value type.
		// The parameters must be valid according to property specifications
		if (!empty($parameters)) {
			foreach ($parameters as $paramname => $paramvalue) {
				if (!$property->setParameterICal($paramname, $paramvalue)) {
					return false;
				}
			}

			// Some parameters interact among themselves (e.g. ENCODING and VALUE)
			// so make sure that after the dust settles, these invariants hold true
			if (!$property->invariantHolds()) {
				return false;
			}
		}

		// $value MUST be valid according to the property data type
		if (!$property->setValueICal($value)) {
			return false;
		}

		// If this property is restricted to only once, blindly overwrite value
		if (!$xname && $this->valid_properties[$name] & RFC2445_ONCE) {
			$this->properties[$name] = array($property);
		}

		// Otherwise add it to the instance array for this property
		else {
			$this->properties[$name][] = $property;
		}

		// Finally: after all these, does the component invariant hold?
		if (!$this->invariantHolds()) {
			// If not, completely undo the property addition
			array_pop($this->properties[$name]);
			if (empty($this->properties[$name])) {
				unset($this->properties[$name]);
			}
			return false;
		}

		return true;
	}

	public function addComponent($component)
	{

		// With the detailed interface, you can add only components with this function
		if (!is_object($component) || !is_subclass_of($component, 'IcalendarComponent')) {
			return false;
		}

		$name = $component->getNameICal();

		// Only valid components as specified by this component are allowed
		if (!in_array($name, $this->valid_components)) {
			return false;
		}

		// Add it
		$this->components[$name][] = $component;

		return true;
	}

	public function getPropertyList($name)
	{

	}

	public function invariantHolds()
	{
		return true;
	}

	public function isValidICal()
	{
		// If we have any child components, check that they are all valid
		if (!empty($this->components)) {
			foreach ($this->components as $component => $instances) {
				foreach ($instances as $number => $instance) {
					if (!$instance->isValidICal()) {
						return false;
					}
				}
			}
		}
		// Finally, check the valid property list for any mandatory properties
		// that have not been set and do not have a default value
		foreach ($this->valid_properties as $property => $propdata) {
			if (($propdata & RFC2445_REQUIRED) && empty($this->properties[$property])) {
				$classname = 'IcalendarProperty' . ucfirst(strtolower(str_replace('-', '', $property)));
				$object = new $classname;
				if ($object->defaultValueICal() === NULL) {
					return false;
				}
				unset($object);
			}
		}

		return true;
	}

	public function serialize()
	{
		// Check for validity of the object
		if (!$this->isValidICal()) {
			return false;
		}

		// Maybe the object is valid, but there are some required properties that
		// have not been given explicit values. In that case, set them to defaults.
		foreach ($this->valid_properties as $property => $propdata) {
			if (($propdata & RFC2445_REQUIRED) && empty($this->properties[$property])) {
				$this->addProperty($property);
			}
		}

		// Start tag
		$string = rfc2445_fold('BEGIN:' . $this->name) . RFC2445_CRLF;
		// List of properties
		if (!empty($this->properties)) {
			foreach ($this->properties as $name => $properties) {
				foreach ($properties as $property) {
					$string .= $property->serialize();
				}
			}
		}
		// List of components
		if (!empty($this->components)) {
			foreach ($this->components as $name => $components) {
				foreach ($components as $component) {
					$string .= $component->serialize();
				}
			}
		}

		// End tag
		$string .= rfc2445_fold('END:' . $this->name) . RFC2445_CRLF;

		return $string;
	}

	/**
	 * Assign values
	 * @param array $activity
	 * @return boolean
	 */
	public function assignValues($activity)
	{
		foreach ($this->mapping_arr as $key => $components) {
			if (!is_array($components['component']) && empty($components['function'])) {
				$this->addProperty($key, $activity[$components['component']]);
			} else if (is_array($components['component']) && empty($components['function'])) {
				$component = '';
				foreach ($components['component'] as $comp) {
					if (!empty($component))
						$component .= ',';
					$component .= $activity[$comp];
				}
				$this->addProperty($key, $component);
			} else if (!empty($components['function'])) {
				$function = $components['function'];
				$this->$function($activity);
			}
		}
		return true;
	}

	public function generateArray($ical_activity)
	{
		$activity = [];
		$activitytype = $ical_activity['TYPE'];
		if ($activitytype == 'VEVENT') {
			$modtype = 'Events';
		} else {
			$modtype = 'Calendar';
		}
		foreach ($this->mapping_arr as $key => $comp) {
			$type = $comp['type'];
			$component = $comp['component'];
			if (!is_array($component)) {
				if ($type != 'user') {
					if (isset($this->field_mapping_arr[$component])) {
						if (\App\Field::getFieldPermission($modtype, $this->field_mapping_arr[$component]))
							$activity[$this->field_mapping_arr[$component]] = $ical_activity[$key];
						else
							$activity[$this->field_mapping_arr[$component]] = '';
					} else {
						if (\App\Field::getFieldPermission($modtype, $component))
							$activity[$component] = $ical_activity[$key];
						else
							$activity[$component] = '';
					}
				}
			} else {
				$temp = $ical_activity[$key];
				$count = 0;
				if ($type == 'string') {
					$values = explode('\\,', $temp);
				} else if ($type == 'datetime' && !empty($temp)) {
					$values = $this->strtodatetime($temp);
				}
				foreach ($component as $index) {
					if (!isset($activity[$index])) {
						if (isset($this->field_mapping_arr[$index])) {
							if (\App\Field::getFieldPermission($modtype, $this->field_mapping_arr[$index]))
								$activity[$this->field_mapping_arr[$index]] = $values[$count];
							else
								$activity[$this->field_mapping_arr[$index]] = '';
						} else {
							if (\App\Field::getFieldPermission($modtype, $index))
								$activity[$index] = $values[$count];
							else
								$activity[$index] = '';
						}
					}
					$count++;
				}
				unset($values);
			}
		}
		if ($activitytype == 'VEVENT') {
			$activity['activitytype'] = 'Meeting';
			if (empty($activity['eventstatus'])) {
				$activity['eventstatus'] = 'PLL_PLANNED';
			}
			if (!empty($ical_activity['VALARM'])) {
				$temp = str_replace("P", '', $ical_activity['VALARM']['TRIGGER']);
				//if there is negative value then ignore it because in vtiger even though its negative or postiview we
				//make reminder to be before the event
				$temp = str_replace("-", '', $temp);
				$durationTypeCharacters = array('W', 'D', 'T', 'H', 'M', 'S');
				$reminder_time = 0;
				foreach ($durationTypeCharacters as $durationType) {
					if (strpos($temp, $durationType) === false) {
						continue;
					}
					$parts = explode($durationType, $temp);
					$durationValue = $parts[0];
					$temp = $parts[1];
					$duration_type = $durationType;
					$duration = intval($durationValue);
					switch ($duration_type) {
						case 'W' :
							$reminder_time += 24 * 24 * 60 * $durationValue;
							break;
						case 'D' :
							$reminder_time += 24 * 60 * $durationValue;
							break;
						case 'T' :
							//Skip this symbol since its just indicates the start of time component
							break;
						case 'H' :
							$reminder_time += $duration * 60;
							break;
						case 'M' :
							$reminder_time = $duration;
							break;
					}
				}
				$activity['reminder_time'] = $reminder_time;
			}
		} else {
			$activity['activitytype'] = 'Task';
			if (empty($activity['activitystatus'])) {
				$activity['activitystatus'] = 'PLL_PLANNED';
			}
		}
		if ($activity['visibility'] == 'PUBLIC') {
			$activity['visibility'] = 'Public';
		}
		if ($activity['visibility'] == 'PRIVATE' || empty($activity['visibility'])) {
			$activity['visibility'] = 'Private';
		}
		if (array_key_exists('taskpriority', $activity)) {
			$priorityMap = array('1' => 'Low', '5' => 'Medium', '9' => 'High');
			$priorityval = $activity['taskpriority'];
			if (array_key_exists($priorityval, $priorityMap))
				$activity['taskpriority'] = $priorityMap[$priorityval];
		}
		if (!array_key_exists('visibility', $activity)) {
			$activity['visibility'] = ' ';
		}
		return $activity;
	}

	public function strtodatetime($date)
	{
		$date = preg_replace('/[A-Za-z_]*/', '', $date);
		$year = substr($date, 0, 4);
		$month = substr($date, 4, 2);
		$day = substr($date, 6, 2);
		$hours = substr($date, 8, 2);
		$minutes = substr($date, 10, 2);
		$seconds = substr($date, 12, 2);
		$datetime[] = $year . "-" . $month . "-" . $day;
		if (empty($hours))
			$hours = '00';
		if (empty($minutes))
			$minutes = '00';
		if (empty($seconds))
			$seconds = '00';
		$datetime[] = $hours . ":" . $minutes . ":" . $seconds;
		return $datetime;
	}
}

class Icalendar extends IcalendarComponent
{

	public $name = 'VCALENDAR';

	public function construct()
	{
		$this->valid_properties = array(
			'CALSCALE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'METHOD' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'PRODID' => RFC2445_REQUIRED | RFC2445_ONCE,
			'VERSION' => RFC2445_REQUIRED | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);

		$this->valid_components = array(
			'VEVENT', 'VTODO', 'VTIMEZONE'
		);
		parent::construct();
	}
}

class IcalendarEvent extends IcalendarComponent
{

	public $name = 'VEVENT';
	public $properties;
	public $mapping_arr = array(
		'CLASS' => array('component' => 'visibility', 'type' => 'string'),
		'DTSTART' => array('component' => array('date_start', 'time_start'), 'function' => 'iCalendarEventDtStart', 'type' => 'datetime'),
		'DTEND' => array('component' => array('due_date', 'time_end'), 'function' => 'iCalendarEventDtEnd', 'type' => 'datetime'),
		'DTSTAMP' => array('component' => array('date_start', 'time_start'), 'function' => 'iCalendarEventDtStamp', 'type' => 'datetime'),
		'LOCATION' => array('component' => 'location', 'type' => 'string'),
		'ORGANIZER' => array('component' => 'activityid', 'function' => 'icalendarEventOrganizer', 'type' => 'user'),
		'STATUS' => array('component' => 'activitystatus', 'type' => 'string'),
		'SUMMARY' => array('component' => 'subject', 'type' => 'string'),
		'PRIORITY' => array('component' => 'priority', 'type' => 'string'),
		'ATTENDEE' => array('component' => 'activityid', 'function' => 'iCalendarEventAttendee', 'type' => 'user'),
		'RESOURCES' => array('component' => array('location', 'activitystatus'), 'type' => 'string'),
		'DESCRIPTION' => array('component' => 'description', 'type' => 'string'),
	);
	public $field_mapping_arr = array(
		'priority' => 'taskpriority'
	);

	public function construct()
	{

		$this->valid_components = array('VALARM');

		$this->valid_properties = array(
			'CLASS' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'CREATED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DESCRIPTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			// Standard ambiguous here: in 4.6.1 it says that DTSTAMP in optional,
			// while in 4.8.7.2 it says it's REQUIRED. Go with REQUIRED.
			'DTSTAMP' => RFC2445_REQUIRED | RFC2445_ONCE,
			// Standard ambiguous here: in 4.6.1 it says that DTSTART in optional,
			// while in 4.8.2.4 it says it's REQUIRED. Go with REQUIRED.
			'DTSTART' => RFC2445_REQUIRED | RFC2445_ONCE,
			'GEO' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LAST-MODIFIED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LOCATION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ORGANIZER' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'PRIORITY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SEQUENCE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'STATUS' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SUMMARY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'TRANSP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			// Standard ambiguous here: in 4.6.1 it says that UID in optional,
			// while in 4.8.4.7 it says it's REQUIRED. Go with REQUIRED.
			'UID' => RFC2445_REQUIRED | RFC2445_ONCE,
			'URL' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'RECURRENCE-ID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DTEND' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DURATION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ATTACH' => RFC2445_OPTIONAL,
			'ATTENDEE' => RFC2445_OPTIONAL,
			'CATEGORIES' => RFC2445_OPTIONAL,
			'COMMENT' => RFC2445_OPTIONAL,
			'CONTACT' => RFC2445_OPTIONAL,
			'EXDATE' => RFC2445_OPTIONAL,
			'EXRULE' => RFC2445_OPTIONAL,
			'REQUEST-STATUS' => RFC2445_OPTIONAL,
			'RELATED-TO' => RFC2445_OPTIONAL,
			'RESOURCES' => RFC2445_OPTIONAL,
			'RDATE' => RFC2445_OPTIONAL,
			'RRULE' => RFC2445_OPTIONAL,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);

		parent::construct();
	}

	public function invariantHolds()
	{
		// DTEND and DURATION must not appear together
		if (isset($this->properties['DTEND']) && isset($this->properties['DURATION'])) {
			return false;
		}


		if (isset($this->properties['DTEND']) && isset($this->properties['DTSTART'])) {

			if ($this->properties['DTEND'][0]->value <= $this->properties['DTSTART'][0]->value) {
				return false;
			}

			// DTEND and DTSTART must have the same value type
			if ($this->properties['DTEND'][0]->val_type != $this->properties['DTSTART'][0]->val_type) {
				return false;
			}
		}
		return true;
	}

	public function iCalendarEventDtStamp($activity)
	{
		$components = gmdate('Ymd', strtotime($activity['date_start'] . ' ' . $activity['time_start'])) . 'T' . gmdate('His', strtotime($activity['date_start'] . " " . $activity['time_start'])) . 'Z';
		$this->addProperty('DTSTAMP', $components);
		return true;
	}

	public function iCalendarEventDtStart($activity)
	{
		$time = str_replace(':', '', $activity['time_start']);
		if (strlen($time) < 6) {
			while ((6 - strlen($time)) > 0) {
				$time .= '0';
			}
		}
		$components = str_replace('-', '', $activity['date_start']) . 'T' . $time . 'Z';
		$this->addProperty('DTSTART', $components);
		return true;
	}

	public function iCalendarEventDtEnd($activity)
	{
		$time = str_replace(':', '', $activity['time_end']);
		if (strlen($time) < 6) {
			while ((6 - strlen($time)) > 0) {
				$time .= '0';
			}
		}
		$components = str_replace('-', '', $activity['due_date']) . 'T' . $time . 'Z';
		$this->addProperty('DTEND', $components);
		return true;
	}

	/**
	 * iCalendar event attendee
	 * @param array $activity
	 * @return boolean
	 */
	public function iCalendarEventAttendee($activity)
	{
		$query = (new App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => $activity['id']]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (!empty($row['email'])) {
				$this->addProperty('ATTENDEE', 'mailto:' . $row['email'], ['CN' => vtlib\Functions::getCRMRecordLabel($row['crmid'])]);
			}
		}
		return true;
	}

	public function icalendarEventOrganizer($activity)
	{
		$email = App\Fields\Email::getUserMail($activity['assigned_user_id']);
		$this->addProperty('ORGANIZER', 'mailto:' . $email);
		return true;
	}
}

class IcalendarTodo extends IcalendarComponent
{

	public $name = 'VTODO';
	public $properties;
	public $mapping_arr = array(
		'DESCRIPTION' => array('component' => 'description', 'type' => 'string'),
		//'DTSTAMP'		=>	array('component'=>array('date_start','time_start'),'function'=>'iCalendarEventDtStamp','type'=>'datetime'),
		'DTSTART' => array('component' => array('date_start', 'time_start'), 'function' => 'iCalendarEventDtStart', 'type' => 'datetime'),
		'DUE' => array('component' => array('due_date'), 'function' => 'iCalendarEventDtEnd', 'type' => 'datetime'),
		'STATUS' => array('component' => 'status', 'type' => 'string'),
		'SUMMARY' => array('component' => 'subject', 'type' => 'string'),
		'PRIORITY' => array('component' => 'priority', 'type' => 'string'),
		'RESOURCES' => array('component' => array('status'), 'type' => 'string'),
	);
	public $field_mapping_arr = array(
		'status' => 'activitystatus',
		'priority' => 'taskpriority'
	);

	public function construct()
	{

		$this->valid_components = [];
		$this->valid_properties = array(
			'CLASS' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'COMPLETED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'CREATED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DESCRIPTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DTSTAMP' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DTSTART' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'GEO' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LAST-MODIFIED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'LOCATION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ORGANIZER' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'PERCENT' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'PRIORITY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'RECURID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SEQUENCE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'STATUS' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'SUMMARY' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'UID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'URL' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DURATION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ATTACH' => RFC2445_OPTIONAL,
			'ATTENDEE' => RFC2445_OPTIONAL,
			'CATEGORIES' => RFC2445_OPTIONAL,
			'COMMENT' => RFC2445_OPTIONAL,
			'CONTACT' => RFC2445_OPTIONAL,
			'EXDATE' => RFC2445_OPTIONAL,
			'EXRULE' => RFC2445_OPTIONAL,
			'RSTATUS' => RFC2445_OPTIONAL,
			'RELATED' => RFC2445_OPTIONAL,
			'RESOURCES' => RFC2445_OPTIONAL,
			'RDATE' => RFC2445_OPTIONAL,
			'RRULE' => RFC2445_OPTIONAL,
			'XPROP' => RFC2445_OPTIONAL
		);

		parent::construct();
	}

	public function iCalendarEventDtStamp($activity)
	{
		$components = gmdate('Ymd', strtotime($activity['date_start'] . ' ' . $activity['time_start'])) . 'T' . gmdate('His', strtotime($activity['date_start'] . ' ' . $activity['time_start'])) . 'Z';
		$this->addProperty('DTSTAMP', $components);
		return true;
	}

	public function iCalendarEventDtStart($activity)
	{
		$time = str_replace(':', '', $activity['time_start']);
		if (strlen($time) < 6) {
			while ((6 - strlen($time)) > 0) {
				$time .= '0';
			}
		}
		$components = str_replace('-', '', $activity['date_start']) . 'T' . $time . 'Z';
		$this->addProperty('DTSTART', $components);
		return true;
	}

	public function iCalendarEventDtEnd($activity)
	{
		$components = str_replace('-', '', $activity['due_date']) . 'T000000Z';
		$this->addProperty('DUE', $components);
		return true;
	}
}

class IcalendarJournal extends IcalendarComponent
{

}

class IcalendarFreebusy extends IcalendarComponent
{

}

class IcalendarAlarm extends IcalendarComponent
{

	public $name = 'VALARM';
	public $properties;
	public $mapping_arr = array(
		'TRIGGER' => array('component' => 'reminder_time', 'function' => 'iCalendarEventTrigger'),
	);

	public function construct()
	{

		$this->valid_components = [];
		$this->valid_properties = array(
			'TRIGGER' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'DESCRIPTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'ACTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'X-WR-ALARMUID' => RFC2445_OPTIONAL | RFC2445_ONCE,
			RFC2445_XNAME => RFC2445_OPTIONAL
		);

		parent::construct();
	}

	public function iCalendarEventTrigger($activity)
	{
		$reminder_time = $activity['reminder_time'];
		if ($reminder_time > 60) {
			$reminder_time = round($reminder_time / 60);
			$reminder = $reminder_time . 'H';
		} else {
			$reminder = $reminder_time . 'M';
		}
		$this->addProperty('ACTION', 'DISPLAY');
		$this->addProperty('TRIGGER', 'PT' . $reminder);
		$this->addProperty('DESCRIPTION', 'Reminder');
		return true;
	}
}

class IcalendarTimezone extends IcalendarComponent
{

	public $name = 'VTIMEZONE';
	public $properties;

	public function construct()
	{
		$this->valid_components = [];
		$this->valid_properties = array(
			'TZID' => RFC2445_REQUIRED | RFC2445_ONCE,
			'LAST-MODIFIED' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'TZURL' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'STANDARDC' => RFC2445_OPTIONAL,
			'DAYLIGHTC' => RFC2445_OPTIONAL,
			'TZOFFSETFROM' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'TZOFFSETTO' => RFC2445_OPTIONAL | RFC2445_ONCE,
			'X-PROP' => RFC2445_OPTIONAL
		);

		parent::construct();
	}
}

// REMINDER: DTEND must be later than DTSTART for all components which support both
// REMINDER: DUE must be later than DTSTART for all components which support both
