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

class OSSMailScanner_SaveWidgetConfig_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$param = $request->get('name');
		$val = $request->get('value');
		$conf_type = $request->get('conf_type');
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$recordModel->setConfigWidget($conf_type, $param, $val);
		$result = array('success' => true, 'data' => vtranslate('JS_save_config_info', 'OSSMailScanner'));
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
