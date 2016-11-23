<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_UserRole_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/UserRole.tpl';
	}

	/**
	 * Function to get the display value in detail view
	 * @param <Integer> crmid of record
	 * @return string
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		if ($value) {
			$userName = \App\PrivilegeUtil::getRoleName($value);
			return $userName;
		}
	}

	/**
	 * Function to get display value
	 * @param string $value
	 * @param <Number> $recordId
	 * @return string display value
	 */
	public function getDisplayValue($value, $recordId = false, $recordInstance = false, $rawText = false)
	{
		$displayValue = $this->getEditViewDisplayValue($value);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser() && $rawText === false) {
			$roleRecordModel = new Settings_Roles_Record_Model();
			$roleRecordModel->set('roleid', $value);
			return '<a href="' . $roleRecordModel->getEditViewUrl() . '">' . \vtlib\Functions::textLength($displayValue) . '</a>';
		}
		return $displayValue;
	}
}
