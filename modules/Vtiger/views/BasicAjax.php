<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_BasicAjax_View extends Vtiger_Basic_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showAdvancedSearch');
		$this->exposeMethod('showSearchResults');
	}

	public function checkPermission()
	{
		
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		return true;
	}

	public function postProcess(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}
		return;
	}

	/**
	 * Function to display the UI for advance search on any of the module
	 * @param Vtiger_Request $request
	 */
	public function showAdvancedSearch(Vtiger_Request $request)
	{
		//Modules for which search is excluded
		$excludedModuleForSearch = array('Vtiger', 'Reports');

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		if ($request->get('source_module')) {
			$moduleName = $request->get('source_module');
		}

		$saveFilterPermitted = true;
		$saveFilterexcludedModules = array('ModComments', 'RSS', 'Portal', 'Integration', 'PBXManager', 'DashBoard');
		if (in_array($moduleName, $saveFilterexcludedModules)) {
			$saveFilterPermitted = false;
		}

		//See if it is an excluded module, If so search in home module
		if (in_array($moduleName, $excludedModuleForSearch)) {
			$moduleName = 'Home';
		}
		$module = $request->getModule();

		$customViewModel = new CustomView_Record_Model();
		$customViewModel->setModule($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

		$viewer->assign('SEARCHABLE_MODULES', Vtiger_Module_Model::getSearchableModules());
		$viewer->assign('CUSTOMVIEW_MODEL', $customViewModel);

		if ($moduleName === 'Calendar') {
			$advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
		} else {
			$advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
		}
		$viewer->assign('ADVANCED_FILTER_OPTIONS', \App\CustomView::ADVANCED_FILTER_OPTIONS);
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
		$viewer->assign('DATE_FILTERS', Vtiger_AdvancedFilter_Helper::getDateFilter($module));
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('SOURCE_MODULE', $moduleName);
		$viewer->assign('SOURCE_MODULE_MODEL', $moduleModel);
		$viewer->assign('MODULE', $module);
		$viewer->assign('SAVE_FILTER_PERMITTED', $saveFilterPermitted);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		echo $viewer->view('AdvanceSearch.tpl', $moduleName, true);
	}

	/**
	 * Function to display the Search Results
	 * @param Vtiger_Request $request
	 */
	public function showSearchResults(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$advFilterList = $request->get('advfilterlist');

		//used to show the save modify filter option
		$isAdvanceSearch = false;
		$matchingRecords = [];
		if (is_array($advFilterList) && $advFilterList) {
			$isAdvanceSearch = true;
			$queryGenerator = new \App\QueryGenerator($moduleName);
			$queryGenerator->setFields(['id']);
			$queryGenerator->parseAdvFilter($advFilterList);
			$query = $queryGenerator->createQuery();
			$rows = $query->limit(100)->all();
			foreach ($rows as &$row) {
				$recordId = current($row);
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
				$recordModel->set('permitted', true);
				$matchingRecords[$moduleName][$recordId] = $recordModel;
			}
			$viewer->assign('SEARCH_MODULE', $moduleName);
		} else {
			$searchKey = $request->get('value');
			$limit = $request->get('limit') != 'false' ? $request->get('limit') : false;
			$operator = (!$request->isEmpty('operator') ) ? $request->get('operator') : false;
			$searchModule = false;
			if ($request->get('searchModule')) {
				$searchModule = $request->get('searchModule');
			}
			$viewer->assign('SEARCH_KEY', $searchKey);
			$viewer->assign('SEARCH_MODULE', $searchModule);
			$matchingRecords = Vtiger_Record_Model::getSearchResult($searchKey, $searchModule, $limit, $operator);
			if (AppConfig::search('GLOBAL_SEARCH_SORTING_RESULTS') === 1) {
				$matchingRecordsList = [];
				foreach (\App\Module::getAllEntityModuleInfo(true) as &$module) {
					if (isset($matchingRecords[$module['modulename']]) && $module['turn_off'] == 1) {
						$matchingRecordsList[$module['modulename']] = $matchingRecords[$module['modulename']];
					}
				}
				$matchingRecords = $matchingRecordsList;
			}
		}
		$curentModule = $request->get('curentModule');
		if (AppConfig::search('GLOBAL_SEARCH_CURRENT_MODULE_TO_TOP') && isset($matchingRecords[$curentModule])) {
			$pushTop = $matchingRecords[$curentModule];
			unset($matchingRecords[$curentModule]);
			$matchingRecords = [$curentModule => $pushTop] + $matchingRecords;
		}
		if ($request->get('html') === 'true') {
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('MATCHING_RECORDS', $matchingRecords);
			$viewer->assign('IS_ADVANCE_SEARCH', $isAdvanceSearch);
			echo $viewer->view('UnifiedSearchResults.tpl', '', true);
		} else {
			$recordsList = [];
			foreach ($matchingRecords as $module => &$modules) {
				foreach ($modules as $recordID => &$recordModel) {
					$label = decode_html($recordModel->getName());
					$label .= ' (' . \App\Fields\Owner::getLabel($recordModel->get('smownerid')) . ')';
					if (!$recordModel->get('permitted')) {
						$label .= ' <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>';
					}
					$recordsList[] = [
						'id' => $recordID,
						'module' => $module,
						'category' => vtranslate($module, $module),
						'label' => $label,
						'permitted' => $recordModel->get('permitted'),
					];
				}
			}
			$response = new Vtiger_Response();
			$response->setResult($recordsList);
			$response->emit();
		}
	}
}
