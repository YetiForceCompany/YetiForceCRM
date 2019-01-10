<?php

/**
 * Vtiger QuickExport action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'QuickExportToExcel')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function __construct()
	{
		$this->exposeMethod('exportToExcel');
	}

	public function exportToExcel(\App\Request $request)
	{
		$moduleName = $request->getModule(false); //this is the type of things in the current view
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$filter = $request->getByType('viewname', 2); //this is the cvid of the current custom filter
		//set up our spreadsheet to write out to
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
		$queryGenerator->initForCustomViewById($filter, true);
		$headers = $listViewModel->getListViewHeaders();
		foreach ($headers as $fieldModel) {
			$label = App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
			if (!empty($fieldModel->get('source_field_name'))) {
				$label = App\Language::translate($moduleModel->getField($fieldModel->get('source_field_name'))->getFieldLabel(), $moduleName) . ' - ' . $label;
			}
			$worksheet->setCellValueExplicitByColumnAndRow($col, $row, App\Purifier::decodeHtml($label), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			++$col;
		}
		++$row;
		//ListViewController has lots of paging stuff and things we don't want
		//so lets just itterate across the list of IDs we have and get the field values
		foreach ($listViewModel->getRecordsFromArray($queryGenerator->createQuery()->all()) as $record) {
			$col = 1;
			if (!$record->isViewable()) {
				continue;
			}
			foreach ($headers as $fieldModel) {
				//depending on the uitype we might want the raw value, the display value or something else.
				//we might also want the display value sans-links so we can use strip_tags for that
				//phone numbers need to be explicit strings
				$value = $record->getListViewDisplayValue($fieldModel, true);
				switch ($fieldModel->getUIType()) {
					case 25:
					case 7:
						if ($fieldModel->getFieldName() === 'sum_time') {
							$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
						} else {
							$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
						}
						break;
					case 71:
					case 72:
						if (!empty($fieldModel->get('source_field_name')) && isset($record->ext[$fieldModel->get('source_field_name')][$fieldModel->getModuleName()])) {
							$value = $record->ext[$fieldModel->get('source_field_name')][$fieldModel->getModuleName()]->get($fieldModel->getFieldName());
						} else {
							$value = $record->get($fieldModel->getFieldName());
						}
						$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
						break;
					case 6://datetimes
					case 23:
					case 70:
						if (!empty($fieldModel->get('source_field_name')) && isset($record->ext[$fieldModel->get('source_field_name')][$fieldModel->getModuleName()])) {
							$value = $record->ext[$fieldModel->get('source_field_name')][$fieldModel->getModuleName()]->get($fieldModel->getFieldName());
						} else {
							$value = $record->get($fieldModel->getFieldName());
						}
						$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
						if ($moduleName === 'Reservations' || $moduleName === 'OSSTimeControl' || $moduleName === 'Calendar') {
							$worksheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY'); //format the date to the users preference
						} else {
							$worksheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM:SS'); //format the date to the users preference
						}
						break;
					default:
						$worksheet->setCellValueExplicitByColumnAndRow($col, $row, App\Purifier::decodeHtml($value), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				}
				++$col;
			}
			++$row;
		}
		//having written out all the data lets have a go at getting the columns to auto-size
		$row = $col = 0;
		foreach ($headers as &$fieldModel) {
			$cell = $worksheet->getCellByColumnAndRow($col, $row);
			$worksheet->getStyleByColumnAndRow($col, $row)->applyFromArray($header_styles);
			$worksheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
			++$col;
		}
		$tempFileName = tempnam(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . \AppConfig::main('tmp_dir'), 'xls');
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

		$fp = fopen($tempFileName, 'rb');
		fpassthru($fp);
		fclose($fp);
		unlink($tempFileName);
	}
}
