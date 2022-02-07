<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
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
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();

		if (!\App\Privilege::isPermitted($moduleName, 'EditView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('FOLDERS', $moduleModel->getAllFolders());
		$viewer->assign('SELECTED_IDS', $request->getArray('selected_ids', 'Alnum'));
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', 'Alnum'));
		$viewer->assign('VIEWNAME', $request->getByType('viewname', 'Alnum'));

		$searchKey = $request->getByType('search_key', 'Alnum');

		$operator = $request->getByType('operator');
		$searchValue = App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $searchKey, $operator);
		$viewer->assign('OPERATOR', $operator);
		$viewer->assign('ALPHABET_VALUE', $searchValue);
		$viewer->assign('SEARCH_KEY', $searchKey);

		$viewer->view('MoveDocuments.tpl', $moduleName);
	}
}
