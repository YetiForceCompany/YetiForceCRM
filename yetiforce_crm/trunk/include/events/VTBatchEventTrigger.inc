<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class VTBatchEventTrigger extends VTEventTrigger{

	function trigger($data){
		$adb = $this->adb;

		$eventInfos = self::lookupCache($this->name);
		if($eventInfos === false) {
			$eventInfos = self::getActiveEventInfos($this->adb, $this->name);
		}
        if(!empty($eventInfos)){
   		foreach($eventInfos as $eventInfo) {
			require_once($eventInfo['handler_path']);
			$handler = new $eventInfo['handler_class']();
			$handler->handleEvent($this->name, $data);
		}
        }
	}
}