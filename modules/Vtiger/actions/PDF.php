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
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	public function checkPermission(App\Request $request)
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
		$this->exposeMethod('saveInventoryColumnScheme');
	}

	public function validateRecords(App\Request $request)
	{
		$moduleName = $request->getModule();
		$records = $request->getArray('records', 'Integer');
		$templates = $request->getArray('templates', 'Integer');
		$allRecords = \count($records);
		$output = ['valid_records' => [], 'message' => \App\Language::translateArgs('LBL_VALID_RECORDS', $moduleName, 0, $allRecords)];

		if (!empty($templates) && \count($templates) > 0) {
			foreach ($templates as $templateId) {
				$templateRecord = Vtiger_PDF_Model::getInstanceById((int) $templateId);
				foreach ($records as $recordId) {
					if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) && !$templateRecord->checkFiltersForRecord((int) $recordId) && false !== ($key = array_search($recordId, $records))) {
						unset($records[$key]);
					}
				}
			}
			$selectedRecords = \count($records);
			$output = ['valid_records' => $records, 'message' => \App\Language::translateArgs('LBL_VALID_RECORDS', $moduleName, $selectedRecords, $allRecords)];
		}
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}

	/**
	 * Generate pdf.
	 *
	 * @param App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function generate(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordIds = $request->getArray('record', 'Integer');
		$templateIds = $request->getArray('pdf_template', 'Integer');
		$singlePdf = 1 === $request->getInteger('single_pdf');
		$emailPdf = 1 === $request->getInteger('email_pdf');
		$key = 'inventoryColumns';
		if (($emailPdf && !\App\Privilege::isPermitted('OSSMail')) || ($request->has($key) && !\App\Privilege::isPermitted($moduleName, 'RecordPdfInventory'))) {
			throw new \App\Exceptions\NoPermitted('LBL_EXPORT_ERROR');
		}
		$increment = $skip = $pdfFiles = [];
		$html = '';
		$countTemplates = \count($templateIds);
		$countRecords = \count($recordIds);
		$pdf = new \App\Pdf\YetiForcePDF();
		foreach ($recordIds as $recordId) {
			foreach ($templateIds as $templateId) {
				if (isset($skip[$templateId])) {
					continue;
				}
				$filePath = $saveFlag = '';
				$template = Vtiger_PDF_Model::getInstanceById($templateId);
				switch ($template->get('type')) {
					case Vtiger_PDF_Model::TEMPLATE_TYPE_SUMMARY:
						$skip[$templateId] = $recordIds;
						$template->setVariable('recordsId', $recordIds);
						break;
					case Vtiger_PDF_Model::TEMPLATE_TYPE_DYNAMIC:
						$template->setVariable('recordId', $recordId);
						$template->setVariable($key, $request->getArray($key, 'Alnum', null));
						break;
					default:
						$template->setVariable('recordId', $recordId);
						break;
				}

				$pdf->setPageSize($template->getFormat(), $template->getOrientation())
					->setWatermark($watermark = $pdf->getTemplateWatermark($template))
					->setFileName($template->parseVariables($template->get('filename')))
					->parseParams($template->getParameters())
					->loadHtml($template->parseVariables($template->getBody()))
					->setHeader($template->parseVariables($template->getHeader()))
					->setFooter($template->parseVariables($template->getFooter()));

				if ($emailPdf || ($countTemplates > 1 || (1 === $countTemplates && !isset($skip[$templateId]) && $countRecords > 1))) {
					$fileName = ($pdf->getFileName() ? $pdf->getFileName() : time());
					$increment[$fileName] = $increment[$fileName] ?? 0;
					$fileName .= ($increment[$fileName]++ > 0 ? '_' . $increment[$fileName] : '') . '.pdf';

					$filePath = $template->getPath();
					$saveFlag = 'F';
					$pdfFiles[] = ['path' => $filePath,	'name' => $fileName];
				}
				if ($singlePdf) {
					$html .= '<div data-page-group
					data-format="' . $template->getFormat() . '"
					data-orientation="' . $template->getOrientation() . '"
					data-margin-left="' . $pdf->defaultMargins['left'] . '"
					data-margin-right="' . $pdf->defaultMargins['right'] . '"
					data-margin-top="' . $pdf->defaultMargins['top'] . '"
					data-margin-bottom="' . $pdf->defaultMargins['bottom'] . '"
					data-header-top="' . $pdf->getHeaderMargin() . '"
					data-footer-bottom="' . $pdf->getFooterMargin() . '"
					>' . $watermark ? "<div data-watermark style=\"text-align:center\">{$watermark}</div>" : '' . '</div>';
					$html .= $pdf->getHtml() . '<div style="page-break-after: always;"></div>';
				} else {
					$pdf->output($filePath, $saveFlag);
					if ($increment) {
						$pdf = new \App\Pdf\YetiForcePDF();
					}
				}
			}
		}
		if ($singlePdf) {
			$pdf->setHeader('')->setFooter('')->setWatermark('');
			$pdf->loadHTML($html);
			$pdf->setFileName(\App\Language::translate('LBL_PDF_MANY_IN_ONE'));
			$pdf->output();
		} elseif ($emailPdf) {
			Vtiger_PDF_Model::attachToEmail(\App\Json::encode($pdfFiles));
		} elseif ($pdfFiles) {
			Vtiger_PDF_Model::zipAndDownload($pdfFiles);
		}
	}

	/**
	 * Checks if given record has valid pdf template.
	 *
	 * @param \App\Request $request
	 *
	 * @return bool true if valid template exists for this record
	 */
	public function hasValidTemplate(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$view = $request->getByType('view');
		if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$pdfModel = new Vtiger_PDF_Model();
		$valid = $pdfModel->checkActiveTemplates($recordId, $moduleName, $view);
		$output = ['valid' => $valid];

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}

	/**
	 * Save inventory column scheme.
	 *
	 * @param App\Request $request
	 */
	public function saveInventoryColumnScheme(App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'RecordPdfInventory')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$records = $request->getArray('records', 'Integer');
		$columns = $request->getArray('inventoryColumns', 'String');
		$save = [];
		foreach ($records as $recordId) {
			$save[$recordId] = $columns;
		}
		\App\Pdf\InventoryColumns::saveInventoryColumnsForRecords($moduleName, $save);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_SCHEME_SAVED', 'Settings:PDF'),
			'records' => $records
		]);
		$response->emit();
	}
}
