<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Roles_DeleteAjax_View extends Settings_Roles_IndexAjax_View
{
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->getByType('record', 2);

		$recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);
		$allRoles = Settings_Roles_Record_Model::getAll();
		unset($allRoles[$recordId]);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('ALL_ROLES', $allRoles);
		echo $viewer->view('DeleteTransferForm.tpl', $qualifiedModuleName, true);
	}
}
