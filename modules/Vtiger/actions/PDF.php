<?php

/**
 * Returns special functions for PDF Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Adrian Koń <a.kon@yetiforce.com>
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
		$records = $request->getArray('records');
		$templates = $request->get('templates');
		$allRecords = count($records);
		$output = ['valid_records' => [], 'message' => \App\Language::translateArgs('LBL_VALID_RECORDS', $moduleName, 0, $allRecords)];

		if (!empty($templates) && count($templates) > 0) {
			foreach ($templates as $templateId) {
				$templateRecord = Vtiger_PDF_Model::getInstanceById((int) $templateId);
				foreach ($records as $recordId) {
					if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) && !$templateRecord->checkFiltersForRecord((int) $recordId)) {
						if (($key = array_search($recordId, $records)) !== false) {
							unset($records[$key]);
						}
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

	public function generate(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$templateIds = $request->getArray('pdf_template', 'Integer');
		$singlePdf = $request->getInteger('single_pdf') === 1 ? true : false;
		$emailPdf = $request->getInteger('email_pdf') === 1 ? true : false;
		if (!is_array($recordId)) {
			$recordId = [$recordId];
		}
		foreach ($recordId as $id) {
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $id)) {
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
				$filePath = 'cache/pdf/' . $recordId[0] . '_' . time() . '.pdf';
				Vtiger_PDF_Model::exportToPdf($recordId[0], $moduleName, $templateIds[0], $filePath, 'F');
				if (file_exists($filePath) && \App\Privilege::isPermitted('OSSMail')) {
					header('Location: index.php?module=OSSMail&view=Compose&pdf_path=' . $filePath);
				} else {
					throw new \App\Exceptions\AppException('LBL_EXPORT_ERROR');
				}
			} else {
				Vtiger_PDF_Model::exportToPdf($recordId[0], $moduleName, $templateIds[0]);
			}
		} elseif ($selectedOneTemplate && $recordsAmount > 1 && $generateOnePdf) {
			Vtiger_PDF_Model::exportToPdf($recordId, $moduleName, $templateIds[0]);
		} else {
			if ($singlePdf) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Pdf', 'Mpdf', $moduleName);
				$pdf = new $handlerClass();
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
					$template = Vtiger_PDF_Model::getInstanceById($firstTemplate);
					$template->setMainRecordId($record);
					$pdf->setLanguage($template->get('language'));
					App\Language::setTemporaryLanguage($template->get('language'));
					$template->getParameters();

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
						$template = Vtiger_PDF_Model::getInstanceById($id);
						$template->setMainRecordId($record);
						$pdf->setLanguage($template->get('language'));
						App\Language::setTemporaryLanguage($template->get('language'));
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
				$pdf->setFileName(\App\Language::translate('LBL_PDF_MANY_IN_ONE'));
				$pdf->output();
			} else {
				mt_srand(time());
				$postfix = time() . '_' . mt_rand(0, 1000);

				$pdfFiles = [];
				foreach ($templateIds as $id) {
					foreach ($recordId as $record) {
						$handlerClass = Vtiger_Loader::getComponentClassName('Pdf', 'Mpdf', $moduleName);
						$pdf = new $handlerClass();
						$pdf->setTemplateId($id);
						$pdf->setRecordId($record);
						$pdf->setModuleName($moduleName);

						$template = Vtiger_PDF_Model::getInstanceById($id);
						$template->setMainRecordId($record);
						$pdf->setLanguage($template->get('language'));
						App\Language::setTemporaryLanguage($template->get('language'));
						$pdf->setFileName($template->get('filename'));
						$pdf->parseParams($template->getParameters());
						$pdf->setHeader('Header', $template->getHeader());
						$pdf->setFooter('Footer', $template->getFooter());
						$pdf->loadHTML($template->getBody());
						$pdfFileName = 'cache/pdf/' . $record . '_' . $pdf->getFileName() . '_' . $postfix . '.pdf';
						$pdf->output($pdfFileName, 'F');

						if (file_exists($pdfFileName)) {
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
