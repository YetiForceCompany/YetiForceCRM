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
	 * @return string - Template Name
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
		if (empty($value)) {
			return '';
		}
		$ownerName = \App\Fields\Owner::getLabel($value);
		if ($rawText) {
			return $ownerName;
		}
		if (\App\Fields\Owner::getType($value) === 'Users') {
			$userModel = Users_Privileges_Model::getInstanceById($value);
			$userModel->setModule('Users');
			$ownerName = $userModel->getName();
			if ($userModel->get('status') === 'Inactive') {
				$ownerName = '<span class="redColor">' . $ownerName . '</span>';
			}
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
	 * Function to get the Display Value in ListView, for the current field type with given DB Insert Value
	 * @param mixed $value
	 * @return string
	 */
	public function getListViewDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$maxLengthText = $this->get('field')->get('maxlengthtext');
		$ownerName = \App\Fields\Owner::getLabel($value);
		if ($rawText) {
			return \vtlib\Functions::textLength($ownerName, $maxLengthText);
		}
		if (\App\Fields\Owner::getType($value) === 'Users') {
			$userModel = Users_Privileges_Model::getInstanceById($value);
			$userModel->setModule('Users');
			$ownerName = vtlib\Functions::textLength($userModel->getName(), $maxLengthText);
			if ($userModel->get('status') === 'Inactive') {
				$ownerName = '<span class="redColor">' . $ownerName . '</span>';
			}
			$detailViewUrl = $userModel->getDetailViewUrl();
			$currentUser = Users_Record_Model::getCurrentUserModel();
			if (!$currentUser->isAdminUser() || $rawText) {
				return $ownerName;
			}
		} else {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			if (!$currentUser->isAdminUser() || $rawText) {
				return \vtlib\Functions::textLength($ownerName, $maxLengthText);
			}
			$recordModel = new Settings_Groups_Record_Model();
			$recordModel->set('groupid', $value);
			$detailViewUrl = $recordModel->getDetailViewUrl();
		}
		return "<a href='" . $detailViewUrl . "'>$ownerName</a>";
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param string $value
	 * @return string
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

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return empty($value) ? \App\User::getCurrentUserId() : (int) $value;
	}
}
