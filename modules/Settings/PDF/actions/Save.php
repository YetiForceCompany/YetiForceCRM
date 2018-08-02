<?php

/**
 * Save Action Class for PDF Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PDF_Save_Action extends Settings_Vtiger_Index_Action
{
	public function process(\App\Request $request)
	{
		$step = $request->getByType('step', 2);
		if ($request->isEmpty('record', true)) {
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance($request->getByType('module_name', 2));
		} else {
			$pdfModel = Vtiger_PDF_Model::getInstanceById($request->getInteger('record'), $request->getByType('module_name', 2));
		}
		$stepFields = Settings_PDF_Module_Model::getFieldsByStep($step);
		foreach ($stepFields as $field) {
			if ($field === 'body_content' || $field === 'header_content' || $field === 'footer_content') {
				$value = $request->getForHtml($field);
			} else {
				$value = $request->get($field);
			}
			if (is_array($value)) {
				$value = implode(',', $value);
			}
			if ($field === 'module_name' && $pdfModel->get('module_name') !== $value) {
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
