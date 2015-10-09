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
			$pdf->setPageSize($template->get('page_format'), $template->get('page_orientation'));
			if ($template->get('margin_chkbox') == 0) {
				$pdf->setMargins(
					$template->get('margin_top'), $template->get('margin_right'), $template->get('margin_bottom'), $template->get('margin_left')
				);
			}

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
				$pdf->setPageSize($template->get('page_format'), $template->get('page_orientation'));
				if ($template->get('margin_chkbox') == 0) {
					$pdf->setMargins(
						$template->get('margin_top'), $template->get('margin_right'), $template->get('margin_bottom'), $template->get('margin_left')
					);
				}

				$html = '';

				$pdf->setHeader('Header', $template->get('header_content'));
				$pdf->setFooter('Footer', $template->get('footer_content'));
				$html = $template->get('body_content');

				$pdf->loadHTML($html);
				$pdf->writeHTML();

				foreach ($templateIds as $id) {
					$template = Settings_PDF_Record_Model::getInstanceById($id);

					$pdf->setHeader('Header' . $id, $template->get('header_content'));

					$parameters = [];
					$parameters['orientation'] = $template->get('page_format') . '-' . $pdf->pageOrientation[$template->get('page_orientation')];
					// margins
					if ($template->get('margin_chkbox') == 0) {
						$parameters['margin-top'] = $template->get('margin_top');
						$parameters['margin-right'] = $template->get('margin_right');
						$parameters['margin-bottom'] = $template->get('margin_bottom');
						$parameters['margin-left'] = $template->get('margin_left');
					}
//					$pdf->pdf()->AddPage('','','','','',
//						$template->get('margin_left'),$template->get('margin_right'),$template->get('margin_top'),$template->get('margin_bottom'));
					$pdf->pdf()->AddPageByArray($parameters);
					$pdf->setFooter('Footer' . $id, $template->get('footer_content'));
					$pdf->loadHTML($template->get('body_content'));
					$pdf->writeHTML();
				}
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
					$pdf->setPageSize($template->get('page_format'), $template->get('page_orientation'));
					if ($template->get('margin_chkbox') == 0) {
						$pdf->setMargins(
							$template->get('margin_top'), $template->get('margin_right'), $template->get('margin_bottom'), $template->get('margin_left')
						);
					}

					$html = '';

					$pdf->setHeader('Header', $template->get('header_content'));
					$pdf->setFooter('Footer', $template->get('footer_content'));
					$html = $template->get('body_content');

					$pdf->loadHTML($html);
					$pdfFileName = 'storage/' . $recordId . '_' . $template->get('filename') . '_' . $postfix . '.pdf';
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
