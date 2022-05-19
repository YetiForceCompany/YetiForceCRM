<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

abstract class Vtiger_Mass_Action extends \App\Controller\Action
{
	/**
	 * Get query for records list from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\QueryGenerator|bool
	 */
	public static function getQuery(App\Request $request)
	{
		$cvId = $request->isEmpty('viewname') ? '' : $request->getByType('viewname', 2);
		$moduleName = $request->getByType('module', 'Alnum');
		if (!empty($cvId) && 'undefined' === $cvId && 'Users' !== $request->getByType('source_module', 2)) {
			$sourceModule = $request->getByType('sourceModule', 2);
			$cvId = CustomView_Record_Model::getAllFilterByModule($sourceModule)->getId();
		}
		$customViewModel = CustomView_Record_Model::getInstanceById((int) $cvId);
		if (!$customViewModel) {
			return false;
		}
		$selectedIds = $request->getArray('selected_ids', 2);
		if ($selectedIds && 'all' !== $selectedIds[0]) {
			$queryGenerator = new App\QueryGenerator($moduleName);
			$queryGenerator->initForCustomViewById($cvId);
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
		if ($request->getBoolean('isSortActive') && !$request->isEmpty('orderby')) {
			$customViewModel->set('orderby', $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL));
		}
		$customViewModel->set('search_params', App\Condition::validSearchParams($moduleName, $request->getArray('search_params')));
		$customViewModel->set('entityState', $request->getByType('entityState'));
		if ($advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : []) {
			$customViewModel->set('advancedConditions', \App\Condition::validAdvancedConditions($advancedConditions));
		}
		return $customViewModel->getRecordsListQuery($request->getArray('excluded_ids', 2), $moduleName);
	}

	/**
	 * Get records list from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return int[]
	 */
	public static function getRecordsListFromRequest(App\Request $request): array
	{
		$selectedIds = $request->getArray('selected_ids', 2);
		if ($selectedIds && 'all' !== $selectedIds[0]) {
			return $selectedIds;
		}
		$queryGenerator = static::getQuery($request);
		return $queryGenerator ? $queryGenerator->clearFields()->createQuery()->column() : [];
	}
}
