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
	/**
	 * Save watermark image.
	 *
	 * @param Vtiger_PDF_Model $pdfModel
	 *
	 * @throws \yii\db\Exception
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string image filename
	 */
	public function saveWatermarkImage(Vtiger_PDF_Model $pdfModel)
	{
		if (empty($_FILES['watermark_image_file'])) {
			return '';
		}
		$targetDir = Settings_PDF_Module_Model::$uploadPath;
		$templateId = $pdfModel->getId();
		$targetFile = $targetDir . (string) $templateId;
		$fileInstance = \App\Fields\File::loadFromRequest($_FILES['watermark_image_file']);
		if (!$fileInstance->validate('image')) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||watermark_image_file', 406);
		}
		if (!$fileInstance->moveFile($targetFile)) {
			throw new \App\Exceptions\AppException('ERR_CREATE_FILE_FAILURE');
		}
		if ($pdfModel->get('watermark_image') !== $targetFile) {
			\App\Db::getInstance('admin')
				->createCommand()
				->update('a_#__pdf', ['watermark_image' => $targetFile], ['pdfid' => $templateId])
				->execute();
		}
		return $targetFile;
	}

	/**
	 * Process request.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
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
			if ($field === 'body_content' || $field === 'header_content' || $field === 'footer_content' || $field === 'watermark_text') {
				$value = $request->getForHtml($field);
			} else {
				$value = $request->get($field);
			}
			if (is_array($value)) {
				if ($field === 'conditions') {
					$value = json_encode($value);
				} else {
					$value = implode(',', $value);
				}
			}
			if ($field === 'module_name' && $pdfModel->get('module_name') !== $value) {
				// change of main module, overwrite existing conditions
				$pdfModel->deleteConditions();
			}
			if ($field === 'watermark_image') {
				$value = '';
				if ($pdfModel->get('watermark_image')) {
					$value = $pdfModel->get('watermark_image');
				}
			}
			$pdfModel->set($field, $value);
		}
		$pdfModel->set('conditions', $request->getArray('conditions', 'Text'));
		Settings_PDF_Record_Model::transformAdvanceFilterToWorkFlowFilter($pdfModel);
		Settings_PDF_Record_Model::save($pdfModel, $step);
		$this->saveWatermarkImage($pdfModel);
		$response = new Vtiger_Response();
		$response->setResult(['id' => $pdfModel->get('pdfid')]);
		$response->emit();
	}
}
