<?php // $Id: iCalendar_components.php,v 1.8 2005/07/21 22:31:44 defacer Exp $
require_once('include/utils/utils.php');

class iCalendar_component {
    var $name             = NULL;
    var $properties       = NULL;
    var $components       = NULL;
    var $valid_properties = NULL;
    var $valid_components = NULL;

    function iCalendar_component() {
        $this->construct();
    }

    function construct() {
        // Initialize the components array
        if(empty($this->components)) {
            $this->components = array();
            foreach($this->valid_components as $name) {
                $this->components[$name] = array();
            }
        }
    }

    function get_name() {
        return $this->name;
    }

    function add_property($name, $value = NULL, $parameters = NULL) {

        // Uppercase first of all
        $name = strtoupper($name);
        // Are we trying to add a valid property?
        $xname = false;
        if(!isset($this->valid_properties[$name])) {
            // If not, is it an x-name as per RFC 2445?
            if(!rfc2445_is_xname($name)) {
                return false;
            }
            // Since this is an xname, all components are supposed to allow this property
            $xname = true;
        }

        // Create a property object of the correct class
        if($xname) {
            $property = new iCalendar_property_x;
            $property->set_name($name);
        }
        else {
            $classname = 'iCalendar_property_'.strtolower(str_replace('-', '_', $name));
            $property = new $classname;
        }
        // If $value is NULL, then this property must define a default value.
        if($value === NULL) {
            $value = $property->default_value();
            if($value === NULL) {
                return false;
            }
        }

        // Set this property's parent component to ourselves, because some
        // properties behave differently according to what component they apply to.
        $property->set_parent_component($this->name);

        // Set parameters before value; this helps with some properties which
        // accept a VALUE parameter, and thus change their default value type.

        // The parameters must be valid according to property specifications
        if(!empty($parameters)) {
            foreach($parameters as $paramname => $paramvalue) {
                if(!$property->set_parameter($paramname, $paramvalue)) {
                    return false;
                }
            }

            // Some parameters interact among themselves (e.g. ENCODING and VALUE)
            // so make sure that after the dust settles, these invariants hold true
            if(!$property->invariant_holds()) {
                return false;
            }
        }

        // $value MUST be valid according to the property data type
        if(!$property->set_value($value)) {
            return false;
        }

        // If this property is restricted to only once, blindly overwrite value
        if(!$xname && $this->valid_properties[$name] & RFC2445_ONCE) {
            $this->properties[$name] = array($property);
        }

        // Otherwise add it to the instance array for this property
        else {
            $this->properties[$name][] = $property;
        }

        // Finally: after all these, does the component invariant hold?
        if(!$this->invariant_holds()) {
            // If not, completely undo the property addition
            array_pop($this->properties[$name]);
            if(empty($this->properties[$name])) {
                unset($this->properties[$name]);
            }
            return false;
        }

        return true;

    }

    function add_component($component) {

        // With the detailed interface, you can add only components with this function
        if(!is_object($component) || !is_subclass_of($component, 'iCalendar_component')) {
            return false;
        }

        $name = $component->get_name();

        // Only valid components as specified by this component are allowed
        if(!in_array($name, $this->valid_components)) {
            return false;
        }

        // Add it
        $this->components[$name][] = $component;

        return true;
    }

    function get_property_list($name) {
    }

    function invariant_holds() {
        return true;
    }

    function is_valid() {
        // If we have any child components, check that they are all valid
        if(!empty($this->components)) {
            foreach($this->components as $component => $instances) {
                foreach($instances as $number => $instance) {
                    if(!$instance->is_valid()) {
                        return false;
                    }
                }
            }
        }
        // Finally, check the valid property list for any mandatory properties
        // that have not been set and do not have a default value
        foreach($this->valid_properties as $property => $propdata) {
            if(($propdata & RFC2445_REQUIRED) && empty($this->properties[$property])) {
                $classname = 'iCalendar_property_'.strtolower(str_replace('-', '_', $property));
                $object    = new $classname;
                if($object->default_value() === NULL) {
                    return false;
                }
                unset($object);
            }
        }

        return true;
    }

    function serialize() {
        // Check for validity of the object
        if(!$this->is_valid()) {
            return false;
        }

        // Maybe the object is valid, but there are some required properties that
        // have not been given explicit values. In that case, set them to defaults.
        foreach($this->valid_properties as $property => $propdata) {
            if(($propdata & RFC2445_REQUIRED) && empty($this->properties[$property])) {
                $this->add_property($property);
            }
        }

        // Start tag
        $string = rfc2445_fold('BEGIN:'.$this->name) . RFC2445_CRLF;
        // List of properties
        if(!empty($this->properties)) {
            foreach($this->properties as $name => $properties) {
                foreach($properties as $property) {
                    $string .= $property->serialize();
                }
            }
        }
        // List of components
        if(!empty($this->components)) {
            foreach($this->components as $name => $components) {
                foreach($components as $component) {
                    $string .= $component->serialize();
                }
            }
        }

        // End tag
        $string .= rfc2445_fold('END:'.$this->name) . RFC2445_CRLF;

        return $string;
    }

    function assign_values($activity) {
    	foreach($this->mapping_arr as $key=>$components){
    		if(!is_array($components['component']) && empty($components['function'])){
    			$this->add_property($key,$activity[$components['component']]);
    		} else if(is_array($components['component']) && empty($components['function'])){
    			$component = '';
    			foreach($components['component'] as $comp){
    				if(!empty($component)) $component .= ',';
    				$component .= $activity[$comp];
    			}
    			$this->add_property($key,$component);
    		} else if(!empty($components['function'])){
    			$this->$components['function']($activity);
    		}
    	}
        return true;
    }

	function generateArray($ical_activity){
		global $current_user;
		$activity = array();
		$activitytype = $ical_activity['TYPE'];
		if($activitytype=='VEVENT'){
			$modtype = 'Events';
		} else {
			$modtype = 'Calendar';
		}
		foreach($this->mapping_arr as $key=>$comp){
			$type = $comp['type'];
			$component = $comp['component'];
			if(!is_array($component)){
				if($type!='user'){
					if(isset($this->field_mapping_arr[$component])){
						if(getFieldVisibilityPermission($modtype,$current_user->id,$this->field_mapping_arr[$component])=='0')
							$activity[$this->field_mapping_arr[$component]] = $ical_activity[$key];
						else
							$activity[$this->field_mapping_arr[$component]] = '';
					} else {
						if(getFieldVisibilityPermission($modtype,$current_user->id,$component)=='0')
							$activity[$component] = $ical_activity[$key];
						else
							$activity[$component] = '';
					}
				}
			} else {
				$temp = $ical_activity[$key];
				$count = 0;
				if($type == 'string'){
					$values = explode('\\,',$temp);
				} else if($type == 'datetime' && !empty($temp)){
					$values = $this->strtodatetime($temp);
				}
				foreach($component as $index){
					if(!isset($activity[$index])){
						if(isset($this->field_mapping_arr[$index])){
							if(getFieldVisibilityPermission($modtype,$current_user->id,$this->field_mapping_arr[$index])=='0')
								$activity[$this->field_mapping_arr[$index]] = $values[$count];
							else
								$activity[$this->field_mapping_arr[$index]] = '';
						} else {
							if(getFieldVisibilityPermission($modtype,$current_user->id,$index)=='0')
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
		if($activitytype=='VEVENT'){
			$activity['activitytype'] = 'Meeting';
			if(!empty($ical_activity['VALARM'])){
				$temp = str_replace("P",'',$ical_activity['VALARM']['TRIGGER']);
                //if there is negative value then ignore it because in vtiger even though its negative or postiview we 
                //make reminder to be before the event
                $temp = str_replace("-",'',$temp);
                $durationTypeCharacters = array('W','D','T','H','M','S');
                $reminder_time = 0;
                foreach($durationTypeCharacters as $durationType) {
                    if(strpos($temp,$durationType) == false){
                        continue;
                    }
                    $parts = explode($durationType, $temp);
                    $durationValue = $parts[0];
                    $temp = $parts[1];
                    $duration_type = $durationType;
                    $duration = intval($durationValue);
                    switch($duration_type){
                        case 'W' : 
                                    $reminder_time += 24*24*60*$durationValue;
                                    break;
                        case 'D' :
                                    $reminder_time += 24*60*$durationValue;
                                    break;
                        case 'T' : 
                                    //Skip this symbol since its just indicates the start of time component
                                    break;
                        case 'H' :
                                    $reminder_time += $duration*60;
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
		}
		return $activity;
	}

	function strtodatetime($date){
		$date = preg_replace('/[A-Za-z_]*/', '', $date);
		$year = substr($date,0,4);
		$month = substr($date,4,2);
		$day = substr($date,6,2);
		$hours = substr($date,8,2);
		$minutes = substr($date,10,2);
		$seconds = substr($date,12,2);
		$datetime[] = $year."-".$month."-".$day;
		$datetime[] = $hours.":".$minutes.":".$seconds;
		return $datetime;
	}
}

class iCalendar extends iCalendar_component {
    var $name = 'VCALENDAR';

    function construct() {
        $this->valid_properties = array(
            'CALSCALE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'METHOD'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PRODID'      => RFC2445_REQUIRED | RFC2445_ONCE,
            'VERSION'     => RFC2445_REQUIRED | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );

        $this->valid_components = array(
            'VEVENT', 'VTODO', 'VTIMEZONE'
            // TODO: add support for the other component types
            //, 'VJOURNAL', 'VFREEBUSY', 'VALARM'
        );
        parent::construct();
    }

}

class iCalendar_event extends iCalendar_component {

    var $name       = 'VEVENT';
    var $properties;
    var $mapping_arr = array(
    	'CLASS'			=>	array('component'=>'visibility','type'=>'string'),
    	'DESCRIPTION'	=>	array('component'=>'description','type'=>'string'),
    	'DTSTART'		=>	array('component'=>array('date_start','time_start'),'function'=>'iCalendar_event_dtstart','type'=>'datetime'),
    	'DTEND'			=>	array('component'=>array('due_date','time_end'),'function'=>'iCalendar_event_dtend','type'=>'datetime'),
    	'DTSTAMP'		=>	array('component'=>array('date_start','time_start'),'function'=>'iCalendar_event_dtstamp','type'=>'datetime'),
    	'LOCATION'		=>	array('component'=>'location','type'=>'string'),
    	'STATUS'		=>	array('component'=>'eventstatus','type'=>'string'),
    	'SUMMARY'		=>	array('component'=>'subject','type'=>'string'),
    	'PRIORITY'		=>	array('component'=>'priority','type'=>'string'),
    	'ATTENDEE'		=>	array('component'=>'activityid','function'=>'iCalendar_event_attendee','type'=>'user'),
    	'RESOURCES'		=>	array('component'=>array('location','eventstatus'),'type'=>'string'),
    );
    var $field_mapping_arr = array(
    	'priority'=>'taskpriority'
    );

    function construct() {

        $this->valid_components = array('VALARM');

        $this->valid_properties = array(
            'CLASS'          => RFC2445_OPTIONAL | RFC2445_ONCE,
            'CREATED'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DESCRIPTION'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            // Standard ambiguous here: in 4.6.1 it says that DTSTAMP in optional,
            // while in 4.8.7.2 it says it's REQUIRED. Go with REQUIRED.
            'DTSTAMP'        => RFC2445_REQUIRED | RFC2445_ONCE,
            // Standard ambiguous here: in 4.6.1 it says that DTSTART in optional,
            // while in 4.8.2.4 it says it's REQUIRED. Go with REQUIRED.
            'DTSTART'        => RFC2445_REQUIRED | RFC2445_ONCE,
            'GEO'            => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LAST-MODIFIED'  => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LOCATION'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ORGANIZER'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PRIORITY'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SEQUENCE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'STATUS'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SUMMARY'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TRANSP'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            // Standard ambiguous here: in 4.6.1 it says that UID in optional,
            // while in 4.8.4.7 it says it's REQUIRED. Go with REQUIRED.
            'UID'            => RFC2445_REQUIRED | RFC2445_ONCE,
            'URL'            => RFC2445_OPTIONAL | RFC2445_ONCE,
            'RECURRENCE-ID'  => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTEND'          => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DURATION'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ATTACH'         => RFC2445_OPTIONAL,
            'ATTENDEE'       => RFC2445_OPTIONAL,
            'CATEGORIES'     => RFC2445_OPTIONAL,
            'COMMENT'        => RFC2445_OPTIONAL,
            'CONTACT'        => RFC2445_OPTIONAL,
            'EXDATE'         => RFC2445_OPTIONAL,
            'EXRULE'         => RFC2445_OPTIONAL,
            'REQUEST-STATUS' => RFC2445_OPTIONAL,
            'RELATED-TO'     => RFC2445_OPTIONAL,
            'RESOURCES'      => RFC2445_OPTIONAL,
            'RDATE'          => RFC2445_OPTIONAL,
            'RRULE'          => RFC2445_OPTIONAL,
            RFC2445_XNAME    => RFC2445_OPTIONAL
        );

        parent::construct();
    }

    function invariant_holds() {
        // DTEND and DURATION must not appear together
        if(isset($this->properties['DTEND']) && isset($this->properties['DURATION'])) {
            return false;
        }


        if(isset($this->properties['DTEND']) && isset($this->properties['DTSTART'])) {
            // DTEND must be later than DTSTART
            // The standard is not clear on how to hande different value types though
            // TODO: handle this correctly even if the value types are different
            if($this->properties['DTEND'][0]->value <= $this->properties['DTSTART'][0]->value) {
                return false;
            }

            // DTEND and DTSTART must have the same value type
            if($this->properties['DTEND'][0]->val_type != $this->properties['DTSTART'][0]->val_type) {
                return false;
            }

        }
        return true;
    }

    function iCalendar_event_dtstamp($activity){
    	$components = gmdate('Ymd', strtotime($activity['date_start']." ".$activity['time_start']))."T".gmdate('His', strtotime($activity['date_start']." ".$activity['time_start']))."Z";
		$this->add_property("DTSTAMP",$components);
    	return true;
    }

   function iCalendar_event_dtstart($activity){
   		$time = str_replace(':','',$activity['time_start']);
   		if(strlen($time)<6){
   			while((6-strlen($time)) > 0 ){
   				$time .= '0';
   			}
   		}
    	$components = str_replace('-', '', $activity['date_start']).'T'. $time . 'Z';
		$this->add_property("DTSTART",$components);
    	return true;
    }

   function iCalendar_event_dtend($activity){
   		$time = str_replace(':','',$activity['time_end']);
   		if(strlen($time)<6){
   			while((6-strlen($time)) > 0 ){
   				$time .= '0';
   			}
   		}
    	$components = str_replace('-', '', $activity['due_date']).'T'. $time . 'Z';
		$this->add_property("DTEND",$components);
    	return true;
    }

	function iCalendar_event_attendee($activity){
		global $adb;
		$users_res = $adb->pquery("SELECT inviteeid FROM vtiger_invitees WHERE activityid=?", array($activity['id']));
		if($adb->num_rows($users_res)>0){
			for($i=0;$i<$adb->num_rows($users_res);$i++){
				$inviteeid = $adb->query_result($users_res,$i,'inviteeid');
				$username = getUserFullName($inviteeid);
				$user_email = getUserEmail($inviteeid);
				$attendee = 'mailto:'.$user_email;
				$this->add_property('ATTENDEE',$attendee);
			}
		}
    	return true;
	}

}

class iCalendar_todo extends iCalendar_component {
    var $name       = 'VTODO';
    var $properties;
    var $mapping_arr = array(
    	'DESCRIPTION'	=>	array('component'=>'description','type'=>'string'),
    	'DTSTAMP'		=>	array('component'=>array('date_start','time_start'),'function'=>'iCalendar_event_dtstamp','type'=>'datetime'),
    	'DTSTART'		=>	array('component'=>array('date_start','time_start'),'function'=>'iCalendar_event_dtstart','type'=>'datetime'),
    	'DUE'			=>	array('component'=>array('due_date'),'function'=>'iCalendar_event_dtend','type'=>'datetime'),
    	'STATUS'		=>	array('component'=>'status','type'=>'string'),
    	'SUMMARY'		=>	array('component'=>'subject','type'=>'string'),
    	'PRIORITY'		=>	array('component'=>'priority','type'=>'string'),
    	'RESOURCES'		=>	array('component'=>array('status'),'type'=>'string'),
    );
    var $field_mapping_arr = array(
    	'status'=>'taskstatus',
    	'priority'=>'taskpriority'
    );

    function construct() {

        $this->valid_components = array();
        $this->valid_properties = array(
            'CLASS'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'COMPLETED'   => RFC2445_OPTIONAL | RFC2445_ONCE,
            'CREATED'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DESCRIPTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTSTAMP'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTSTART'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'GEO'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LAST-MODIFIED'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LOCATION'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ORGANIZER'   => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PERCENT'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PRIORITY'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'RECURID'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SEQUENCE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'STATUS'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SUMMARY'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'UID'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'URL'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DUE'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DURATION'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ATTACH'      => RFC2445_OPTIONAL,
            'ATTENDEE'    => RFC2445_OPTIONAL,
            'CATEGORIES'  => RFC2445_OPTIONAL,
            'COMMENT'     => RFC2445_OPTIONAL,
            'CONTACT'     => RFC2445_OPTIONAL,
            'EXDATE'      => RFC2445_OPTIONAL,
            'EXRULE'      => RFC2445_OPTIONAL,
            'RSTATUS'     => RFC2445_OPTIONAL,
            'RELATED'     => RFC2445_OPTIONAL,
            'RESOURCES'   => RFC2445_OPTIONAL,
            'RDATE'       => RFC2445_OPTIONAL,
            'RRULE'       => RFC2445_OPTIONAL,
            'XPROP'       => RFC2445_OPTIONAL
        );

        parent::construct();
        // TODO:
        // either 'due' or 'duration' may appear in  a 'eventprop', but 'due'
        // and 'duration' MUST NOT occur in the same 'eventprop'
    }
    function iCalendar_event_dtstamp($activity){
    	$components = gmdate('Ymd', strtotime($activity['date_start']." ".$activity['time_start']))."T".gmdate('His', strtotime($activity['date_start']." ".$activity['time_start']))."Z";
		$this->add_property("DTSTAMP",$components);
    	return true;
    }

   function iCalendar_event_dtstart($activity){
   		$time = str_replace(':','',$activity['time_start']);
   		if(strlen($time)<6){
   			while((6-strlen($time)) > 0 ){
   				$time .= '0';
   			}
   		}
    	$components = str_replace('-', '', $activity['date_start']).'T'. $time . 'Z';
		$this->add_property("DTSTART",$components);
    	return true;
    }

   function iCalendar_event_dtend($activity){
    	$components = str_replace('-', '', $activity['due_date']).'T000000Z';
		$this->add_property("DUE",$components);
    	return true;
    }
}

class iCalendar_journal extends iCalendar_component {
    // TODO: implement
}

class iCalendar_freebusy extends iCalendar_component {
    // TODO: implement
}

class iCalendar_alarm extends iCalendar_component {
    var $name='VALARM';
    var $properties;
    var $mapping_arr = array(
    	'TRIGGER'	=>	array('component'=>'reminder_time', 'function'=>'iCalendar_event_trigger'),
    );

    function construct() {

        $this->valid_components = array();
        $this->valid_properties = array(
            'TRIGGER'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DESCRIPTION'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ACTION'          => RFC2445_OPTIONAL | RFC2445_ONCE,
            'X-WR-ALARMUID'   => RFC2445_OPTIONAL | RFC2445_ONCE,
             RFC2445_XNAME    => RFC2445_OPTIONAL
        );

        parent::construct();
    }

   function iCalendar_event_trigger($activity){
    	$reminder_time = $activity['reminder_time'];
    	if($reminder_time>60){
    		$reminder_time = round($reminder_time/60);
    		$reminder = $reminder_time.'H';
    	}else {
    		$reminder = $reminder_time.'M';
    	}
	    $this->add_property('ACTION', 'DISPLAY');
	    $this->add_property('TRIGGER', 'PT'.$reminder);
	    $this->add_property('DESCRIPTION', 'Reminder');
    	return true;
    }
}

class iCalendar_timezone extends iCalendar_component {
    var $name       = 'VTIMEZONE';
    var $properties;

    function construct() {
        $this->valid_components = array();
        $this->valid_properties = array(
            'TZID'        => RFC2445_REQUIRED | RFC2445_ONCE,
            'LAST-MODIFIED'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZURL'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            // TODO: the next two are components of their own!
            'STANDARDC'   => RFC2445_OPTIONAL,
            'DAYLIGHTC'   => RFC2445_OPTIONAL,
            'TZOFFSETFROM'   => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZOFFSETTO'   => RFC2445_OPTIONAL | RFC2445_ONCE,
            'X-PROP'      => RFC2445_OPTIONAL
        );

        parent::construct();
    }

}

// REMINDER: DTEND must be later than DTSTART for all components which support both
// REMINDER: DUE must be later than DTSTART for all components which support both

?>
