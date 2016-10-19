<?php

/**
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Inventory_Module_Model extends Vtiger_Base_Model
{

	public static function getCleanInstance()
	{
		return new self();
	}

	public static function getPicklistValues($type)
	{
		$picklists['aggregation'] = ['LBL_CANNOT_BE_COMBINED', 'LBL_IN_TOTAL', 'LBL_CASCADE'];
		$picklists['discounts'] = ['LBL_GLOBAL', 'LBL_GROUP', 'LBL_INDIVIDUAL'];
		$picklists['taxs'] = ['LBL_GLOBAL', 'LBL_GROUP', 'LBL_INDIVIDUAL', 'LBL_REGIONAL'];
		return $picklists[$type];
	}

	private static $tablename = ['DiscountConfiguration' => 'a_#__discounts_config', 'TaxConfiguration' => 'a_#__taxes_config'];

	public static function getTableNameFromType($type)
	{
		return static::$tablename[$type];
	}

	public static function getConfig($type, $name = false)
	{
		\App\Log::trace('Start ' . __CLASS__ . ':' . __FUNCTION__ . " | Type: " . print_r($type, true) . " | Name: " . print_r($name, true));
		$tableName = self::getTableNameFromType($type);
		$query = (new \App\Db\Query())->from($tableName);
		if ($name && !is_array($name)) {
			$name = [$name];
		}
		if ($name) {
			$query->where(['param' => $name]);
		}
		$output = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$output[$row['param']] = $row['value'];
		}
		\App\Log::trace('End ' . __CLASS__ . ':' . __FUNCTION__);
		return $output;
	}

	public function setConfig($type, $param)
	{
		\App\Log::trace('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$tableName = self::getTableNameFromType($type);
		\App\Db::getInstance()->createCommand()
			->update($tableName, ['value' => $param['value']], ['param' => $param['param']])
			->execute();
		\App\Log::trace('End ' . __CLASS__ . ':' . __FUNCTION__);
		return true;
	}
}
