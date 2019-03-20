<?php
 /* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Documents_DownloadFile_Action extends Vtiger_RelationAjax_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$records = [];
		if ($request->has('record')) {
			$records[] = $request->getInteger('record');
		} else {
			$relatedModuleName = $request->getModule();
			$sourceModule = $request->getByType('sourceModule', 2);
			$sourceRecordId = $request->getInteger('src_record');
			$pagingModel = new Vtiger_Paging_Model();
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModule);
			$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
			$excludedIds = $request->getArray('excluded_ids', 'Integer');
			if ('all' === $request->getArray('selected_ids')[0]) {
				if ($request->has('entityState')) {
					$relationListView->set('entityState', $request->getByType('entityState'));
				}
				$operator = 's';
				if (!$request->isEmpty('operator', true)) {
					$operator = $request->getByType('operator');
					$relationListView->set('operator', $operator);
				}
				if (!$request->isEmpty('search_key', true)) {
					$searchKey = $request->getByType('search_key', 'Alnum');
					$relationListView->set('search_key', $searchKey);
					$relationListView->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $relatedModuleName, $searchKey, $operator));
				}
				$searchParmams = App\Condition::validSearchParams($relatedModuleName, $request->getArray('search_params'));
				if (empty($searchParmams) || !is_array($searchParmams)) {
					$searchParmams = [];
				}
				$transformedSearchParams = $relationListView->get('query_generator')->parseBaseSearchParamsToCondition($searchParmams);
				$relationListView->set('search_params', $transformedSearchParams);
				$rows = array_keys($relationListView->getEntries($pagingModel));
			} else {
				$rows = '[]' === $request->getRaw('selected_ids') ? [] : $request->getArray('selected_ids', 'Integer');
			}
			foreach ($rows as $id) {
				if (!in_array($id, $excludedIds) && \App\Privilege::isPermitted($relatedModuleName, 'DetailView', $id)) {
					$records[] = $id;
				}
			}
		}
		if (1 === count($records)) {
			$documentRecordModel = Vtiger_Record_Model::getInstanceById($records[0], $relatedModuleName);
			//Download the file
			$documentRecordModel->downloadFile();
			//Update the Download Count
			$documentRecordModel->updateDownloadCount();
		} else {
			Documents_Record_Model::downloadFiles($records);
		}
	}
}
