<?php

/**
 * Delete Action Class for MappedFields Settings.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_DeleteAjax_Action extends Settings_Vtiger_Index_Action
{
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');

		$response = new Vtiger_Response();
		$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);
		if ($moduleInstance->delete()) {
			$response->setResult(['success' => 'true']);
		} else {
			$response->setResult(['success' => 'false']);
		}
		$response->emit();
	}
}
