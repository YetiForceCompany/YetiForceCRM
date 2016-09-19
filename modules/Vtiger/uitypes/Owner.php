<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Owner_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Owner.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$ownerName = \includes\fields\Owner::getLabel($value);
		if ($rawText) {
			return $ownerName;
		}
		if (\includes\fields\Owner::getType($value) === 'Users') {
			$userModel = Users_Record_Model::getCleanInstance('Users');
			$userModel->set('id', $value);
			$detailViewUrl = $userModel->getDetailViewUrl();
			$currentUser = Users_Record_Model::getCurrentUserModel();
			if (!$currentUser->isAdminUser() || $rawText) {
				return $ownerName;
			}
		} else {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			if (!$currentUser->isAdminUser() || $rawText) {
				return $ownerName;
			}
			$recordModel = new Settings_Groups_Record_Model();
			$recordModel->set('groupid', $value);
			$detailViewUrl = $recordModel->getDetailViewUrl();
		}
		return "<a href='" . $detailViewUrl . "'>$ownerName</a>";
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param <String> $value
	 * @return <String>
	 */
	public function getRelatedListDisplayValue($value)
	{
		return $value;
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/OwnerFieldSearchView.tpl';
	}

	public function isAjaxEditable()
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$roleModel = Settings_Roles_Record_Model::getInstanceById($userPrivModel->get('roleid'));
		if ($roleModel->get('changeowner')) {
			return true;
		}
		return false;
	}
}
