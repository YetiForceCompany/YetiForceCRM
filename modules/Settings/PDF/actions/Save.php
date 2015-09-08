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
		$summary = $request->get('summary');
		$moduleName = $request->get('module_name');

		if ($recordId) {
			$pdfModel = Settings_PDF_Record_Model::getInstance($recordId);
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
		}

		$response = new Vtiger_Response();
		$pdfModel->set('summary', $summary);
		$pdfModel->set('module_name', $moduleName);

		$pdfModel->save();

		$response->setResult(['id' => $pdfModel->get('pdfid')]);
		$response->emit();
	}
}
