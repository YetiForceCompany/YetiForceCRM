<?php

/**
 * Save Application
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WebserviceUsers_SaveAjax_Action extends Settings_Vtiger_Save_Action
{

	/**
	 * Save
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$data = $request->get('param');
		$typeApi = $request->get('typeApi');
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		if ($recordId) {
			$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($recordId, $typeApi);
		} else {
			$recordModel = Settings_WebserviceUsers_Record_Model::getCleanInstance($typeApi);
		}
		$result = $recordModel->save($data);

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($result);
		$responceToEmit->emit();
	}
}
