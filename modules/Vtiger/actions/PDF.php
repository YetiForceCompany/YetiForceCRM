<?php

/**
 * Export PDF action file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Export PDF action class.
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
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'ExportPdf')) {
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
	 *
	 * @return void
	 */
	public function validateRecords(App\Request $request): void
	{
		$moduleName = $request->getModule();
		$recordId = $request->isEmpty('record', true) ? null : $request->getInteger('record');
		$result = false;
		foreach ($request->getArray('templates', \App\Purifier::INTEGER) as $templateId) {
			$templateRecord = Vtiger_PDF_Model::getInstanceById($templateId);
			foreach ($this->getRecords($request) as $recordId) {
				if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) && $templateRecord->checkFiltersForRecord((int) $recordId)) {
					$result = true;
					break 2;
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'valid' => $result,
			'message' => \App\Language::translateArgs('LBL_NO_DATA_AVAILABLE', $moduleName),
		]);
		$response->emit();
	}

	/**
	 * Generate pdf.
	 *
	 * @param App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return void
	 */
	public function generate(App\Request $request): void
	{
		$pdfModuleName = $request->getModule();
		$view = $request->getByType('fromview', \App\Purifier::STANDARD);
		$recordId = $request->isEmpty('record', true) ? null : $request->getInteger('record');
		if ($isRelatedView = ('RelatedList' === $view)) {
			$pdfModuleName = $request->getByType('relatedModule', \App\Purifier::ALNUM);
		}
		$recordIds = $this->getRecords($request);

		$templateIds = $request->getArray('pdf_template', 'Integer');
		$singlePdf = 1 === $request->getInteger('single_pdf');
		$emailPdf = 1 === $request->getInteger('email_pdf');
		$pdfFlag = $request->getByType('flag', \App\Purifier::STANDARD) ?: null;
		$key = 'inventoryColumns';
		$userVariables = $request->getArray('userVariables', \App\Purifier::TEXT);

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $pdfModuleName);
		$pdfModel = new $handlerClass();
		$templates = ($recordId && !$isRelatedView) ? $pdfModel->getActiveTemplatesForRecord($recordId, $view, $pdfModuleName) : $pdfModel->getActiveTemplatesForModule($pdfModuleName, $view);

		if (($emailPdf && !\App\Mail::checkInternalMailClient())
			|| ($request->has($key) && !\App\Privilege::isPermitted($pdfModuleName, 'RecordPdfInventory'))
			|| array_diff($templateIds, array_keys($templates))
			) {
			throw new \App\Exceptions\NoPermitted('LBL_EXPORT_ERROR');
		}
		$eventHandler = new App\EventHandler();
		$eventHandler->setModuleName($pdfModuleName);
		$eventHandler->setParams([
			'records' => $recordIds,
			'templates' => $templateIds,
			'viewInstance' => $this,
			'pdfModel' => $pdfModel,
		]);
		$eventHandler->trigger('PdfGenerateInit');
		$recordIds = $eventHandler->getParam('records');

		$increment = $skip = $pdfFiles = [];
		$countTemplates = \count($templateIds);
		$countRecords = \count($recordIds);
		foreach ($recordIds as $recordId) {
			foreach ($templateIds as $templateId) {
				if (isset($skip[$templateId])) {
					continue;
				}
				$pdf = \App\Pdf\Pdf::getInstanceByTemplateId($templateId);
				$template = $pdf->getTemplate();
				$filePath = '';
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
						foreach (['viewname', 'search_value', 'search_key', 'search_params', 'operator', 'cvId', 'relatedModule', 'relationId'] as $keyName) {
							if ('search_params' === $keyName) {
								$template->setVariable($keyName, App\Condition::validSearchParams($pdfModuleName, $request->getArray($keyName)));
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

				$pdf->loadTemplateData();
				$eventHandler->addParams('pdf', $pdf);
				$eventHandler->trigger('PdfGenerate');

				$attach = $template->attachFiles ?? [];
				if ($attach || $emailPdf || ($countTemplates > 1 || (1 === $countTemplates && !isset($skip[$templateId]) && $countRecords > 1))) {
					$fileName = ($pdf->getFileName() ?: time());
					$increment[$fileName] = $increment[$fileName] ?? 0;
					$fileName .= ($increment[$fileName]++ > 0 ? '_' . $increment[$fileName] : '') . '.pdf';

					$filePath = $template->getPath();
					$mode = 'F';
					$pdfFiles[] = ['name' => $fileName, 'path' => $filePath, 'recordId' => $recordId, 'moduleName' => $pdfModuleName];
					foreach ($attach as $info) {
						if (!isset($pdfFiles[$info['path']])) {
							$tmpFileName = 'cache' . \DIRECTORY_SEPARATOR . 'pdf' . \DIRECTORY_SEPARATOR;
							$tmpFileName = $tmpFileName . basename(tempnam($tmpFileName, 'Attach' . time()));
							if (\copy($info['path'], $tmpFileName)) {
								$pdfFiles[$info['path']] = ['name' => $info['name'], 'path' => $tmpFileName, 'recordId' => $recordId, 'moduleName' => $pdfModuleName];
							}
						}
					}
				}
				$pdf->output($filePath, $mode ?? $pdfFlag ?? 'D');
			}
		}
		if ($singlePdf) {
			\App\Pdf\Pdf::merge(array_column($pdfFiles, 'path'), \App\Fields\File::sanitizeUploadFileName(\App\Language::translate('LBL_PDF_MANY_IN_ONE')) . '.pdf', $pdfFlag ?: 'D');
			foreach ($pdfFiles as $pdfFile) {
				unlink($pdfFile['path']);
			}
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
	 * @return void
	 */
	public function hasValidTemplate(App\Request $request): void
	{
		$recordId = $request->isEmpty('record', true) ? null : $request->getInteger('record');
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$response = new Vtiger_Response();
		$response->setResult(['valid' => (new Vtiger_PDF_Model())->checkActiveTemplates($recordId, $moduleName, $request->getByType('view'))]);
		$response->emit();
	}

	/**
	 * Save inventory column scheme.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function saveInventoryColumnScheme(App\Request $request): void
	{
		$moduleName = $request->has('relatedModule') ? $request->getByType('relatedModule', \App\Purifier::ALNUM) : $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'RecordPdfInventory')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$records = $this->getRecords($request);
		$columns = $request->getArray('inventoryColumns');
		$save = [];
		foreach ($records as $recordId) {
			$save[$recordId] = $columns;
		}
		\App\Pdf\InventoryColumns::saveInventoryColumnsForRecords($moduleName, $save);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_SCHEME_SAVED', 'Settings:PDF'),
			'records' => $records,
		]);
		$response->emit();
	}

	/**
	 * Get record ids.
	 *
	 * @param \App\Request $request
	 *
	 * @return int[]
	 */
	private function getRecords(App\Request $request): array
	{
		if ($request->has('relatedModule')) {
			$records = \Vtiger_RelationAjax_Action::getRecordsListFromRequest($request);
		} elseif (!$request->isEmpty('record', true)) {
			$records = [$request->getInteger('record')];
		} else {
			$records = \Vtiger_Mass_Action::getRecordsListFromRequest($request);
		}
		return $records;
	}

	/** {@inheritdoc} */
	public function validateRequest(App\Request $request)
	{
		if ('generate' === $request->getMode()) {
			$request->validateReadAccess();
		} else {
			$request->validateWriteAccess();
		}
	}
}
