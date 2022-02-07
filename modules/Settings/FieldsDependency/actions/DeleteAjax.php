<?php
/**
 * Settings fields dependency delete action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings fields dependency delete action class.
 */
class Settings_FieldsDependency_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordModel = Settings_FieldsDependency_Record_Model::getInstanceById($request->getInteger('record'));
		if ($recordModel) {
			$result = ['success' => $recordModel->delete()];
		} else {
			$result = ['success' => false];
			\App\Log::error('Not found Fields Dependency record with this id :' . $request->getInteger('record'));
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
