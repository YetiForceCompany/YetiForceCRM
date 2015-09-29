<?php

/**
 * Returns special functions for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_DeleteWatermark_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	function process(Vtiger_Request $request)
	{
		$recordId = $request->get('id');
		$pdfModel = Settings_PDF_Record_Model::getInstanceById($recordId);
		
		$output = $pdfModel->deleteWatermark();

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
