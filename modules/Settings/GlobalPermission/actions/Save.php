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

class Settings_GlobalPermission_Save_Action extends Vtiger_Action_Controller
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
		$profileID = $request->get('profileID');
		$checked = $request->get('checked');
		$globalactionid = $request->get('globalactionid');
		$checked == 'true' ? $checked = 1 : $checked = 0;
		$recordModel = Settings_GlobalPermission_Record_Model::save($profileID, $globalactionid, $checked);
		$response = new Vtiger_Response();
		$response->setResult(array('success' => true, 'message' => vtranslate('LBL_SAVE_OK', $request->getModule(false))));
		$response->emit();
	}
}
