<?php

/**
 * Delete Action Class for PDF Settings.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PDF_DeleteAjax_Action extends Settings_Vtiger_Index_Action
{
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('record');

		$response = new Vtiger_Response();
		$recordModel = Vtiger_PDF_Model::getInstanceById($recordId);
		if (Settings_PDF_Record_Model::delete($recordModel)) {
			$response->setResult(['success' => 'true']);
		} else {
			$response->setResult(['success' => 'false']);
		}
		$response->emit();
	}
}
