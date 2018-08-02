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

abstract class Vtiger_Mass_Action extends \App\Controller\Action
{
	/**
	 * Get query for records list from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\QueryGenerator|bool
	 */
	public static function getQuery(\App\Request $request)
	{
		$cvId = $request->isEmpty('viewname') ? '' : $request->getByType('viewname', 2);
		$moduleName = $request->getByType('module');
		if (!empty($cvId) && $cvId === 'undefined' && $request->getByType('source_module', 2) !== 'Users') {
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

			return $queryGenerator;
		}
		if (!$request->isEmpty('operator')) {
			$customViewModel->set('operator', $request->getByType('operator'));
			$customViewModel->set('search_key', $request->getByType('search_key'));
			$customViewModel->set('search_value', $request->get('search_value'));
		}
		$customViewModel->set('search_params', $request->get('search_params'));

		return $customViewModel->getRecordsListQuery($request->get('excluded_ids'), $moduleName);
	}

	/**
	 * Get records list from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public static function getRecordsListFromRequest(\App\Request $request)
	{
		$selectedIds = $request->getArray('selected_ids', 2);
		if ($selectedIds && $selectedIds[0] !== 'all') {
			return $selectedIds;
		}
		$queryGenerator = static::getQuery($request);

		return $queryGenerator ? $queryGenerator->createQuery()->column() : [];
	}
}
