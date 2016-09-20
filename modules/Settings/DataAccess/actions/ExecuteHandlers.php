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

class Settings_DataAccess_ExecuteHandlers_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		return;
	}

	public function process(Vtiger_Request $request)
	{
		$param = $request->get('param');
		$Resp = Settings_DataAccess_Module_Model::executeAjaxHandlers($param['module'], $param);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $Resp['success'],
			'data' => $Resp['data'])
		);
		$response->emit();
	}
}
