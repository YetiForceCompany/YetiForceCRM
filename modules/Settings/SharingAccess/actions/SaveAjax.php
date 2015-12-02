<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

Class Settings_SharingAccess_SaveAjax_Action extends Vtiger_SaveAjax_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser()) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$modulePermissions = $request->get('permissions');
		$modulePermissions[4] = $modulePermissions[6];

		foreach ($modulePermissions as $tabId => $permission) {
			$moduleModel = Settings_SharingAccess_Module_Model::getInstance($tabId);
			$moduleModel->set('permission', $permission);

			try {
				$moduleModel->save();
			} catch (AppException $e) {
				
			}
		}
		Settings_SharingAccess_Module_Model::recalculateSharingRules();

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->emit();
	}
}
