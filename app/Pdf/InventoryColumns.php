<?php
/**
 * InventoryColumns class.
 *
 * @package App\Pdf
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace App\Pdf;

class InventoryColumns
{
	/**
	 * Get column scheme for specified record.
	 *
	 * @param int        $recordId
	 * @param string     $moduleName
	 * @param array|null $columns
	 *
	 * @return array
	 */
	public static function getInventoryColumnsForRecord(int $recordId, string $moduleName, ?array $columns = null)
	{
		$columnsAll = array_keys(\Vtiger_Inventory_Model::getInstance($moduleName)->getFields());
		if (null === $columns) {
			$columnsJSON = (new \App\Db\Query())
				->select(['columns'])
				->from('u_#__pdf_inv_scheme')
				->where(['crmid' => $recordId])
				->scalar();
			$columns = $columnsJSON ? \App\Json::decode($columnsJSON) : $columnsAll;
		}
		return array_intersect($columns, $columnsAll);
	}

	/**
	 * Save column scheme for specified record.
	 *
	 * @param string $moduleName
	 * @param array  $records
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public static function saveInventoryColumnsForRecords(string $moduleName, array $records)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$availableColumns = array_keys(\Vtiger_Inventory_Model::getInstance($moduleName)->getFields());
		foreach ($records as $columns) {
			if (array_diff($columns, $availableColumns)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
		}
		$table = 'u_#__pdf_inv_scheme';
		$insertData = [];
		foreach ($records as $recordId => $columns) {
			$json = \App\Json::encode($columns);
			$schemeExists = (new \App\Db\Query())
				->from($table)
				->where(['crmid' => $recordId])
				->exists();
			if ($schemeExists) {
				$dbCommand->update($table, ['columns' => $json], ['crmid' => $recordId])->execute();
			} else {
				$insertData[] = [$recordId, $json];
			}
		}
		if (!empty($insertData)) {
			$dbCommand->batchInsert($table, ['crmid', 'columns'], $insertData)->execute();
		}
		unset($insertData, $availableColumns, $updateData, $row, $table, $schemeExists);
	}
}
