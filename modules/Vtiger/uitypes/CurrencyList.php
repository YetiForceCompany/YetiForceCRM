<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_CurrencyList_UIType extends Vtiger_Picklist_UIType
{

	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$currencylist = $this->getPicklistValues();
		return isset($currencylist[$value]) ? $currencylist[$value] : $value;
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return array List of picklist values if the field
	 */
	public function getPicklistValues()
	{
		$fieldModel = $this->get('field');
		return $fieldModel->getCurrencyList();
	}

	/**
	 * Function defines empty picklist element availability
	 * @return boolean
	 */
	public function isEmptyPicklistOptionAllowed()
	{
		return false;
	}

	public function getCurrenyListReferenceFieldName()
	{
		return 'currency_name';
	}
}
