<?php

/**
 * Delete Action Class for MappedFields Settings
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_DeleteAjax_Action extends Settings_Vtiger_Index_Action
{

	public function process(\App\Request $request)
	{
		$recordId = $request->get('record');

		$response = new Vtiger_Response();
		$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
		if ($moduleInstance->delete()) {
			$response->setResult(array('success' => 'true'));
		} else {
			$response->setResult(array('success' => 'false'));
		}
		$response->emit();
	}
}
