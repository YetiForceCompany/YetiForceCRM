<?php

/**
 * Save Application.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings_MeetingServices_SaveAjax_Action class.
 */
class Settings_MeetingServices_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			$recordModel = Settings_MeetingServices_Record_Model::getCleanInstance();
		} else {
			$recordModel = Settings_MeetingServices_Record_Model::getInstanceById($request->getInteger('record'));
		}
		$recordModel->setDataFromRequest($request);
		$result = $recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
