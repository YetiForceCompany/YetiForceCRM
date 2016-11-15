<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_CurrencyList_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/CurrencyList.tpl';
	}

	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$currencyName = (new App\Db\Query())->select(['currency_name'])
			->from('vtiger_currency_info')
			->where(['currency_status' => 'Active', 'id' => $value])
			->scalar();
		if ($currencyName) {
			return $currencyName;
		}
		return $value;
	}

	public function getCurrenyListReferenceFieldName()
	{
		return 'currency_name';
	}
}
