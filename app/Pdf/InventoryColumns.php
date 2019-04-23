<?php
/**
 * InventoryColumns class.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace App\Pdf;

class InventoryColumns
{
	/**
	 * Inventory columns.
	 *
	 * @var array
	 */
	public static $inventoryColumns = [];
	/**
	 * Is custom mode
	 * Columns are from request ? or from db configuration?
	 *
	 * @var bool
	 */
	public static $isCustomMode = false;

	/**
	 * Get column scheme for specified record.
	 *
	 * @param int    $recordId
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getInventoryColumnsForRecord($recordId, string $moduleName)
	{
		if (static::$isCustomMode && !empty(static::$inventoryColumns)) {
			return static::$inventoryColumns;
		}
		$columnsJSON = (new \App\Db\Query())
			->select(['columns'])
			->from('u_#__pdf_inv_scheme')
			->where(['crmid' => $recordId])
			->scalar();
		if ($columnsJSON) {
			return \App\Json::decode($columnsJSON);
		}
		return array_keys(Vtiger_Inventory_Model::getInstance($moduleName)->getFields());
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
		$availableColumns = array_keys(Vtiger_Inventory_Model::getInstance($moduleName)->getFields());
		foreach ($records as $columns) {
			if (array_diff($columns, $availableColumns)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE', 406);
			}
		}
		$table = 'u_#__pdf_inv_scheme';
		$insertData = [];
		$updateData = [];
		foreach ($records as $recordId => $columns) {
			$json = \App\Json::encode($columns);
			$schemeExists = (new \App\Db\Query())
				->select(['crmid'])
				->from($table)
				->where(['crmid' => $recordId])
				->exists();
			if (!$schemeExists) {
				$insertData[] = [$recordId, $json];
			} else {
				$updateData[] = [$recordId, $json];
			}
		}
		if (!empty($insertData)) {
			\App\Db::getInstance()->createCommand()->batchInsert($table, ['crmid', 'columns'], $insertData)->execute();
		}
		if (!empty($updateData)) {
			foreach ($updateData as $row) {
				\App\Db::getInstance()->createCommand()->update($table, ['columns' => $row[1]], ['crmid' => $row[0]])->execute();
			}
		}
		unset($insertData, $availableColumns, $updateData, $row, $table, $schemeExists);
	}
}
