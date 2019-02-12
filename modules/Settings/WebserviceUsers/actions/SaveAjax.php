<?php

/**
 * Save Application.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceUsers_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * Save.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$data = [
			'server_id' => $request->getInteger('server_id'),
			'status' => $request->getInteger('status'),
			'user_name' => $request->getByType('user_name', 'Text'),
			'password_t' => $request->getRaw('password_t'),
			'type' => $request->getInteger('type'),
			'language' => $request->getByType('language', 'Text'),
			'popupReferenceModule' => $request->getByType('popupReferenceModule', 'Alnum'),
			'crmid' => $request->isEmpty('crmid') ? '' : $request->getInteger('crmid'),
			'crmid_display' => $request->getByType('crmid_display', 'Text'),
			'user_id' => $request->getInteger('user_id')
		];
		$typeApi = $request->getByType('typeApi', 'Alnum');
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($request->getInteger('record'), $typeApi);
		} else {
			$recordModel = Settings_WebserviceUsers_Record_Model::getCleanInstance($typeApi);
		}
		$result = $recordModel->save($data);
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($result);
		$responceToEmit->emit();
	}
}
