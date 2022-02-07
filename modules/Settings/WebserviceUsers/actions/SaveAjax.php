<?php

/**
 * Save Application.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_WebserviceUsers_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$typeApi = $request->getByType('typeApi', 'Alnum');
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_WebserviceUsers_Record_Model::getInstanceById($request->getInteger('record'), $typeApi);
		} else {
			$recordModel = Settings_WebserviceUsers_Record_Model::getCleanInstance($typeApi);
		}
		$recordModel->setDataFromRequest($request);
		try {
			if ($response = $recordModel->checkData()) {
				$result = ['success' => false, 'message' => \App\Language::translate($response, $request->getModule(false))];
			} else {
				$recordModel->save();
				$result = ['success' => true];
			}
		} catch (\Exception $e) {
			\App\Log::error($e->getMessage() . PHP_EOL . $e->__toString());
			$result = ['success' => false, 'message' => \App\Language::translate('ERR_NOT_ALLOWED_VALUE')];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
