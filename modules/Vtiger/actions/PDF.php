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

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('hasValidTemplate');
		$this->exposeMethod('validateRecords');
		$this->exposeMethod('generate');
		$this->exposeMethod('saveInventoryColumnScheme');
	}

	/**
	 * Function to validate date.
	 *
	 * @param App\Request $request
	 */
	public function validateRecords(App\Request $request)
	{
		$moduleName = $request->getModule();
		$templates = $request->getArray('templates', \App\Purifier::INTEGER);
		$recordId = $request->getInteger('record');
		$records = $recordId ? [$recordId] : \Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$result = false;
		foreach ($templates as $templateId) {
			$templateRecord = Vtiger_PDF_Model::getInstanceById($templateId);
			foreach ($records as $recordId) {
				if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) && $templateRecord->checkFiltersForRecord((int) $recordId)) {
					$result = true;
					break 2;
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(['valid' => $result, 'message' => \App\Language::translateArgs('LBL_NO_DATA_AVAILABLE', $moduleName)]);
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
		$recordId = $request->getInteger('record');
		$recordIds = $recordId ? [$recordId] : \Vtiger_Mass_Action::getRecordsListFromRequest($request);

		$templateIds = $request->getArray('pdf_template', 'Integer');
		$singlePdf = 1 === $request->getInteger('single_pdf');
		$emailPdf = 1 === $request->getInteger('email_pdf');
		$pdfFlag = $request->getByType('flag', \App\Purifier::STANDARD) ?: '';
		$view = $request->getByType('fromview', \App\Purifier::STANDARD);
		$key = 'inventoryColumns';
		$userVariables = $request->getArray('userVariables', \App\Purifier::TEXT);

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
		$pdfModel = new $handlerClass();
		$templates = $recordId ? $pdfModel->getActiveTemplatesForRecord($recordId, $view, $moduleName) : $pdfModel->getActiveTemplatesForModule($moduleName, $view);

		if (($emailPdf && !\App\Privilege::isPermitted('OSSMail'))
			|| ($request->has($key) && !\App\Privilege::isPermitted($moduleName, 'RecordPdfInventory'))
			|| array_diff($templateIds, array_keys($templates))
			) {
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
						$skip[$templateId] = true;
						$validRecords = [];
						foreach ($recordIds as $record) {
							if ($template->checkFiltersForRecord($record)) {
								$validRecords[] = $record;
							}
						}
						$template->setVariable('recordsId', $validRecords);
						foreach (['viewname', 'search_value', 'search_key', 'search_params', 'operator'] as $keyName) {
							if ('search_params' === $keyName) {
								$template->setVariable($keyName, App\Condition::validSearchParams($moduleName, $request->getArray($keyName)));
							} else {
								$template->setVariable($keyName, $request->isEmpty($keyName) ? '' : $request->getByType($keyName, \App\Purifier::ALNUM));
							}
						}
						break;
					case Vtiger_PDF_Model::TEMPLATE_TYPE_DYNAMIC:
						if (!$template->checkFiltersForRecord($recordId)) {
							break 2;
						}
						$template->setVariable('recordId', $recordId);
						$template->setVariable($key, $request->getArray($key, 'Alnum', null));
						break;
					default:
						if (!$template->checkFiltersForRecord($recordId)) {
							break 2;
						}
						$template->setVariable('recordId', $recordId);
						break;
				}

				if (isset($userVariables[$template->getId()])) {
					foreach ($userVariables[$template->getId()] as $key => $value) {
						$template->getParser()->setParam($key, $value);
					}
				}

				$pdf->setPageSize($template->getFormat(), $template->getOrientation())
					->setWatermark($watermark = $pdf->getTemplateWatermark($template))
					->setFileName($template->parseVariables($template->get('filename')))
					->parseParams($template->getParameters())
					->loadHtml($template->parseVariables($template->getBody()))
					->setHeader($template->parseVariables($template->getHeader()))
					->setFooter($template->parseVariables($template->getFooter()));
				$attach = $template->attachFiles ?? [];
				if (!$singlePdf && ($attach || $emailPdf || ($countTemplates > 1 || (1 === $countTemplates && !isset($skip[$templateId]) && $countRecords > 1)))) {
					$fileName = ($pdf->getFileName() ?: time());
					$increment[$fileName] = $increment[$fileName] ?? 0;
					$fileName .= ($increment[$fileName]++ > 0 ? '_' . $increment[$fileName] : '') . '.pdf';

					$filePath = $template->getPath();
					$saveFlag = 'F';
					$pdfFiles[] = ['path' => $filePath,	'name' => $fileName];
					foreach ($attach as $info) {
						if (!isset($pdfFiles[$info['path']])) {
							$tmpFileName = 'cache' . \DIRECTORY_SEPARATOR . 'pdf' . \DIRECTORY_SEPARATOR;
							$tmpFileName = $tmpFileName . basename(tempnam($tmpFileName, 'Attach' . time()));
							if (\copy($info['path'], $tmpFileName)) {
								$pdfFiles[$info['path']] = ['name' => $info['name'], 'path' => $tmpFileName];
							}
						}
					}
				}
				if ($singlePdf) {
					$html = $html ? substr_replace($html, '<div style="page-break-after: always;">', -6, 0) : $html;
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
					$html .= $pdf->getHtml() . '</div>';
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
			$pdf->output('', $pdfFlag);
		} elseif ($emailPdf) {
			$pdfFiles = array_values($pdfFiles);
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
		$recordId = $request->getInteger('record');
		$records = $recordId ? [$recordId] : \Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$columns = $request->getArray('inventoryColumns');
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
