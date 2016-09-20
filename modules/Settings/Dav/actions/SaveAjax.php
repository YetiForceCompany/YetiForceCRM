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

class Settings_Dav_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addKey');
		$this->exposeMethod('deleteKey');
	}

	public function addKey(Vtiger_Request $request)
	{
		$params = $request->get('params');
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Dav_Module_Model::getInstance($qualifiedModuleName);
		$result = $moduleModel->addKey($params);
		$success = true;
		$message = vtranslate('LBL_SUCCESS_SAVE_KEY', $request->getModule(false));
		if ($result === 0) {
			$success = false;
			$message = vtranslate('LBL_ERROR_SAVE_KEY', $request->getModule(false));
		} elseif ($result === 1) {
			$success = false;
			$message = vtranslate('LBL_DUPLICATE_USER_SERVICES', $request->getModule(false));
		}
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $success,
			'key' => $result,
			'message' => $message
		));
		$response->emit();
	}

	public function deleteKey(Vtiger_Request $request)
	{
		$params = $request->get('params');
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Dav_Module_Model::getInstance($qualifiedModuleName);
		$result = $moduleModel->deleteKey($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => vtranslate('LBL_KEY_HAS_BEEN_REMOVED', $request->getModule(false))
		));
		$response->emit();
	}
}
