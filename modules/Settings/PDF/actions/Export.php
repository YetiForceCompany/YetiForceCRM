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

		if (!is_array($recordId)) {
			$recordId = [$recordId];
		}

		if (count($templateIds) == 1) {
			$pdf = new Settings_PDF_mPDF_Model();
			$pdf->setTemplateId($templateIds[0]);
			$record = array_pop($recordId);
			$pdf->setRecordId($record);
			$pdf->setModuleName($moduleName);

			$template = Settings_PDF_Record_Model::getInstanceById($templateIds[0]);
			$template->setMainRecordId($record);

			$pdf->setLanguage($template->get('language'));
			$pdf->setFileName($template->get('filename'));

			$pdf->parseParams($template->getParameters());

			$html = '';

			$pdf->setHeader('Header', $template->getHeader());
			$pdf->setFooter('Footer', $template->getFooter());
			$html = $template->getBody();

			$pdf->loadHTML($html);
			$pdf->output();
		} else { // save multiple templates as pdf files
			if ($singlePdf) {
				$pdf = new Settings_PDF_mPDF_Model();
				foreach ($recordId as $index => $record) {
					$templateIdsTemp = $templateIds;
					$pdf->setRecordId($recordId[0]);
					$pdf->setModuleName($moduleName);

					$firstTemplate = array_shift($templateIdsTemp);
					$template = Settings_PDF_Record_Model::getInstanceById($firstTemplate);
					$template->setMainRecordId($record);
					$pdf->setLanguage($template->get('language'));

					$pdf->parseParams($template->getParameters());

					$html = '';

					$pdf->setHeader('Header_' . $record . '_' . $firstTemplate, $template->getHeader());
					if ($index > 0) {
						$pdf->pdf()->AddPageByArray($parameters);
					}
					$pdf->setFooter('Footer_' . $record . '_' . $firstTemplate, $template->getFooter());
					$html = $template->getBody();

					$pdf->loadHTML($html);
					$pdf->writeHTML();

					foreach ($templateIdsTemp as $id) {
						$template = Settings_PDF_Record_Model::getInstanceById($id);
						$template->setMainRecordId($record);
						$pdf->setLanguage($template->get('language'));

						$pdf->setHeader('Header_' . $record . '_' . $id, $template->getHeader());

						// building parameters
						$parameters = $template->getParameters();
						$pdf->parseParams($parameters);

						$pdf->pdf()->AddPageByArray($parameters);

						$pdf->setFooter('Footer_' . $record . '_' . $id, $template->getFooter());
						$pdf->loadHTML($template->getBody());
						$pdf->writeHTML();
					}
				}
				$pdf->setFileName(vtranslate('LBL_MANY_IN_ONE', 'Settings:PDF'));
				$pdf->output();
			} else {
				mt_srand(time());
				$postfix = time() . '_' . mt_rand(0, 1000);

				$pdfFiles = [];
				foreach ($templateIds as $id) {
					foreach ($recordId as $record) {
						$pdf = new Settings_PDF_mPDF_Model();
						$pdf->setTemplateId($id);
						$pdf->setRecordId($record);
						$pdf->setModuleName($moduleName);

						$template = Settings_PDF_Record_Model::getInstanceById($id);
						$template->setMainRecordId($record);
						$pdf->setLanguage($template->get('language'));
						$pdf->setFileName($template->get('filename'));

						$pdf->parseParams($template->getParameters());

						$html = '';

						$pdf->setHeader('Header', $template->getHeader());
						$pdf->setFooter('Footer', $template->getFooter());
						$html = $template->getBody();

						$pdf->loadHTML($html);
						$pdfFileName = 'storage/' . $record . '_' . $pdf->getFileName() . '_' . $postfix . '.pdf';
						$pdf->output($pdfFileName, 'F');

						if (file_exists($pdfFileName)) {
							$pdfFiles[] = $pdfFileName;
						}
						unset($pdf, $template);
					}
				}

				if (!empty($pdfFiles)) {
					Settings_PDF_Module_Model::zipAndDownload($pdfFiles);
				}
			}
		}
	}
}
