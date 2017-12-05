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
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return empty($value) ? \App\User::getCurrentUserId() : (int) $value;
	}

	/**
	 * Verification of data
	 * @param string $value
	 * @param bool $isUserFormat
	 * @return null
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		$this->validate = true;
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
		if (empty($value)) {
			return '';
		}
		$ownerName = \App\Fields\Owner::getLabel($value);
		if ($rawText) {
			return $ownerName;
		}
		switch (\App\Fields\Owner::getType($value)) {
			case 'Users':
				$userModel = Users_Privileges_Model::getInstanceById($value);
				$userModel->setModule('Users');
				if ($userModel->get('status') === 'Inactive') {
					$ownerName = '<span class="redColor"><s>' . $ownerName . '</s></span>';
				}
				if (App\User::getCurrentUserModel()->isAdmin()) {
					$detailViewUrl = $userModel->getDetailViewUrl();
				}
				break;
			case 'Groups':
				if (App\User::getCurrentUserModel()->isAdmin()) {
					$recordModel = new Settings_Groups_Record_Model();
					$recordModel->set('groupid', $value);
					$detailViewUrl = $recordModel->getDetailViewUrl();
				}
				break;
			default:
				$ownerName = '<span class="redColor">---</span>';
				break;
		}
		if (isset($detailViewUrl)) {
			return "<a href='" . $detailViewUrl . "'>$ownerName</a>";
		}
		return $ownerName;
	}

	/**
	 * Function to get the Display Value in ListView, for the current field type with given DB Insert Value
	 * @param mixed $value
	 * @return string
	 */
	public function getListViewDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$ownerName = vtlib\Functions::textLength(\App\Fields\Owner::getLabel($value), $this->getFieldModel()->get('maxlengthtext'));
		if ($rawText) {
			return $ownerName;
		}
		switch (\App\Fields\Owner::getType($value)) {
			case 'Users':
				$userModel = Users_Privileges_Model::getInstanceById($value);
				$userModel->setModule('Users');
				if ($userModel->get('status') === 'Inactive') {
					$ownerName = '<span class="redColor">' . $ownerName . '</span>';
				}
				if (App\User::getCurrentUserModel()->isAdmin()) {
					$detailViewUrl = $userModel->getDetailViewUrl();
				}
				break;
			case 'Groups':
				if (App\User::getCurrentUserModel()->isAdmin()) {
					$recordModel = new Settings_Groups_Record_Model();
					$recordModel->set('groupid', $value);
					$detailViewUrl = $recordModel->getDetailViewUrl();
				}
				break;
			default:
				$ownerName = '<span class="redColor">---</span>';
				break;
		}
		if (isset($detailViewUrl)) {
			return "<a href='" . $detailViewUrl . "'>$ownerName</a>";
		}
		return $ownerName;
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

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Owner.tpl';
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
