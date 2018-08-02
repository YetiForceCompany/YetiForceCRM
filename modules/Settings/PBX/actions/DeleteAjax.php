<?php

/**
 * Class to delete.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PBX_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Function  proccess.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordModel = Settings_PBX_Record_Model::getInstanceById($request->getInteger('record'));
		$result = $recordModel->delete();

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($result);
		$responceToEmit->emit();
	}
}
