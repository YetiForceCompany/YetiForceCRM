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

class SMSNotifier_MassSaveAjax_Action extends Vtiger_Mass_Action
{
	/**
	 * Check Permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', 2);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'CreateView') || !$currentUserPriviligesModel->hasModuleActionPermission($sourceModule, 'MassSendSMS') || !SMSNotifier_Module_Model::checkServer()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function that saves SMS records.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', 2);
		$queryGenerator = $this->getRecordsListQueryFromRequest($request);
		$phoneFieldList = $fields = $request->getArray('fields', 2);
		$fields[] = 'id';

		$queryGenerator->setFields($fields);
		$query = $queryGenerator->createQuery();
		$dataReader = $query->createCommand()->query();
		$recordIds = $toNumbers = [];
		while ($row = $dataReader->read()) {
			$numberSelected = false;
			foreach ($phoneFieldList as $fieldName) {
				if (!empty($row[$fieldName])) {
					$toNumbers[] = preg_replace_callback('/[^\d]/s', function ($m) {
						return '';
					}, $row[$fieldName]);
					$numberSelected = true;
				}
			}
			if ($numberSelected) {
				$recordIds[] = $row['id'];
			}
		}
		$dataReader->close();
		$toNumbers = array_unique($toNumbers);
		$response = new Vtiger_Response();
		if (!empty($toNumbers)) {
			SMSNotifier_Module_Model::addSmsToCron($request->getForHtml('message'), $toNumbers, $recordIds, $sourceModule);
			$response->setResult(true);
		} else {
			$response->setResult(false);
		}
		return $response;
	}

	/**
	 * Function gets query of records list.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\QueryGenerator
	 */
	public function getRecordsListQueryFromRequest(\App\Request $request)
	{
		$module = $request->getModule();
		$sourceModule = $request->getByType('source_module', 'Alnum');
		$selectedIds = $request->getArray('selected_ids', 'Alnum');
		$excludedIds = $request->getArray('excluded_ids', 'Alnum');
		if (!empty($selectedIds) && !in_array($selectedIds[0], ['all', '"all"']) && count($selectedIds) > 0) {
			$queryGenerator = new \App\QueryGenerator($sourceModule);
			$queryGenerator->addCondition('id', $selectedIds, 'e');
			return $queryGenerator;
		}
		$customViewModel = CustomView_Record_Model::getInstanceById($request->getByType('viewname', 'Alnum'));

		if ($customViewModel) {
			if (!$request->isEmpty('operator')) {
				$operator = $request->getByType('operator');
				$searchKey = $request->getByType('search_key', 'Alnum');
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $sourceModule, $searchKey, $operator));
			}
			$customViewModel->set('search_params', App\Condition::validSearchParams($sourceModule, $request->getArray('search_params')));
			$customViewModel->set('entityState', $request->getByType('entityState'));
			return $customViewModel->getRecordsListQuery($excludedIds, $module);
		}
	}
}
