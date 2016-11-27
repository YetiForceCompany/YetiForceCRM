<?php

/**
 * Returns special functions for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PDF_Watermark_Action extends Settings_Vtiger_Index_Action
{

	public function __construct()
	{
		$this->exposeMethod('Delete');
		$this->exposeMethod('Upload');
	}

	public function Delete(Vtiger_Request $request)
	{
		$recordId = $request->get('id');
		$pdfModel = Vtiger_PDF_Model::getInstanceById($recordId);
		$output = Settings_PDF_Record_Model::deleteWatermark($pdfModel);

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}

	public function Upload(Vtiger_Request $request)
	{
		$templateId = $request->get('template_id');
		$newName = basename($_FILES['watermark']['name'][0]);
		$newName = explode('.', $newName);
		$newName = $templateId . '.' . end($newName);
		$targetDir = Settings_PDF_Module_Model::$uploadPath;
		$targetFile = $targetDir . $newName;
		$uploadOk = 1;

		$fileInstance = \App\Fields\File::loadFromPath($_FILES['watermark']['tmp_name'][0]);
		if (!$fileInstance->validate('image')) {
			$uploadOk = 0;
		}

		// Check allowed upload file size
		if ($uploadOk && $_FILES['watermark']['size'][0] > vglobal('upload_maxsize')) {
			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk === 1) {
			$db = App\Db::getInstance('admin');
			$watermarkImage = (new \App\Db\Query())->select('watermark_image')
				->from('a_#__pdf')
				->where(['pdfid' => $templateId])
				->scalar($db);
			if (file_exists($watermarkImage)) {
				unlink($watermarkImage);
			}
			// successful upload
			if ($fileInstance->moveFile($targetFile)) {
				$db->createCommand()
					->update('a_#__pdf', ['watermark_image' => $targetFile], ['pdfid' => $templateId])
					->execute();
			}
		}
	}
}
