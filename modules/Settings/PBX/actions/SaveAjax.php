<?php

/**
 * Save pbx record
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PBX_SaveAjax_Action extends Settings_Vtiger_Save_Action
{

	/**
	 * Save pbx record
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		if ($recordId) {
			$recordModel = Settings_PBX_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_PBX_Record_Model::getCleanInstance();
		}
		$recordModel->parseFromRequest($request->getArray('param'));
		$result = $recordModel->save();

		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($result);
		$responceToEmit->emit();
	}
}
