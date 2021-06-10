<?php

/**
 * Export to csv class.
 *
 * @package Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Class ExportRecords.
 */
class Vtiger_ExportToCsv_Model extends \App\Export\ExportRecords
{
	/** @var string File extension. */
	protected $fileExtension = 'csv';

	/**
	 * Function exports the data based on the mode.
	 */
	public function exportData()
	{
		$entries = [];
		if (!$this->exportColumns && $this->quickExport && $this->queryOptions['viewname']) {
			$headers = $this->getHeaders();
			$listViewModel = Vtiger_ListView_Model::getInstance($this->moduleName, $this->queryOptions['viewname']);
			$pagingModel = (new \Vtiger_Paging_Model())->set('limit', Vtiger_Paging_Model::PAGE_MAX_LIMIT);
			$listViewModel->set('query_generator', $this->queryGeneratorForList);
			foreach ($listViewModel->getListViewEntries($pagingModel) as $record) {
				$recordValues = [];
				foreach ($this->listViewHeaders as $fieldModel) {
					$displayValue = $record->getDisplayValue($fieldModel->getName(), $record->getId(), true, false);
					$displayValue = strip_tags($displayValue);
					$recordValues[] = $displayValue;
				}
				$entries[] = $recordValues;
			}
		} else {
			$query = $this->getExportQuery();
			$headers = $this->getHeaders();
			$isInventory = $this->moduleInstance->isInventory();
			if ($isInventory) {
				$inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleName);
				$inventoryFields = $inventoryModel->getFields();
				$inventoryTable = $inventoryModel->getDataTableName();
			}
			$rowsCounter = 0;
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$sanitizedRow = $this->sanitizeValues($row);
				if ($isInventory) {
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
		}
		$this->output($headers, $entries);
	}

	/**
	 * Function that create the exported file.
	 *
	 * @param array $headers - output file header
	 * @param array $entries - outfput file data
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
