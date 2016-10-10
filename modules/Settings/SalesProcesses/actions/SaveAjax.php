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

class Settings_SalesProcesses_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateConfig');
	}

	public function updateConfig(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$moduleModel = Settings_SalesProcesses_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $moduleModel->setConfig($param),
			'message' => vtranslate('LBL_SAVE_CONFIG', $request->getModule(false))
		));
		$response->emit();
	}
}
