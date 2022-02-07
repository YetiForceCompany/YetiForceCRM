<?php
/**
 * Settings SlaPolicy DeleteAjax class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_SlaPolicy_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordModel = Settings_SlaPolicy_Record_Model::getInstanceById($request->getInteger('record'));
		$result =  ['success' => true];
		if ($recordModel) {
			$result =  ['success' => (bool) $recordModel->delete()];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
