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

			// building parameters
			$parameters = [];
			$parameters['page_format'] = $template->get('page_format');
			$parameters['page_orientation'] = $template->get('page_orientation');
			// margins
			if ($template->get('margin_chkbox') == 0) {
				$parameters['margin-top'] = $template->get('margin_top');
				$parameters['margin-right'] = $template->get('margin_right');
				$parameters['margin-bottom'] = $template->get('margin_bottom');
				$parameters['margin-left'] = $template->get('margin_left');
			} else {
				$parameters['margin-top'] = '';
				$parameters['margin-right'] = '';
				$parameters['margin-bottom'] = '';
				$parameters['margin-left'] = '';
			}

			// metadata
			if ($template->get('metatags_status') == 0) {
				$parameters['title'] = $template->get('meta_title');
				$parameters['author'] = $template->get('meta_author');
				$parameters['creator'] = $template->get('meta_creator');
				$parameters['subject'] = $template->get('meta_subject');
				$parameters['keywords'] = $template->get('meta_keywords');
			} else {
				$companyDetails = getCompanyDetails();
				$parameters['title'] = $template->get('primary_name');
				$parameters['author'] = $companyDetails['organizationname'];
				$parameters['creator'] = $companyDetails['organizationname'];
				$parameters['subject'] = $template->get('secondary_name');

				// preparing keywords
				unset($companyDetails['organization_id']);
				unset($companyDetails['logo']);
				unset($companyDetails['logoname']);
				$parameters['keywords'] = implode(', ', $companyDetails);
			}
			$pdf->parseParams($parameters);

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

				// building parameters
				$parameters = [];
				$parameters['page_format'] = $template->get('page_format');
				$parameters['page_orientation'] = $template->get('page_orientation');
				// margins
				if ($template->get('margin_chkbox') == 0) {
					$parameters['margin-top'] = $template->get('margin_top');
					$parameters['margin-right'] = $template->get('margin_right');
					$parameters['margin-bottom'] = $template->get('margin_bottom');
					$parameters['margin-left'] = $template->get('margin_left');
				} else {
					$parameters['margin-top'] = '';
					$parameters['margin-right'] = '';
					$parameters['margin-bottom'] = '';
					$parameters['margin-left'] = '';
				}

				// metadata
				if ($template->get('metatags_status') == 0) {
					$parameters['title'] = $template->get('meta_title');
					$parameters['author'] = $template->get('meta_author');
					$parameters['creator'] = $template->get('meta_creator');
					$parameters['subject'] = $template->get('meta_subject');
					$parameters['keywords'] = $template->get('meta_keywords');
				} else {
					$companyDetails = getCompanyDetails();
					$parameters['title'] = $template->get('primary_name');
					$parameters['author'] = $companyDetails['organizationname'];
					$parameters['creator'] = $companyDetails['organizationname'];
					$parameters['subject'] = $template->get('secondary_name');

					// preparing keywords
					unset($companyDetails['organization_id']);
					unset($companyDetails['logo']);
					unset($companyDetails['logoname']);
					$parameters['keywords'] = implode(', ', $companyDetails);
				}
				$pdf->parseParams($parameters);

				$html = '';

				$pdf->setHeader('Header', $template->get('header_content'));
				$pdf->setFooter('Footer', $template->get('footer_content'));
				$html = $template->get('body_content');

				$pdf->loadHTML($html);
				$pdf->writeHTML();

				foreach ($templateIds as $id) {
					$template = Settings_PDF_Record_Model::getInstanceById($id);

					$pdf->setHeader('Header' . $id, $template->get('header_content'));

					// building parameters
					$parameters = [];
					$parameters['page_format'] = $template->get('page_format');
					$parameters['page_orientation'] = $template->get('page_orientation');
					// margins
					if ($template->get('margin_chkbox') == 0) {
						$parameters['margin-top'] = $template->get('margin_top');
						$parameters['margin-right'] = $template->get('margin_right');
						$parameters['margin-bottom'] = $template->get('margin_bottom');
						$parameters['margin-left'] = $template->get('margin_left');
					} else {
						$parameters['margin-top'] = '';
						$parameters['margin-right'] = '';
						$parameters['margin-bottom'] = '';
						$parameters['margin-left'] = '';
					}

					// metadata
					if ($template->get('metatags_status') == 0) {
						$parameters['title'] = $template->get('meta_title');
						$parameters['author'] = $template->get('meta_author');
						$parameters['creator'] = $template->get('meta_creator');
						$parameters['subject'] = $template->get('meta_subject');
						$parameters['keywords'] = $template->get('meta_keywords');
					} else {
						$companyDetails = getCompanyDetails();
						$parameters['title'] = $template->get('primary_name');
						$parameters['author'] = $companyDetails['organizationname'];
						$parameters['creator'] = $companyDetails['organizationname'];
						$parameters['subject'] = $template->get('secondary_name');

						// preparing keywords
						unset($companyDetails['organization_id']);
						unset($companyDetails['logo']);
						unset($companyDetails['logoname']);
						$parameters['keywords'] = implode(', ', $companyDetails);
					}
					$pdf->parseParams($parameters);

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
					// building parameters
					$parameters = [];
					$parameters['page_format'] = $template->get('page_format');
					$parameters['page_orientation'] = $template->get('page_orientation');
					// margins
					if ($template->get('margin_chkbox') == 0) {
						$parameters['margin-top'] = $template->get('margin_top');
						$parameters['margin-right'] = $template->get('margin_right');
						$parameters['margin-bottom'] = $template->get('margin_bottom');
						$parameters['margin-left'] = $template->get('margin_left');
					} else {
						$parameters['margin-top'] = '';
						$parameters['margin-right'] = '';
						$parameters['margin-bottom'] = '';
						$parameters['margin-left'] = '';
					}

					// metadata
					if ($template->get('metatags_status') == 0) {
						$parameters['title'] = $template->get('meta_title');
						$parameters['author'] = $template->get('meta_author');
						$parameters['creator'] = $template->get('meta_creator');
						$parameters['subject'] = $template->get('meta_subject');
						$parameters['keywords'] = $template->get('meta_keywords');
					} else {
						$companyDetails = getCompanyDetails();
						$parameters['title'] = $template->get('primary_name');
						$parameters['author'] = $companyDetails['organizationname'];
						$parameters['creator'] = $companyDetails['organizationname'];
						$parameters['subject'] = $template->get('secondary_name');

						// preparing keywords
						unset($companyDetails['organization_id']);
						unset($companyDetails['logo']);
						unset($companyDetails['logoname']);
						$parameters['keywords'] = implode(', ', $companyDetails);
					}
					$pdf->parseParams($parameters);

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
