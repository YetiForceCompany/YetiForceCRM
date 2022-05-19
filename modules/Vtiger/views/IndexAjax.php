<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Vtiger_IndexAjax_View extends Vtiger_Index_View
{
	use App\Controller\ClearProcess;
	use \App\Controller\ExposeMethod;

	public function getRecordsListFromRequest(App\Request $request)
	{
		$cvId = $request->getByType('cvid', 2);
		$selectedIds = $request->getArray('selected_ids', 2);
		$excludedIds = $request->getArray('excluded_ids', 2);

		if (!empty($selectedIds) && 'all' !== $selectedIds[0] && \count($selectedIds) > 0) {
			return $selectedIds;
		}
		if (!empty($cvId) && 'undefined' == $cvId) {
			$sourceModule = $request->getByType('sourceModule', 2);
			$cvId = CustomView_Record_Model::getAllFilterByModule($sourceModule)->getId();
		}

		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if ($customViewModel) {
			if (!$request->isEmpty('operator', true)) {
				$operator = $request->getByType('operator');
				$searchKey = $request->getByType('search_key', 'Alnum');
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $request->getModule(), $searchKey, $operator));
			}
			if ($request->has('search_params')) {
				$customViewModel->set('search_params', App\Condition::validSearchParams($request->getModule(), $request->getArray('search_params')));
			}
			if ($advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : []) {
				$customViewModel->set('advancedConditions', \App\Condition::validAdvancedConditions($advancedConditions));
			}
			return $customViewModel->getRecordIds($excludedIds);
		}
	}
}
