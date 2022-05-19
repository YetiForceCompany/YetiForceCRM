<?php

/**
 * Export to csv - file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Export to csv - class.
 */
class Vtiger_ExportToCsv_Model extends \App\Export\Records
{
	/** @var string File extension. */
	protected $fileExtension = 'csv';

	/**
	 * Function exports the data based on the mode.
	 *
	 * @return void
	 */
	public function exportData(): void
	{
		$this->output($this->getHeaders(), $this->getEntries());
	}

	/**
	 * Get entires.
	 *
	 * @return array
	 */
	public function getEntries(): array
	{
		$entries = [];
		$addInventoryData = $this->fullData && $this->moduleInstance->isInventory();
		if ($addInventoryData) {
			$inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleName);
			$inventoryFields = $inventoryModel->getFields();
			$inventoryTable = $inventoryModel->getDataTableName();
		}
		$rowsCounter = 0;
		$dataReader = $this->getExportQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$sanitizedRow = $this->sanitizeValues($row);
			if ($addInventoryData) {
				$sanitizedRow[] = $rowsCounter++;
				$rows = (new \App\Db\Query())->from($inventoryTable)->where(['crmid' => $row['id']])->orderBy('seq')->all();
				if ($rows) {
					foreach ($rows as &$row) {
						$sanitizedInventoryRow = $this->sanitizeInventoryValues($row, $inventoryFields);
						$entries[] = array_merge($sanitizedRow, $sanitizedInventoryRow);
					}
				} else {
					$entries[] = $sanitizedRow;
				}
			} else {
				$entries[] = $sanitizedRow;
			}
		}
		$dataReader->close();
		return $entries;
	}

	/**
	 * Function that create the exported file.
	 *
	 * @param array $headers - output file header
	 * @param array $entries - output file data
	 */
	public function output(array $headers, array $entries)
	{
		$output = fopen('php://output', 'w');
		fputcsv($output, $headers);
		foreach ($entries as $row) {
			fputcsv($output, $row);
		}
		fclose($output);
	}
}
