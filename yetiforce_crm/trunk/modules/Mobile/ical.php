<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

chdir( dirname(__FILE__). '/../../');

// Include ICAL library
set_include_path(dirname(__FILE__) . '/third-party/qCal' . PATH_SEPARATOR . get_include_path());
include_once dirname(__FILE__) . '/third-party/qCal/autoload.php';

include_once 'vtlib/Vtiger/Module.php';

/**
 * Core class to process ICAL request
 */
class Mobile_ICAL {

	// User context
	private $userfocus;

	// DB Connection
	private $db;

	/**
	 * Default constructor
	 */
	function __construct() {
		$this->db = PearDatabase::getInstance();
	}

	/**
	 * Authenticate user
	 *
	 * @param String $username
	 * @param String $password
	 * @return True if authenticated, false otherwise
	 */
	function authenticate($username, $password) {

		$this->userfocus = CRMEntity::getInstance('Users');
		$this->userfocus->column_fields['user_name'] = $username;
		$authsuccess = $this->userfocus->doLogin($password);

		if($authsuccess) {
			if(!isset($this->userfocus->id)) {
				$this->userfocus->id = $this->userfocus->retrieve_user_id($username);
			}
		}

		return $authsuccess;
	}

	/**
	 * Prepare date to useable icalendar format
	 *
	 * @param String $date yyyy-mm-dd format
	 * @return yyyymmdd
	 */
	function formatDate($date) {
		if(empty($date)) $date = date('Y-m-d');
		return str_replace('-', '', $date);
	}

	/**
	 * Prepare date-time to useable icalendar format
	 *
	 * @param String $date yyyy-mm-dd format
	 * @param String $time hh:ii:ss format
	 * @return yyyymmddThhiissZ
	 */
	function formatDateTime($date, $time) {
		if(empty($date) || preg_match("/0000-00-00/", $date)) {
			$date = date('Y-m-d');
		}

		if(empty($time)) $time = "00:00:00";

		// Hous not padded?
        if(preg_match("/([0-9]):([0-9][0-9])$/", $time, $m)) {
        	$time = sprintf("0%s:%s", $m[1], $m[2]);
        }
        // Minutes not padded?
        if(preg_match("/([0-9][0-9]):([0-9])$/", $time, $m)) {
        	$time = sprintf("%s:0%s", $m[1], $m[2]);
        }
		if(strlen($time) == 5) $time = "{$time}:00";

		return sprintf("%sT%sZ", $this->formatDate($date), str_replace(':','',$time));
	}

	/**
	 * Prepare date-timestamp to useable icalendar format
	 *
	 * @param String $value yyyy-mm-dd hh:ii:ss format
	 * @return yyyymmddThhiissZ
	 */
	function formatDateTimestamp($value) {
		return str_replace(array('-', ':', ' '), array('','','T'), trim($value)) . 'Z';
	}

	/**
	 * Format value based on its current state.
	 *
	 * @param String $value
	 * @param String $defvalue
	 * @return unknown|unknown
	 */
	function formatValue($value, $defvalue='') {
		if(is_null($value) || empty($value)) return $defvalue;
		return $value;
	}

	/**
	 * Generate icalendar data output.
	 *
	 * @return String
	 */
	function generate() {

		$properties = array();
		$properties['prodid'] = '-//vtiger/Mobile/NONSGML 1.0//EN';

		$ical = new qCal($properties);

		// TODO Configure timezone information.

		$fieldnames = array(
			'activityid', 'subject', 'description', 'activitytype', 'location', 'reminder_time',
			'date_start', 'time_start', 'due_date', 'time_end', 'modifiedtime'
		);

		$query = "SELECT " . implode(',', $fieldnames) . " FROM vtiger_activity
			INNER JOIN vtiger_crmentity ON
			(vtiger_activity.activityid=vtiger_crmentity.crmid 	AND vtiger_crmentity.deleted = 0 AND vtiger_crmentity.smownerid = ?)
			LEFT JOIN vtiger_activity_reminder ON vtiger_activity_reminder.activity_id=vtiger_activity.activityid
			WHERE vtiger_activity.activitytype != 'Emails'";

		$result = $this->db->pquery($query, array($this->userfocus->id));

		while($resultrow = $this->db->fetch_array($result)) {

			$properties = array();
			$properties['uid']         = $resultrow['activityid'];
			$properties['summary']     = $this->formatValue(decode_html($resultrow['subject']));
			$properties['description'] = $this->formatValue(decode_html($resultrow['description']));
			$properties['class']       = 'PRIVATE';
			$properties['dtstart']     = $this->formatDateTime( $resultrow['date_start'], $resultrow['time_start']);
			$properties['dtend']       = $this->formatDateTime( $resultrow['due_date'], $resultrow['time_end']);
			$properties['dtstamp']     = $this->formatDateTimestamp($resultrow['modifiedtime']);
			$properties['location']    = $this->formatValue($resultrow['location']);

			if($resultrow['activitytype'] == 'Task') {
				// Tranform the parameter
				$properties['due'] = $properties['dtend'];
				unset($properties['dtend']);

				$icalComponent = new qCal_Component_Vtodo($properties);
			} else {

				$icalComponent = new qCal_Component_Vevent($properties);

				if(!empty($resultrow['reminder_time'])) {
					$alarmProperties = array();
					$alarmProperties['trigger'] = $resultrow['reminder_time'] * 60;
					$icalComponent->attach(new qCal_Component_Valarm($alarmProperties));
				}
			}

			$ical->attach($icalComponent);
		}

		return $ical->render();
	}

	/**
	 * Helper method to process the request and emit output
	 *
	 * @param String $username
	 * @param String $password
	 */
	static function process($username, $password) {
		$mobileical = new Mobile_ICAL();

		if(!$mobileical->authenticate($username, $password)) {
			header('Content-type: text/plain');
			echo "FAILED";
		} else {
			$icalContent = $mobileical->generate();
			header('Content-Disposition: attachment; filename="icalendar.ics"');
			header('Content-Length: '. strlen($icalContent));
			echo $icalContent;
		}
	}
}

// To make it easier for subscribing to Calendar via applications we support the

// url format: http://localhost:81/modules/Mobile/ical.php/username@mail.com/password

// Retrieve username and password from the URL
$pathinfo = $_SERVER['PATH_INFO'];
if(empty($pathinfo)) $pathinfo = "/";


// Extract username and password
$parts = explode('/', $pathinfo);
$matches = array();
$matches[2] = array_pop($parts);
$matches[1] = array_pop($parts);

// Process the request
if (vtlib_isModuleActive('Mobile')) {
	Mobile_ICAL::process($matches[1], $matches[2]);
}
