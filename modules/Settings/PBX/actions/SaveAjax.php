<?php

/**
 * Save pbx record.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PBX_SaveAjax_Action extends Settings_Vtiger_Save_Action
{
	/**
	 * Save pbx record.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		if (!$request->isEmpty('record', true)) {
			$recordModel = Settings_PBX_Record_Model::getInstanceById($request->getInteger('record'));
		} else {
			$recordModel = Settings_PBX_Record_Model::getCleanInstance();
		}
		$recordModel->parseFromRequest($request->getArray('param', 'Text'));
		$result = $recordModel->save();

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($result);
		$responceToEmit->emit();
	}
}
