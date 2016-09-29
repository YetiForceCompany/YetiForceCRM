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
		$instance = new self();
		return $instance;
	}

	public static function getPicklistValues($type)
	{
		$picklists['aggregation'] = ['LBL_CANNOT_BE_COMBINED', 'LBL_IN_TOTAL', 'LBL_CASCADE'];
		$picklists['discounts'] = ['LBL_GLOBAL', 'LBL_GROUP', 'LBL_INDIVIDUAL'];
		$picklists['taxs'] = ['LBL_GLOBAL', 'LBL_GROUP', 'LBL_INDIVIDUAL', 'LBL_REGIONAL'];
		return $picklists[$type];
	}

	public static function getTableNameFromType($type)
	{
		$tablename = ['DiscountConfiguration' => 'a_yf_discounts_config', 'TaxConfiguration' => 'a_yf_taxes_config'];
		return $tablename[$type];
	}

	public static function getConfig($type, $name = false)
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__ . " | Type: " . print_r($type, true) . " | Name: " . print_r($name, true));
		$db = PearDatabase::getInstance();
		$tableName = self::getTableNameFromType($type);
		$sql = sprintf('SELECT * FROM `%s`', $tableName);
		if ($name && !is_array($name)) {
			$name = [$name];
		}
		$params = [];
		if ($name) {
			$sql .= sprintf(' WHERE `param` IN (%s)', generateQuestionMarks($name));
			$params = $name;
		}
		$result = $db->pquery($sql, $params);
		$output = [];
		while ($row = $db->fetch_array($result)) {
			$output[$row['param']] = $row['value'];
		}
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
		return $output;
	}

	public function setConfig($type, $param)
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$db = PearDatabase::getInstance();
		$tableName = self::getTableNameFromType($type);
		$db->update($tableName, ['value' => $param['value']], '`param` = ?', [$param['param']]);
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
		return true;
	}
}
