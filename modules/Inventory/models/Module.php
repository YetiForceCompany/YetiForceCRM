<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Inventory Module Model Class
 */
class Inventory_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	public function isQuickCreateSupported()
	{
		//SalesOrder module is not enabled for quick create
		return false;
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	static function getAllCurrencies()
	{
		return getAllCurrencies();
	}

	static function getAllProductTaxes()
	{
		return getAllTaxes('available');
	}

	/**
	 * Function returns export query
	 * @param <String> $where
	 * @return <String> export query
	 */
	public function getExportQuery($focus, $query)
	{
		$baseTableName = $focus->table_name;
		$splitQuery = explode(' FROM ', $query);
		$columnFields = explode(',', $splitQuery[0]);
		foreach ($columnFields as $key => &$value) {
			if ($value == ' vtiger_inventoryproductrel.discount_amount') {
				$value = ' vtiger_inventoryproductrel.discount_amount AS item_discount_amount';
			} else if ($value == ' vtiger_inventoryproductrel.discount_percent') {
				$value = ' vtiger_inventoryproductrel.discount_percent AS item_discount_percent';
			} else if ($value == " $baseTableName.currency_id") {
				$value = ' vtiger_currency_info.currency_name AS currency_id';
			}
		}
		$joinSplit = explode(' WHERE ', $splitQuery[1]);
		$joinSplit[0] .= " LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = $baseTableName.currency_id";
		$splitQuery[1] = $joinSplit[0] . ' WHERE ' . $joinSplit[1];

		$query = implode(',', $columnFields) . ' FROM ' . $splitQuery[1];

		return $query;
	}
}
