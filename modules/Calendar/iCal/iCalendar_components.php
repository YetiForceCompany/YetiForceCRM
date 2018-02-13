<?php
// $Id: iCalendar_components.php,v 1.8 2005/07/21 22:31:44 defacer Exp $
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/Icalendar.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarAlarm.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarEvent.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarFreebusy.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarJournal.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarTimezone.php');
Vtiger_Loader::includeOnce('~modules/Calendar/iCal/IcalendarTodo.php');

class IcalendarComponent
{
    public $name = null;
    public $properties = null;
    public $components = null;
    public $valid_properties = null;
    public $valid_components = null;

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

    public function addProperty($name, $value = null, $parameters = null)
    {

        // Uppercase first of all
        $name = strtoupper($name);
        // Are we trying to add a valid property?
        $xname = false;
        if (!isset($this->valid_properties[$name])) {
            // If not, is it an x-name as per RFC 2445?
            if (!\ICalendarRfc::rfc2445IsXname($name)) {
                return false;
            }
            // Since this is an xname, all components are supposed to allow this property
            $xname = true;
        }

        // Create a property object of the correct class
        if ($xname) {
            $property = new IcalendarPropertyX();
            $property->setName($name);
        } else {
            $classname = 'IcalendarProperty'.ucfirst(strtolower(str_replace('-', '', $name)));
            $property = new $classname();
        }
        // If $value is NULL, then this property must define a default value.
        if ($value === null) {
            $value = $property->defaultValueICal();
            if ($value === null) {
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
            $this->properties[$name] = [$property];
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
                $classname = 'IcalendarProperty'.ucfirst(strtolower(str_replace('-', '', $property)));
                $object = new $classname();
                if ($object->defaultValueICal() === null) {
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
        $string = \ICalendarRfc::rfc2445Fold('BEGIN:'.$this->name).RFC2445_CRLF;
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
        $string .= \ICalendarRfc::rfc2445Fold('END:'.$this->name).RFC2445_CRLF;

        return $string;
    }

    /**
     * Assign values.
     *
     * @param array $activity
     *
     * @return bool
     */
    public function assignValues($activity)
    {
        foreach ($this->mapping_arr as $key => $components) {
            if (!is_array($components['component']) && empty($components['function'])) {
                $this->addProperty($key, $activity[$components['component']]);
            } elseif (is_array($components['component']) && empty($components['function'])) {
                $component = '';
                foreach ($components['component'] as $comp) {
                    if (!empty($component)) {
                        $component .= ',';
                    }
                    $component .= $activity[$comp];
                }
                $this->addProperty($key, $component);
            } elseif (!empty($components['function'])) {
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
                        if (\App\Field::getFieldPermission($modtype, $this->field_mapping_arr[$component])) {
                            $activity[$this->field_mapping_arr[$component]] = $ical_activity[$key];
                        } else {
                            $activity[$this->field_mapping_arr[$component]] = '';
                        }
                    } else {
                        if (\App\Field::getFieldPermission($modtype, $component)) {
                            $activity[$component] = $ical_activity[$key];
                        } else {
                            $activity[$component] = '';
                        }
                    }
                }
            } else {
                $temp = $ical_activity[$key];
                $count = 0;
                if ($type == 'string') {
                    $values = explode('\\,', $temp);
                } elseif ($type == 'datetime' && !empty($temp)) {
                    $values = $this->strtodatetime($temp);
                }
                foreach ($component as $index) {
                    if (!isset($activity[$index])) {
                        if (isset($this->field_mapping_arr[$index])) {
                            if (\App\Field::getFieldPermission($modtype, $this->field_mapping_arr[$index])) {
                                $activity[$this->field_mapping_arr[$index]] = $values[$count];
                            } else {
                                $activity[$this->field_mapping_arr[$index]] = '';
                            }
                        } else {
                            if (\App\Field::getFieldPermission($modtype, $index)) {
                                $activity[$index] = $values[$count];
                            } else {
                                $activity[$index] = '';
                            }
                        }
                    }
                    ++$count;
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
                $temp = str_replace('P', '', $ical_activity['VALARM']['TRIGGER']);
                //if there is negative value then ignore it because in vtiger even though its negative or postiview we
                //make reminder to be before the event
                $temp = str_replace('-', '', $temp);
                $durationTypeCharacters = ['W', 'D', 'T', 'H', 'M', 'S'];
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
                        case 'W':
                            $reminder_time += 24 * 24 * 60 * $durationValue;
                            break;
                        case 'D':
                            $reminder_time += 24 * 60 * $durationValue;
                            break;
                        case 'T':
                            //Skip this symbol since its just indicates the start of time component
                            break;
                        case 'H':
                            $reminder_time += $duration * 60;
                            break;
                        case 'M':
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
            $priorityMap = ['1' => 'Low', '5' => 'Medium', '9' => 'High'];
            $priorityval = $activity['taskpriority'];
            if (array_key_exists($priorityval, $priorityMap)) {
                $activity['taskpriority'] = $priorityMap[$priorityval];
            }
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
        $datetime[] = $year.'-'.$month.'-'.$day;
        if (empty($hours)) {
            $hours = '00';
        }
        if (empty($minutes)) {
            $minutes = '00';
        }
        if (empty($seconds)) {
            $seconds = '00';
        }
        $datetime[] = $hours.':'.$minutes.':'.$seconds;

        return $datetime;
    }
}
