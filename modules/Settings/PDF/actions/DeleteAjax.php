<?php
/**
 * Delete Action Class for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */

class Settings_PDF_DeleteAjax_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');

		$response = new Vtiger_Response();
		$recordModel = Settings_PDF_Record_Model::getInstanceById($recordId);
		if ($recordModel->delete()) {
			$response->setResult(array('success' => 'true'));
		} else {
			$response->setResult(array('success' => 'false'));
		}
		$response->emit();
	}
