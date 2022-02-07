<?php
/**
 * Settings BusinessHours DeleteAjax class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$result = true;
		$recordModel = Settings_BusinessHours_Record_Model::getInstanceById($request->getInteger('record'));
		if ($recordModel) {
			$result = (bool) $recordModel->delete();
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => $result]);
		$response->emit();
	}
}
