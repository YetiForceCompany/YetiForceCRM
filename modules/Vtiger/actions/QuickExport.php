<?php

/**
 * Vtiger QuickExport action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_QuickExport_Action extends Vtiger_Mass_Action
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
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'QuickExportToExcel')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('exportToExcel');
	}

	/**
	 * Function export list fields to excel.
	 *
	 * @param \App\Request $request
	 */
	public function exportToExcel(App\Request $request)
	{
		$userModel = \App\User::getCurrentUserModel();
		$moduleName = $request->getModule(false);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$filter = $request->getByType('viewname', 2);
		$workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$worksheet = $workbook->setActiveSheetIndex(0);
		$header_styles = [
			'fill' => ['type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'E1E0F7']],
			'font' => ['bold' => true],
		];
		$col = $row = 1;
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $filter);
		$customView = CustomView_Record_Model::getInstanceById($filter);
		$queryGenerator = self::getQuery($request);
		$listViewModel->set('query_generator', $queryGenerator);
		$pagingModel = (new \Vtiger_Paging_Model())->set('limit', Vtiger_Paging_Model::PAGE_MAX_LIMIT);
		$headers = $listViewModel->getListViewHeaders();
		foreach ($headers as $fieldModel) {
			$label = $fieldModel->getFullLabelTranslation($moduleModel);
			$worksheet->setCellValueExplicitByColumnAndRow($col, $row, App\Purifier::decodeHtml($label), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			++$col;
		}
		++$row;
		foreach ($listViewModel->getListViewEntries($pagingModel) as $record) {
			$col = 1;
			if (!$record->isViewable()) {
				continue;
			}
			foreach ($headers as $fieldModel) {
				//depending on the uitype we might want the raw value, the display value or something else.
				//we might also want the display value sans-links so we can use strip_tags for that
				//phone numbers need to be explicit strings
				switch ($fieldModel->getFieldDataType()) {
					case 'integer':
						$value = $record->getValueByFieldModel($fieldModel);
						$worksheet->setCellValueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
						$worksheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
						break;
					case 'currencyInventory':
						$value = number_format((float) $record->getValueByFieldModel($fieldModel), $userModel->getDetail('no_of_currency_decimals'), '.', '');
						if ($fieldModel->get('source_field_name')) {
							$recordRel = $record->ext[$fieldModel->get('source_field_name')][$fieldModel->getModuleName()] ?? null;
							$currencyId = $recordRel ? (current($record->getInventoryData())['currency'] ?? null) : null;
						} else {
							$currencyId = current($record->getInventoryData())['currency'] ?? null;
						}
						$type = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00;
						if ($currencyId && ($currencySymbol = \App\Fields\Currency::getById($currencyId)['currency_symbol'] ?? '')) {
							$currencySymbolPlacement = $userModel->getDetail('currency_symbol_placement');
							if ('1.0$' === $currencySymbolPlacement) {
								$type = "#,##0.00_-\"{$currencySymbol}\"";
							} else {
								$type = "\"{$currencySymbol}\"#,##0.00_-";
							}
						}
						if (is_numeric($value)) {
							$worksheet->setCellValueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
							$worksheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode($type);
						} else {
							$worksheet->setCellValueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
						}
						break;
					case 'double':
					case 'currency':
						$value = $record->getValueByFieldModel($fieldModel);
						if (is_numeric($value)) {
							$worksheet->setCellValueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
							$worksheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
						} else {
							$worksheet->setCellValueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
						}
						break;
					case 'date':
						$value = $record->getValueByFieldModel($fieldModel);
						if ($value) {
							$worksheet->setCellValueExplicitByColumnAndRow($col, $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
							$worksheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
						} else {
							$worksheet->setCellValueExplicitByColumnAndRow($col, $row, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
						}
						break;
					case 'datetime':
						$value = $record->getValueByFieldModel($fieldModel);
						if ($value) {
							$worksheet->setCellValueExplicitByColumnAndRow($col, $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
							$worksheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM');
						} else {
							$worksheet->setCellValueExplicitByColumnAndRow($col, $row, '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
						}
						break;
					default:
						$value = $record->getListViewDisplayValue($fieldModel, true);
						$worksheet->setCellValueExplicitByColumnAndRow($col, $row, App\Purifier::decodeHtml($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				}
				++$col;
			}
			++$row;
		}
		//having written out all the data lets have a go at getting the columns to auto-size
		$row = $col = 1;
		foreach ($headers as $fieldModel) {
			$cell = $worksheet->getCellByColumnAndRow($col, $row);
			$worksheet->getStyleByColumnAndRow($col, $row)->applyFromArray($header_styles);
			$worksheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
			++$col;
		}
		$tempFileName = tempnam(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . \App\Config::main('tmp_dir'), 'xls');
		$workbookWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($workbook, 'Xls');
		$workbookWriter->save($tempFileName);

		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			header('pragma: public');
			header('cache-control: must-revalidate, post-check=0, pre-check=0');
		}
		header('content-type: application/x-msexcel');
		header('content-length: ' . filesize($tempFileName));
		$filename = \App\Language::translate($moduleName, $moduleName) . '-' . \App\Language::translate(App\Purifier::decodeHtml($customView->get('viewname')), $moduleName) . '.xls';
		header("content-disposition: attachment; filename=\"$filename\"");

		$fp = fopen($tempFileName, 'r');
		fpassthru($fp);
		fclose($fp);
		unlink($tempFileName);
	}
}
