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

class Vtiger_Export_View extends Vtiger_Index_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'Export')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewId = $entityState = false;
		$selectedIds = $request->getArray('selected_ids', 2);
		$excludedIds = $request->getArray('excluded_ids', 2);
		$page = $request->getInteger('page');
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		if (!$request->isEmpty('viewname')) {
			$viewId = $request->getByType('viewname', 2);
		}
		if (!$request->isEmpty('entityState')) {
			$entityState = $request->getByType('entityState');
		}
		$viewer->assign('VIEWID', $viewId);
		$viewer->assign('ENTITY_STATE', $entityState);
		$viewer->assign('PAGE', $page);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE', 'Export');
		$viewer->assign('XML_TPL_LIST', Import_Module_Model::getListTplForXmlType($moduleName));
		$viewer->assign('EXPORT_TYPE', \App\Export\Records::getSupportedFileFormats($moduleName));
		$viewer->assign('OPERATOR', $request->getByType('operator'));
		$viewer->assign('ALPHABET_VALUE', \App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $request->getByType('search_key', 'Alnum'), $request->getByType('operator')));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 'Alnum'));
		$viewer->assign('SEARCH_PARAMS', \App\Condition::validSearchParams($moduleName, $request->getArray('search_params'), false));
		$advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : [];
		if ($advancedConditions) {
			\App\Condition::validAdvancedConditions($advancedConditions);
		}
		$viewer->assign('ADVANCED_CONDITIONS', $advancedConditions);
		$viewer->view('Export.tpl', $moduleName);
	}
}
