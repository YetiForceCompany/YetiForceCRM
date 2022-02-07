<?php

/**
 * Class to delete.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PBX_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Function  proccess.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$result = true;
		$recordModel = Settings_PBX_Record_Model::getInstanceById($request->getInteger('record'));
		if ($recordModel) {
			$result = (bool) $recordModel->delete();
		}
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult(['success' => $result]);
		$responceToEmit->emit();
	}
}
