<?php

/**
 * Save Action Class for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PDF_Save_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$step = $request->get('step');
		$moduleName = $request->get('module_name');

		if ($recordId) {
			$pdfModel = Vtiger_PDF_Model::getInstanceById($recordId, $moduleName);
		} else {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($moduleName);
		}

		$stepFields = Settings_PDF_Module_Model::getFieldsByStep($step);
		foreach ($stepFields as $field) {
			if ($field == 'body_content') {
				$value = $request->getForHtml($field);
			} else {
				$value = $request->get($field);
			}

			if (is_array($value)) {
				$value = implode(',', $value);
			}

			if ($field === 'module_name' && $pdfModel->get('module_name') != $value) {
				// change of main module, overwrite existing conditions
				$pdfModel->deleteConditions();
			}
			$pdfModel->set($field, $value);
		}
		$pdfModel->set('conditions', $request->get('conditions'));
		Settings_PDF_Record_Model::transformAdvanceFilterToWorkFlowFilter($pdfModel);
		Settings_PDF_Record_Model::save($pdfModel, $step);

		$response = new Vtiger_Response();
		$response->setResult(['id' => $pdfModel->get('pdfid')]);
		$response->emit();
	}
}
