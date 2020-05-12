<?php

/**
 * Class to delete.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MeetingServices_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * {@inheritdoc}
	 */
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
