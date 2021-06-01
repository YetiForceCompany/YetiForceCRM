<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Roles_MoveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function process(App\Request $request)
	{
		$recordId = $request->getByType('record', 2);
		$parentRoleId = $request->getByType('parent_roleid', 2);

		$parentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
		$recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);
		$recordModel->moveTo($parentRole);

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->emit();
	}
}
