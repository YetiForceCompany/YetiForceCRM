<?php
/**
 * Save Action Class for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */

class Settings_PDF_Save_Action extends Settings_Vtiger_Basic_Action
{

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$step = $request->get('step');

		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstanceById($recordId);
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance('');
		}

		$response = new Vtiger_Response();

		$pdfModel->save($step);

		$response->setResult(['id' => $pdfModel->get('pdfid')]);
		$response->emit();
	}
}
