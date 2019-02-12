<?php

/**
 * Returns special functions for PDF Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Vtiger_PDF_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'ExportPdf')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('hasValidTemplate');
		$this->exposeMethod('validateRecords');
		$this->exposeMethod('generate');
	}

	public function validateRecords(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$records = $request->getArray('records', 'Integer');
		$templates = $request->getArray('templates', 'Integer');
		$allRecords = count($records);
		$output = ['valid_records' => [], 'message' => \App\Language::translateArgs('LBL_VALID_RECORDS', $moduleName, 0, $allRecords)];

		if (!empty($templates) && count($templates) > 0) {
			foreach ($templates as $templateId) {
				$templateRecord = Vtiger_PDF_Model::getInstanceById((int) $templateId);
				foreach ($records as $recordId) {
					if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) && !$templateRecord->checkFiltersForRecord((int) $recordId) && ($key = array_search($recordId, $records)) !== false) {
						unset($records[$key]);
					}
				}
			}
			$selectedRecords = count($records);
			$output = ['valid_records' => $records, 'message' => \App\Language::translateArgs('LBL_VALID_RECORDS', $moduleName, $selectedRecords, $allRecords)];
		}
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}

	/**
	 * Generate pdf.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function generate(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getArray('record', 'Integer');
		$templateIds = $request->getArray('pdf_template', 'Integer');
		$singlePdf = $request->getInteger('single_pdf') === 1 ? true : false;
		$emailPdf = $request->getInteger('email_pdf') === 1 ? true : false;

		$postfix = time() . '_' . random_int(0, 1000);
		foreach ($recordId as $templateId) {
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $templateId)) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
		$templateAmount = count($templateIds);
		$recordsAmount = count($recordId);
		$selectedOneTemplate = $templateAmount == 1 ? true : false;
		if ($selectedOneTemplate) {
			$template = Vtiger_PDF_Model::getInstanceById($templateIds[0]);
			$generateOnePdf = $template->get('one_pdf');
		}
		if ($selectedOneTemplate && $recordsAmount == 1) {
			if ($emailPdf) {
				$filePath = 'cache' . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $recordId[0] . '_' . time() . '.pdf';
				Vtiger_PDF_Model::exportToPdf($recordId[0], $moduleName, $templateIds[0], ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $filePath, 'F');
				if (file_exists($filePath) && \App\Privilege::isPermitted('OSSMail')) {
					header('location: index.php?module=OSSMail&view=Compose&pdf_path=' . $filePath);
				} else {
					throw new \App\Exceptions\AppException('LBL_EXPORT_ERROR');
				}
			} else {
				Vtiger_PDF_Model::exportToPdf($recordId[0], $moduleName, $templateIds[0]);
			}
		} elseif ($selectedOneTemplate && $recordsAmount > 1 && $generateOnePdf) {
			$pdf = new \App\Pdf\YetiForcePDF();
			$pdf->setTemplateId($templateIds[0]);
			$pdf->setRecordId($recordId[0]);
			$pdf->setModuleName($moduleName);
			$html = '';
			$last = count($recordId) - 1;
			foreach ($recordId as $index => $record) {
				$template = Vtiger_PDF_Model::getInstanceById($templateIds[0]);
				$template->setMainRecordId($record);
				$template->getParameters();
				$currentPage = '<div data-page-group></div>';
				$currentPage .= $pdf->wrapHeaderContent($template->getHeader());
				$currentPage .= $pdf->wrapFooterContent($template->getFooter());
				$currentPage .= $pdf->wrapWatermark($pdf->getTemplateWatermark($template));
				$currentPage .= $template->getBody();
				if ($index !== $last) {
					$currentPage .= '<div style="page-break-after: always;"></div>';
				}
				$pdf->setRecordId($record);
				$html .= $pdf->parseVariables($currentPage);
			}
			$pdf->loadHTML($html);
			$pdf->setFileName(\App\Language::translate('LBL_PDF_MANY_IN_ONE'));
			$pdf->output();
		} else {
			if ($singlePdf) {
				$pdf = new \App\Pdf\YetiForcePDF();
				$html = '';
				foreach ($recordId as $index => $record) {
					$templateIdsTemp = $templateIds;
					foreach ($templateIdsTemp as $templateIndex => $templateId) {
						$template = Vtiger_PDF_Model::getInstanceById($templateId);
						$template->setMainRecordId($record);
						$template->getParameters();
						$pdf->setLanguage($template->get('language'));
						$pdf->setTemplateId($templateId);
						$pdf->setModuleName($moduleName);
						$currentPage = '<div data-page-group 
							data-format="' . $template->getFormat() . '" 
							data-orientation="' . $template->getOrientation() . '"
							data-margin-left="' . $template->get('margin_left') . '"
							data-margin-right="' . $template->get('margin_right') . '"
							data-margin-top="' . $template->get('margin_top') . '"
							data-margin-bottom="' . $template->get('margin_bottom') . '"
							data-header-top="' . $template->get('header_height') . '"
							data-footer-bottom="' . $template->get('footer_height') . '"
							></div>';
						$currentPage .= $pdf->wrapHeaderContent($template->getHeader());
						$currentPage .= $pdf->wrapFooterContent($template->getFooter());
						$currentPage .= $pdf->wrapWatermark($pdf->getTemplateWatermark($template));
						$currentPage .= $template->getBody();
						$currentPage .= '<div style="page-break-after: always;"></div>';
						$pdf->setRecordId($record);
						$html .= $pdf->parseVariables($currentPage);
					}
				}
				$pdf->loadHTML($html);
				$pdf->setFileName(\App\Language::translate('LBL_PDF_MANY_IN_ONE'));
				$pdf->output();
			} else {
				$pdfFiles = [];
				foreach ($templateIds as $templateId) {
					foreach ($recordId as $index => $record) {
						$pdf = new \App\Pdf\YetiForcePDF();
						$pdf->setTemplateId($templateId);
						$pdf->setRecordId($record);
						$pdf->setModuleName($moduleName);
						$template = Vtiger_PDF_Model::getInstanceById($templateId);
						$template->setMainRecordId($record);
						$template->getParameters();
						$currentPage = '<div data-page-group 
							data-format="' . $template->getFormat() . '" 
							data-orientation="' . $template->getOrientation() . '"
							data-margin-left="' . $template->get('margin_left') . '"
							data-margin-right="' . $template->get('margin_right') . '"
							data-margin-top="' . $template->get('margin_top') . '"
							data-margin-bottom="' . $template->get('margin_bottom') . '"
							data-header-top="' . $template->get('header_height') . '"
							data-footer-bottom="' . $template->get('footer_height') . '"
							></div>';
						$currentPage .= $pdf->wrapHeaderContent($template->getHeader());
						$currentPage .= $pdf->wrapFooterContent($template->getFooter());
						$currentPage .= $pdf->wrapWatermark($pdf->getTemplateWatermark($template));
						$currentPage .= $template->getBody();
						$pdf->setRecordId($record);
						$pdf->loadHTML($pdf->parseVariables($currentPage));
						$pdfFileName = 'cache/pdf/' . $record . '_' . $templateId . '_' . $pdf->getFileName() . '_' . $postfix . '.pdf';
						$pdf->output(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $pdfFileName, 'F');
						if (file_exists(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $pdfFileName)) {
							$pdfFiles[] = $pdfFileName;
						}
						unset($pdf, $template);
					}
				}
				if (!empty($pdfFiles)) {
					if (!empty($emailPdf)) {
						Vtiger_PDF_Model::attachToEmail($postfix);
					} else {
						Vtiger_PDF_Model::zipAndDownload($pdfFiles);
					}
				}
			}
		}
		App\Language::clearTemporaryLanguage();
	}

	/**
	 * Checks if given record has valid pdf template.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool true if valid template exists for this record
	 */
	public function hasValidTemplate(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$view = $request->getByType('view');
		if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$pdfModel = new Vtiger_PDF_Model();
		$pdfModel->setMainRecordId($recordId);
		$valid = $pdfModel->checkActiveTemplates($recordId, $moduleName, $view);
		$output = ['valid' => $valid];

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
