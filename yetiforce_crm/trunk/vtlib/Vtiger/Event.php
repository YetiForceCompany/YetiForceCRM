<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once('vtlib/Vtiger/Utils.php');
include_once('modules/Users/Users.php');
@include_once('include/events/include.inc');

/**
 * Provides API to work with vtiger CRM Eventing (available from vtiger 5.1)
 * @package vtlib
 */
class Vtiger_Event {
	/** Event name like: vtiger.entity.aftersave, vtiger.entity.beforesave */
	var $eventname;
	/** Event handler class to use */
	var $classname;
	/** Filename where class is defined */
	var $filename;
	/** Condition for the event */
	var $condition;

	/** Internal caching */
	static $is_supported = '';
	
	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim=true) {
		Vtiger_Utils::Log($message, $delim);
	}

	/**
	 * Check if vtiger CRM support Events
	 */
	static function hasSupport() {
		if(self::$is_supported === '') {
			self::$is_supported = Vtiger_Utils::checkTable('vtiger_eventhandlers');
		}
		return self::$is_supported;
	}

	/**
	 * Handle event registration for module
	 * @param Vtiger_Module Instance of the module to use
	 * @param String Name of the Event like vtiger.entity.aftersave, vtiger.entity.beforesave
	 * @param String Name of the Handler class (should extend VTEventHandler)
	 * @param String File path which has Handler class definition
	 * @param String Condition for the event to trigger (default blank)
	 */
	static function register($moduleInstance, $eventname, $classname, $filename, $condition='', $dependent='[]') {
		// Security check on fileaccess, don't die if it fails
		if(Vtiger_Utils::checkFileAccess($filename, false)) {
			global $adb;
			$eventsManager = new VTEventsManager($adb);
			$eventsManager->registerHandler($eventname, $filename, $classname, $condition, $dependent);
			$eventsManager->setModuleForHandler($moduleInstance->name, $classname);

			self::log("Registering Event $eventname with [$filename] $classname ... DONE");
		}
	}

	/**
	 * Trigger event based on CRM Record
	 * @param String Name of the Event to trigger
	 * @param Integer CRM record id on which event needs to be triggered.
	 */
	static function trigger($eventname, $crmid) {
		if(!self::hasSupport()) return;

		global $adb;
		$checkres = $adb->pquery("SELECT setype, crmid, deleted FROM vtiger_crmentity WHERE crmid=?", Array($crmid));
		if($adb->num_rows($checkres)) {
			$result = $adb->fetch_array($checkres, 0);
			if($result['deleted'] == '0') {
				$module = $result['setype'];
				$moduleInstance = CRMEntity::getInstance($module);
				$moduleInstance->retrieve_entity_info($result['crmid'], $module);
				$moduleInstance->id = $result['crmid'];

				global $current_user;
				if(!$current_user) {
					$current_user = new Users();
					$current_user->id = $moduleInstance->column_fields['assigned_user_id'];
				}

				// Trigger the event
				$em = new VTEventsManager($adb);
				$em->triggerEvent($eventname, VTEntityData::fromCRMEntity($moduleInstance));
			}
		}		
	}

	/**
	 * Get all the registered module events
	 * @param Vtiger_Module Instance of the module to use
	 */
	static function getAll($moduleInstance) {
		global $adb;
		$events = false;
		if(self::hasSupport()) {
			// Get all events related to module
			$records = $adb->pquery("SELECT * FROM vtiger_eventhandlers WHERE handler_class IN 
				(SELECT handler_class FROM vtiger_eventhandler_module WHERE module_name=?)", Array($moduleInstance->name)); 
			if($records) {
				while($record = $adb->fetch_array($records)) {
					$event = new Vtiger_Event();					
					$event->eventname = $record['event_name'];
					$event->classname = $record['handler_class']; 
					$event->filename  = $record['handler_path'];
					$event->condition = $record['condition'];
					$events[] = $event;
				}
			}
		}
		return $events;
	}		
}
?>