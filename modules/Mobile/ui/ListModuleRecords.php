<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/../api/ws/ListModuleRecords.php';
include_once dirname(__FILE__) . '/models/SearchFilter.php';

class Mobile_UI_ListModuleRecords extends Mobile_WS_ListModuleRecords {
	
	function cachedModule($moduleName) {
		$modules = $this->sessionGet('_MODULES'); // Should be available post login
		foreach($modules as $module) {
			if ($module->name() == $moduleName) return $module;
		}
		return false;
	}
	
	function getPagingModel(Mobile_API_Request $request) {
		$pagingModel =  Mobile_WS_PagingModel::modelWithPageStart($request->get('page'));
		$pagingModel->setLimit(Mobile::config('Navigation.Limit', 100));
		return $pagingModel;
	}
	
	/** For search capability */
	function cachedSearchFields($module) {
		$cachekey = "_MODULE.{$module}.SEARCHFIELDS";
		return $this->sessionGet($cachekey, false);
	}
	
	function getSearchFilterModel($module, $search) {
		$searchFilter = false;
		if (!empty($search)) {
			$criterias = array('search' => $search, 'fieldnames' => $this->cachedSearchFields($module));
			$searchFilter = Mobile_UI_SearchFilterModel::modelWithCriterias($module, $criterias);
			return $searchFilter;
		}
		return $searchFilter;
	}
	/** END */
	
	function process(Mobile_API_Request $request) {
		$wsResponse = parent::process($request);

		$response = false;
		if($wsResponse->hasError()) {
			$response = $wsResponse;
		} else {
			$wsResponseResult = $wsResponse->getResult();

			$viewer = new Mobile_UI_Viewer();
			$viewer->assign('_MODULE', $this->cachedModule($wsResponseResult['module']) );
			$viewer->assign('_RECORDS', Mobile_UI_ModuleRecordModel::buildModelsFromResponse($wsResponseResult['records']) );
			$viewer->assign('_MODE', $request->get('mode'));
			$viewer->assign('_PAGER', $this->getPagingModel($request));
			$viewer->assign('_SEARCH', $request->get('search'));

			$response = $viewer->process('generic/List.tpl');
		}
		return $response;
	}

}