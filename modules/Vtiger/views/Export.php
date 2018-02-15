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

	public function process(\App\Request $request)
	{
		$sourceModule = $request->getModule();
		$viewId = $request->getByType('viewname', 2);
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		$page = $request->getInteger('page');
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('VIEWID', $viewId);
		$viewer->assign('PAGE', $page);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('MODULE', 'Export');
		$viewer->assign('XML_TPL_LIST', Import_Module_Model::getListTplForXmlType($sourceModule));
		$viewer->assign('EXPORT_TYPE', ['LBL_CSV' => 'csv', 'LBL_XML' => 'xml']);
		if (!$request->isEmpty('operator')) {
			$viewer->assign('OPERATOR', $request->getByType('operator', 1));
			$viewer->assign('ALPHABET_VALUE', $request->get('search_value'));
			$viewer->assign('SEARCH_KEY', $request->getByType('search_key', 1));
		}
		$viewer->assign('SEARCH_PARAMS', $request->get('search_params'));
		$viewer->view('Export.tpl', $sourceModule);
	}
}
