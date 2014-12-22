<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once dirname(__FILE__) . '/../api/ws/FetchRecordWithGrouping.php';

class Mobile_UI_SearchConfig extends Mobile_WS_FetchRecordWithGrouping {
	
	function cachedModule($moduleName) {
		$modules = $this->sessionGet('_MODULES'); // Should be available post login
		foreach($modules as $module) {
			if ($module->name() == $moduleName) return $module;
		}
		return false;
	}
	
	function cacheSearchFields($module, $fieldnames) {
		$this->sessionSet("_MODULE.{$module}.SEARCHFIELDS", $fieldnames);
	}
	
	function cachedSearchFields($module) {
		$cachekey = "_MODULE.{$module}.SEARCHFIELDS";
		return $this->sessionGet($cachekey, array());
	}
	
	function process(Mobile_API_Request $request) {
		$mode = $request->get('mode');
		$module = $this->cachedModule($request->get('module'));
		
		$searchIn = $this->cachedSearchFields($module->name());
		
		if($mode == 'update') {
			$searchIn = array();
			foreach($_REQUEST as $k=>$v) {
				if(preg_match("/field_(.*)/i", $k, $m)) {
					$searchIn[] = vtlib_purify($m[1]);
				}
			}
			$this->cacheSearchFields($module->name(), $searchIn);
			header("Location: index.php?_operation=listModuleRecords&module={$module->name()}&mode=search");
			exit;
		}
		
		$request->setDefault('record', "{$module->id()}x0");
		
		$wsResponse = parent::process($request);
		$wsResponseResult = $wsResponse->getResult();
		
		$templateRecord = Mobile_UI_ModuleRecordModel::buildModelFromResponse($wsResponseResult['record']);

		$viewer = new Mobile_UI_Viewer();
		$viewer->assign('_MODULE', $module );
		$viewer->assign('_RECORD', $templateRecord );
		$viewer->assign('_SEARCHIN', $searchIn);
		$viewer->assign('_SEARCHIN_ALL', empty($searchIn));

		$response = $viewer->process('generic/SearchConfig.tpl');
		
		return $response;
	}
}