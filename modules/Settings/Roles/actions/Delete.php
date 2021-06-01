<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Roles_Delete_Action extends Settings_Vtiger_Basic_Action
{
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->getByType('record', 'Alnum');
		$transferRecordId = $request->getByType('transfer_record', 'Alnum');

		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);
		$transferToRole = Settings_Roles_Record_Model::getInstanceById($transferRecordId);
		if ($recordModel && $transferToRole && $recordId != $transferRecordId && false === strpos($transferToRole->get('parentrole') . '::', $recordModel->get('parentrole'))) {
			$recordModel->delete($transferToRole);
		}
		$redirectUrl = $moduleModel->getDefaultUrl();
		header("location: $redirectUrl");
	}
}
