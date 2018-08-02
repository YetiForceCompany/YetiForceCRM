<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Documents_MoveDocuments_View extends Vtiger_Index_View
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
		$moduleName = $request->getModule();

		if (!\App\Privilege::isPermitted($moduleName, 'EditView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('FOLDERS', $moduleModel->getAllFolders());
		$viewer->assign('SELECTED_IDS', $request->get('selected_ids'));
		$viewer->assign('EXCLUDED_IDS', $request->get('excluded_ids'));
		$viewer->assign('VIEWNAME', $request->getByType('viewname', 2));

		$searchKey = $request->getByType('search_key', 2);
		$searchValue = $request->get('search_value');
		$operator = $request->getByType('operator', 1);
		if (!empty($operator)) {
			$viewer->assign('OPERATOR', $operator);
			$viewer->assign('ALPHABET_VALUE', $searchValue);
			$viewer->assign('SEARCH_KEY', $searchKey);
		}

		$viewer->view('MoveDocuments.tpl', $moduleName);
	}
}
