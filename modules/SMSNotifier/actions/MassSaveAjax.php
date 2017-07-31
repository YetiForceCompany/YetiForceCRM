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
	 * Check Permission
	 * @param \App\Request $request
	 * @throws \Exception\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$sourceModule = $request->get('source_module');
		if (!\App\Privilege::isPermitted($sourceModule, 'CreateView') || !\App\Privilege::isPermitted($sourceModule, 'MassSendSMS') || !SMSNotifier_Module_Model::checkServer()) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function that saves SMS records
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$sourceModule = $request->get('source_module');
		$queryGenerator = $this->getRecordsListQueryFromRequest($request);
		$phoneFieldList = $fields = $request->get('fields');
		$fields[] = 'id';

		$queryGenerator->setFields($fields);
		$query = $queryGenerator->createQuery();
		$dataReader = $query->createCommand()->query();
		$recordIds = $toNumbers = [];
		while ($row = $dataReader->read()) {
			$numberSelected = false;
			foreach ($phoneFieldList as $fieldName) {
				if (!empty($row[$fieldName])) {
					$toNumbers[] = preg_replace_callback('/[^\d]/s', function($m) {
						return '';
					}, $row[$fieldName]);
					$numberSelected = true;
				}
			}
			if ($numberSelected) {
				$recordIds[] = $row['id'];
			}
		}
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
	 * Function gets query of records list 
	 * @param \App\Request $request
	 * @return \App\QueryGenerator
	 */
	public function getRecordsListQueryFromRequest(\App\Request $request)
	{
		$cvId = $request->get('viewname');
		$module = $request->getModule();
		$sourceModule = $request->get('source_module');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		if (!empty($selectedIds) && !in_array($selectedIds, ['all', '"all"'])) {
			if (!empty($selectedIds) && count($selectedIds) > 0) {
				$queryGenerator = new \App\QueryGenerator($sourceModule);
				$queryGenerator->addCondition('id', $selectedIds, 'e');
				return $queryGenerator;
			}
		}

		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if ($customViewModel) {
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');
			$operator = $request->get('operator');
			if (!empty($operator)) {
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', $searchValue);
			}

			$customViewModel->set('search_params', $request->get('search_params'));
			return $customViewModel->getRecordsListQuery($excludedIds, $module);
		}
	}
}
