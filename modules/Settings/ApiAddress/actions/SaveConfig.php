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

class Settings_ApiAddress_SaveConfig_Action extends Settings_Vtiger_Basic_Action
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule(false);
		$elements = $request->get('elements');

		$result = Settings_ApiAddress_Module_Model::getInstance($moduleName)->setConfig($elements);

		if ($result)
			$result = array('success' => true, 'message' => vtranslate('LBL_SAVE_NOTIFY_OK', $moduleName));
		else
			$result = array('success' => false, 'message' => vtranslate('JS_ERROR', $moduleName));

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
