<?php
/* * ************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************ */

class Vtiger_MergeRecord_View extends \App\Controller\View\Page
{
	public function checkPermission(App\Request $request)
	{
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$records = $request->getExploded('records');
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$fieldModels = $moduleModel->getFields();

		foreach ($records as $recordId) {
			if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
				$recordModels[] = Vtiger_Record_Model::getInstanceById($recordId);
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDS', $records);
		$viewer->assign('RECORDMODELS', $recordModels);
		$viewer->assign('FIELDS', $fieldModels);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('MergeRecords.tpl', $moduleName);
	}
}
