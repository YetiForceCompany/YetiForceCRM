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

class Vtiger_Export_View extends Vtiger_Index_View
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
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'Export')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$sourceModule = $request->getModule();
		$viewId = $request->getByType('viewname', 2);
		$selectedIds = $request->getArray('selected_ids', 2);
		$excludedIds = $request->getArray('excluded_ids', 2);
		$page = $request->getInteger('page');
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('ENTITY_STATE', $request->getByType('entityState'));
		$viewer->assign('VIEWID', $viewId);
		$viewer->assign('PAGE', $page);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('MODULE', 'Export');
		$viewer->assign('XML_TPL_LIST', Import_Module_Model::getListTplForXmlType($sourceModule));
		$viewer->assign('EXPORT_TYPE', Vtiger_Export_Model::getSupportedFileFormats($sourceModule));
		$viewer->assign('OPERATOR', $request->getByType('operator'));
		$viewer->assign('ALPHABET_VALUE', \App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $sourceModule, $request->getByType('search_key', 'Alnum'), $request->getByType('operator')));
		$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 'Alnum'));
		$viewer->assign('SEARCH_PARAMS', \App\Condition::validSearchParams($sourceModule, $request->getArray('search_params')));
		$viewer->view('Export.tpl', $sourceModule);
	}
}
