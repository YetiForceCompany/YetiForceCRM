<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Text_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * Set value from request
	 * @param \App\Request $request
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel)
	{
		$fieldName = $this->get('field')->getFieldName();
		if ($this->get('field')->getUIType() === 300) {
			$value = $request->getForHtml($fieldName, '');
		} else {
			$value = $request->get($fieldName, '');
		}
		$this->validate($value);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * Function to get the display value, for the current field type with given DB Insert Value
	 * @param mixed $value
	 * @param int $record
	 * @param type $recordModel
	 * @param Vtiger_Record_Model $rawText
	 * @return mixed
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$uiType = $this->get('field')->get('uitype');
		if ($uiType === 300) {
			return App\Purifier::purifyHtml($value);
		} else {
			return nl2br(\App\Purifier::encodeHtml($value));
		}
	}

	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Text.tpl';
	}
}
