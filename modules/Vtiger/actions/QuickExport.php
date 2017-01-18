<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Vtiger_QuickExport_Action extends Vtiger_Mass_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'QuickExportToExcel')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function __construct()
	{
		$this->exposeMethod('ExportToExcel');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();

		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	public function ExportToExcel(Vtiger_Request $request)
	{
		vimport('libraries.PHPExcel.PHPExcel');
		$module = $request->getModule(false); //this is the type of things in the current view
		$filter = $request->get('viewname'); //this is the cvid of the current custom filter
		$recordIds = $this->getRecordsListFromRequest($request); //this handles the 'all' situation.
		//set up our spreadsheet to write out to
		$workbook = new PHPExcel();
		$worksheet = $workbook->setActiveSheetIndex(0);
		$header_styles = [
			'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'E1E0F7']],
			'font' => ['bold' => true]
		];
		$row = 1;
		$col = 0;

		$queryGenerator = new \App\QueryGenerator($module);
		$queryGenerator->initForCustomViewById($filter);
		$headers = $queryGenerator->getListViewFields();
		$customView = CustomView_Record_Model::getInstanceById($filter);
		//get the column headers, they go in row 0 of the spreadsheet
		foreach ($headers as &$fieldsModel) {
			$worksheet->setCellValueExplicitByColumnAndRow($col, $row, decode_html(App\Language::translate($fieldsModel->getFieldLabel(), $module)), PHPExcel_Cell_DataType::TYPE_STRING);
			$col++;
		}
		$row++;

		$targetModuleFocus = CRMEntity::getInstance($module);
		//ListViewController has lots of paging stuff and things we don't want
		//so lets just itterate across the list of IDs we have and get the field values
		foreach ($recordIds as $id) {
			$col = 0;
			$record = Vtiger_Record_Model::getInstanceById($id, $module);
			foreach ($headers as &$fieldsModel) {
				//depending on the uitype we might want the raw value, the display value or something else.
				//we might also want the display value sans-links so we can use strip_tags for that
				//phone numbers need to be explicit strings
				$value = $record->getDisplayValue($fieldsModel->getFieldName());
				switch ($fieldsModel->getUIType()) {
					case 25:
					case 7:
						if ($fieldsModel->getFieldName() === 'sum_time') {
							$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, strip_tags($value), PHPExcel_Cell_DataType::TYPE_STRING);
						} else {
							$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, strip_tags($value), PHPExcel_Cell_DataType::TYPE_NUMERIC);
						}
						break;
					case 71:
					case 72:
						$rawValue = $record->get($fieldsModel->getFieldName());
						$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, strip_tags($rawValue), PHPExcel_Cell_DataType::TYPE_NUMERIC);
						break;
					case 6://datetimes
					case 23:
					case 70:
						$worksheet->setCellvalueExplicitByColumnAndRow($col, $row, PHPExcel_Shared_Date::PHPToExcel(strtotime($record->get($fieldsModel->getFieldName()))), PHPExcel_Cell_DataType::TYPE_NUMERIC);
						$worksheet->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY HH:MM:SS'); //format the date to the users preference
						break;
					default:
						$worksheet->setCellValueExplicitByColumnAndRow($col, $row, decode_html(strip_tags($value)), PHPExcel_Cell_DataType::TYPE_STRING);
				}
				$col++;
			}
			$row++;
		}

		//having written out all the data lets have a go at getting the columns to auto-size
		$col = 0;
		$row = 1;
		foreach ($headers as &$fieldsModel) {
			$cell = $worksheet->getCellByColumnAndRow($col, $row);
			$worksheet->getStyleByColumnAndRow($col, $row)->applyFromArray($header_styles);
			$worksheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
			$col++;
		}

		$tmpDir = vglobal('tmp_dir');
		$tempFileName = tempnam(ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $tmpDir, 'xls');
		$workbookWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel5');
		$workbookWriter->save($tempFileName);

		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			header('Pragma: public');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		}

		header('Content-Type: application/x-msexcel');
		header('Content-Length: ' . @filesize($tempFileName));
		$filename = vtranslate($module, $module) . '-' . vtranslate(decode_html($customView->get('viewname')), $module) . ".xls";
		header("Content-Disposition: attachment; filename=\"$filename\"");

		$fp = fopen($tempFileName, 'rb');
		fpassthru($fp);
		fclose($fp);
		unlink($tempFileName);
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
