<?php

/**
 * List View Class for PDF Settings.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PDF_Import_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		\App\Log::trace('Start ' . __METHOD__);
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		if ($request->has('upload') && $request->getBoolean('upload')) {
			$fileInstance = \App\Fields\File::loadFromRequest($_FILES['imported_xml']);
			if (!$fileInstance->validate() || 'xml' !== $fileInstance->getExtension(true)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FILE');
			}
			$imagePath = '';
			$base64Image = false;
			$pdfModel = Settings_PDF_Record_Model::getCleanInstance();
			$xml = simplexml_load_file($fileInstance->getPath());
			foreach ($xml as $fieldsValue) {
				foreach ($fieldsValue as $fieldValue) {
					foreach ($fieldValue as $columnKey => $columnValue) {
						switch ($columnKey) {
							case 'imageblob':
								$base64Image = (string) $columnValue;
								break;
							case 'watermark_image':
								$imagePath = (string) $columnValue;
								$pdfModel->set($columnKey, '');
								break;
							case 'header_content':
							case 'body_content':
							case 'footer_content':
								$pdfModel->set($columnKey, App\Purifier::purifyHtml((string) $columnValue));
								break;
							default:
								$pdfModel->set($columnKey, App\Purifier::purify((string) $columnValue));
						}
					}
				}
			}
			Settings_PDF_Record_Model::save($pdfModel, 'import');
			if ($pdfModel->getId() && $imagePath && $base64Image) {
				$targetDir = Settings_PDF_Module_Model::$uploadPath;
				$imageInstance = \App\Fields\File::loadFromInfo([
					'content' => base64_decode($base64Image),
					'path' => $imagePath,
					'name' => 'watermark_image',
					'validateAllCodeInjection' => true,
				]);
				if (!$imageInstance->validateAndSecure('image')) {
					throw new \App\Exceptions\Security('ERR_ILLEGAL_WATERMARK_IMAGE');
				}
				$newFilePath = $targetDir . $pdfModel->getId() . '.' . $imageInstance->getExtension();
				$pdfModel->set('watermark_image', $newFilePath);
				Settings_PDF_Record_Model::save($pdfModel, 8);
				file_put_contents($newFilePath, $imageInstance->getContents());
			}
			$viewer->assign('RECORDID', $pdfModel->getId());
			$viewer->assign('UPLOAD', true);
		}
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('Import.tpl', $qualifiedModule);
		\App\Log::trace('End ' . __METHOD__);
	}

	public function getHeaderCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'modules.Settings.' . $request->getModule() . '.Edit',
		]), parent::getHeaderCss($request));
	}
}
