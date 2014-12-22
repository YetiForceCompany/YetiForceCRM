<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/Login.php';

class Mobile_WS_LoginAndFetchModules extends Mobile_WS_Login {
	
	function postProcess(Mobile_API_Response $response) {
		$current_user = $this->getActiveUser();
		
		if ($current_user) {
			$result = $response->getResult();
			$result['modules'] = $this->getListing($current_user);
			$response->setResult($result);
		}
	}
		
	function getListing($user) {
		$modulewsids = Mobile_WS_Utils::getEntityModuleWSIds();
		
		// Disallow modules
		unset($modulewsids['Users']);
		
		// Calendar & Events module will be merged
		unset($modulewsids['Events']);

		$listresult = vtws_listtypes(null,$user);
		
		$listing = array();
		foreach($listresult['types'] as $index => $modulename) {
			if(!isset($modulewsids[$modulename])) continue;
			
			$listing[] = array(
				'id'   => $modulewsids[$modulename],
				'name' => $modulename,
				'isEntity' => $listresult['information'][$modulename]['isEntity'],
				'label' => $listresult['information'][$modulename]['label'],
				'singular' => $listresult['information'][$modulename]['singular'],
			);
		}
		
		return $listing;
	}
}
