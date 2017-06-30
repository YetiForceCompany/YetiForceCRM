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

class Vtiger_Phone_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Phone.tpl';
	}

	/**
	 * Function to get the Detailview template name for the current UI Type Object 
	 * Added for PhoneCalls module
	 * @return string - Template Name
	 */
	public function getDetailViewTemplateName()
	{
		return 'uitypes/PhoneDetailView.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param mixed $value
	 * @param int|bool $record
	 * @param Vtiger_Record_Model|bool $recordInstance
	 * @param bool $rawText
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		if ($rawText) {
			return $value;
		}
		if (!\App\Integrations\Pbx::isActive()) {
			return \App\Purifier::encodeHtml($value);
		}
		return '<a class="phoneField" onclick="Vtiger_Index_Js.performPhoneCall(\'' . preg_replace('/(?<!^)\+|[^\d+]+/', '', $value) . '\',' . $record . ')"><span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span> ' . \App\Purifier::encodeHtml($value) . '</a>';
	}

	public function getListViewDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		return $this->getDisplayValue($value, $record, $recordInstance, $rawText);
	}
}
