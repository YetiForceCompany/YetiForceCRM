<?php

/**
 * Settings meeting services delete ajax action file.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings meeting services delete ajax action class.
 */
class Settings_MeetingServices_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$recordModel = Settings_MeetingServices_Record_Model::getInstanceById($recordId);
		$result = $recordModel->delete();

		$response = new Vtiger_Response();
		$response->setResult(['success' => $result]);
		$response->emit();
	}
}
