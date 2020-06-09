<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_CronTasks_AddAjax_View extends Settings_Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_CronTasks_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		$viewer = $this->getViewer($request);

		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_LIST', Settings_Workflows_Module_Model::getSupportedModules());
		$viewer->assign('RECORD', $recordId);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('AddAjax.tpl', $qualifiedModuleName);
	}
}
