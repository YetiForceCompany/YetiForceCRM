<?php

/**
 * Settings DataAccess ExecuteHandlers action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_DataAccess_ExecuteHandlers_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(\App\Request $request)
	{
		return;
	}

	public function process(\App\Request $request)
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
