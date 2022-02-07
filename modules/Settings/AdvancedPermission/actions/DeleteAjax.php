<?php

/**
 * Advanced permission delete action model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($request->getInteger('record'));
		$result = true;
		if ($recordModel) {
			$result = (bool) $recordModel->delete();
		}
		$response = new Vtiger_Response();
		$response->setResult(['success' => $result]);
		$response->emit();
	}
}
