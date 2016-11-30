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

class Vtiger_Multipicklist_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/MultiPicklist.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		if (empty($value)) {
			return null;
		}
		$value = explode(' |##| ', $value);
		$trValue = [];
		$moduleName = $this->get('field')->getModuleName();
		$countValue = count($value);
		for ($i = 0; $i < $countValue; $i++) {
			$trValue[] = Vtiger_Language_Handler::getTranslatedString($value[$i], $moduleName);
		}
		$trValue = implode(' |##| ', $trValue);

		return str_ireplace(' |##| ', ', ', $trValue);
	}

	/**
	 * Function to get the display value in edit view
	 * @param string $value
	 * @param int $record - Record ID
	 * @return array
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		return explode(' |##| ', $value);
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (is_array($value)) {
			$value = implode(' |##| ', $value);
		}
		return $value;
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/MultiSelectFieldSearchView.tpl';
	}
}
