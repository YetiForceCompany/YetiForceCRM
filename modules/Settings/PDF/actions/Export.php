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
		$emailPdf = $request->get('email_pdf') == 1 ? true : false;

		if (!is_array($recordId)) {
			$recordId = [$recordId];
		}

		if (count($templateIds) == 1 && count($recordId) == 1) {
			if ($emailPdf) {
				$filePath = 'cache/pdf/'.$recordId[0].'_'.time().'.pdf';
				Settings_PDF_mPDF_Model::exportToPdf($recordId[0], $moduleName, $templateIds[0], $filePath, 'F');
				if (file_exists($filePath)) {
					header('Location: index.php?module=OSSMail&view=compose&pdf_path='.$filePath);
					exit;
				} else {
					throw new AppException(vtranslate('LBL_EXPORT_ERROR', 'Settings:PDF'));
				}
			} else {
				Settings_PDF_mPDF_Model::exportToPdf($recordId[0], $moduleName, $templateIds[0]);
			}
		} else { // save multiple templates as pdf files
			if ($singlePdf) {
				$pdf = new Settings_PDF_mPDF_Model();
				$styles = '';
				$headers = '';
				$footers = '';
				$classes = '';
				$body = '';
				foreach ($recordId as $index => $record) {
					$templateIdsTemp = $templateIds;
					$pdf->setRecordId($recordId[0]);
					$pdf->setModuleName($moduleName);

					$firstTemplate = array_shift($templateIdsTemp);
					$template = Settings_PDF_Record_Model::getInstanceById($firstTemplate);
					$template->setMainRecordId($record);
					$pdf->setLanguage($template->get('language'));
					$template->getParameters();
					//$pdf->parseParams($template->getParameters());

					$styles .= " @page template_{$record}_{$firstTemplate} {
						sheet-size: {$template->getFormat()};
						margin-top: {$template->get('margin_top')}mm;
						margin-left: {$template->get('margin_left')}mm;
						margin-right: {$template->get('margin_right')}mm;
						margin-bottom: {$template->get('margin_bottom')}mm;
						odd-header-name: html_Header_{$record}_{$firstTemplate};
						odd-footer-name: html_Footer_{$record}_{$firstTemplate};
					}";
					$html = '';

					$headers .= ' <htmlpageheader name="Header_' . $record . '_' . $firstTemplate . '">' . $template->getHeader() . '</htmlpageheader>';

					$footers .= ' <htmlpagefooter name="Footer_' . $record . '_' . $firstTemplate . '">' . $template->getFooter() . '</htmlpagefooter>';

					$classes .= ' div.page_' . $record . '_' . $firstTemplate . ' { page-break-before: always; page: template_' . $record . '_' . $firstTemplate . '; }';

					$body .= '<div class="page_' . $record . '_' . $firstTemplate . '">' . $template->getBody() . '</div>';
					foreach ($templateIdsTemp as $id) {
						$template = Settings_PDF_Record_Model::getInstanceById($id);
						$template->setMainRecordId($record);
						$pdf->setLanguage($template->get('language'));

						// building parameters
						$parameters = $template->getParameters();
						//$pdf->parseParams($parameters);

						$styles .= " @page template_{$record}_{$id} {
							sheet-size: {$template->getFormat()};
							margin-top: {$template->get('margin_top')}mm;
							margin-left: {$template->get('margin_left')}mm;
							margin-right: {$template->get('margin_right')}mm;
							margin-bottom: {$template->get('margin_bottom')}mm;
							odd-header-name: html_Header_{$record}_{$id};
							odd-footer-name: html_Footer_{$record}_{$id};
						}";
						$html = '';

						$headers .= ' <htmlpageheader name="Header_' . $record . '_' . $id . '">' . $template->getHeader() . '</htmlpageheader>';

						$footers .= ' <htmlpagefooter name="Footer_' . $record . '_' . $id . '">' . $template->getFooter() . '</htmlpagefooter>';

						$classes .= ' div.page_' . $record . '_' . $id . ' { page-break-before: always; page: template_' . $record . '_' . $id . '; }';

						$body .= '<div class="page_' . $record . '_' . $id . '">' . $template->getBody() . '</div>';
					}
				}
				$html = "<html><head><style>{$styles} {$classes}</style></head><body>{$headers} {$footers} {$body}</body></html>";
				$pdf->loadHTML($html);
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
					if (!empty($emailPdf)) {
						Settings_PDF_Module_Model::zipAndEmail($pdfFiles);
					} else {
						Settings_PDF_Module_Model::zipAndDownload($pdfFiles);
					}
				}
			}
		}
	}
}
