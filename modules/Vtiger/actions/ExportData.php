<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_ExportData_Action extends Vtiger_Mass_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getByType('source_module', 2);
		if (empty($moduleName)) {
			$moduleName = $request->getModule();
		}
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'Export')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function is called by the controller.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$exportModel = Vtiger_Export_Model::getInstanceFromRequest($request);
		if ($request->getMode() === 'ExportSelectedRecords') {
			$exportModel->setRecordList($this->getRecordsListFromRequest($request));
		}
		$exportModel->sendHttpHeader();
		$exportModel->exportData();
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getQuery(\App\Request $request)
	{
		$cvId = $request->isEmpty('viewname') ? '' : $request->getByType('viewname', 2);
		$moduleName = $request->getByType('source_module', 'Alnum');
		if (!empty($cvId) && $cvId === 'undefined' && $moduleName !== 'Users') {
			$sourceModule = $request->getByType('sourceModule', 2);
			$cvId = CustomView_Record_Model::getAllFilterByModule($sourceModule)->getId();
		}
		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if (!$customViewModel) {
			return false;
		}
		$selectedIds = $request->getArray('selected_ids', 2);
		if ($selectedIds && $selectedIds[0] !== 'all') {
			$queryGenerator = new App\QueryGenerator($moduleName);
			$queryGenerator->setFields(['id']);
			$queryGenerator->addCondition('id', $selectedIds, 'e');
			$queryGenerator->setStateCondition($request->getByType('entityState'));
			return $queryGenerator;
		}
		if (!$request->isEmpty('operator')) {
			$operator = $request->getByType('operator');
			$searchKey = $request->getByType('search_key', 'Alnum');
			$customViewModel->set('operator', $operator);
			$customViewModel->set('search_key', $searchKey);
			$customViewModel->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $searchKey, $operator));
		}
		$customViewModel->set('search_params', App\Condition::validSearchParams($moduleName, $request->getArray('search_params')));
		$customViewModel->set('entityState', $request->getByType('entityState'));
		return $customViewModel->getRecordsListQuery($request->getArray('excluded_ids', 2), $moduleName);
	}
}
