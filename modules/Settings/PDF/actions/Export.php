<?php

/**
 * Returns special functions for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_Export_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->get('frommodule');
		$templateIds = $request->get('template');
		$singlePdf = $request->get('single_pdf') == 1 ? true : false;

		if (count($templateIds) == 1) {
			$pdf = new Settings_PDF_mPDF_Model();
			$pdf->setTemplateId($templateIds[0]);
			$pdf->setRecordId($recordId);
			$pdf->setModuleName($moduleName);

			$template = Settings_PDF_Record_Model::getInstanceById($templateIds[0]);
			$pdf->setLanguage($template->get('language'));
			$pdf->setFileName($template->get('filename'));

			$pdf->parseParams($template->getParameters());

			$html = '';

			$pdf->setHeader('Header', $template->get('header_content'));
			$pdf->setFooter('Footer', $template->get('footer_content'));
			$html = $template->get('body_content');

			$pdf->loadHTML($html);
			$pdf->output();
		} else { // save multiple templates as pdf files
			if ($singlePdf) {
				$pdf = new Settings_PDF_mPDF_Model();
				$pdf->setRecordId($recordId);
				$pdf->setModuleName($moduleName);

				$firstTemplate = array_shift($templateIds);
				$template = Settings_PDF_Record_Model::getInstanceById($firstTemplate);
				$pdf->setLanguage($template->get('language'));

				$pdf->parseParams($template->getParameters());

				$html = '';

				$pdf->setHeader('Header', $template->get('header_content'));
				$pdf->setFooter('Footer', $template->get('footer_content'));
				$html = $template->get('body_content');

				$pdf->loadHTML($html);
				$pdf->writeHTML();

				foreach ($templateIds as $id) {
					$template = Settings_PDF_Record_Model::getInstanceById($id);
					$pdf->setLanguage($template->get('language'));

					$pdf->setHeader('Header' . $id, $template->get('header_content'));

					// building parameters
					$parameters = $template->getParameters();
					$pdf->parseParams($parameters);

					$pdf->pdf()->AddPageByArray($parameters);

					$pdf->setFooter('Footer' . $id, $template->get('footer_content'));
					$pdf->loadHTML($template->get('body_content'));
					$pdf->writeHTML();
				}
				$pdf->setFileName(vtranslate('LBL_MANY_IN_ONE', 'Settings:PDF'));
				$pdf->output();
			} else {
				mt_srand(time());
				$postfix = time() . '_' . mt_rand(0, 1000);

				$pdfFiles = [];
				foreach ($templateIds as $id) {
					$pdf = new Settings_PDF_mPDF_Model();
					$pdf->setTemplateId($id);
					$pdf->setRecordId($recordId);
					$pdf->setModuleName($moduleName);

					$template = Settings_PDF_Record_Model::getInstanceById($id);
					$pdf->setLanguage($template->get('language'));
					$pdf->setFileName($template->get('filename'));

					$pdf->parseParams($template->getParameters());

					$html = '';

					$pdf->setHeader('Header', $template->get('header_content'));
					$pdf->setFooter('Footer', $template->get('footer_content'));
					$html = $template->get('body_content');

					$pdf->loadHTML($html);
					$pdfFileName = 'storage/' . $pdf->getFileName() . '_' . $postfix . '.pdf';
					$pdf->output($pdfFileName, 'F');

					if (file_exists($pdfFileName)) {
						$pdfFiles[] = $pdfFileName;
					}
					unset($pdf, $template);
				}

				if (!empty($pdfFiles)) {
					Settings_PDF_Module_Model::zipAndDownload($pdfFiles);
				}
			}
		}
	}
}
