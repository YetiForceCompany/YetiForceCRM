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
 * User Field Model Class
 */
class Users_Field_Model extends Vtiger_Field_Model
{

	/**
	 * Function to check whether the current field is read-only
	 * @return boolean - true/false
	 */
	public function isReadOnly()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (($currentUserModel->isAdminUser() === false && $this->get('uitype') == 98) || in_array($this->get('uitype'), array(115, 156))) {
			return true;
		}
		return parent::isReadOnly();
	}

	/**
	 * Function to check if the field is shown in detail view
	 * @return boolean - true/false
	 */
	public function isViewEnabled()
	{
		if ($this->getDisplayType() === 4 || in_array($this->get('presence'), [1, 3])) {
			return false;
		}
		if ($this->get('uitype') === 106 && !AppConfig::module('Users', 'USER_NAME_IS_EDITABLE')) {
			return false;
		}
		return parent::isViewEnabled();
	}

	/**
	 * Function to get the Webservice Field data type
	 * @return string Data type of the field
	 */
	public function getFieldDataType()
	{
		switch ($this->get('uitype')) {
			case 99:
				return 'password';
			case 115:
				return 'picklist';
			case 98:
				return 'userRole';
			case 101:
				return 'userReference';
			case 105:
				return 'image';
			case 31:
				return 'theme';
			default:
				break;
		}
		return parent::getFieldDataType();
	}

	/**
	 * Function to check whether field is ajax editable'
	 * @return boolean
	 */
	public function isAjaxEditable()
	{
		if (!$this->isEditable() || $this->get('uitype') === 115 || $this->get('uitype') === 105 ||
			$this->get('uitype') === 106 || $this->get('uitype') === 98 || $this->get('uitype') === 101 || 'date_format' === $this->getFieldName() || 'email1' === $this->getFieldName()) {
			return false;
		}
		return parent::isAjaxEditable();
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return array List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getPicklistValues($skipCheckingRole = false)
	{
		if ($this->get('uitype') == 115) {
			$fieldPickListValues = [];
			$query = (new \App\Db\Query())->select([$this->getFieldName()])->from('vtiger_' . $this->getFieldName());
			$dataReader = $query->createCommand($db)->query();
			while ($row = $dataReader->read()) {
				$picklistValue = $row[$this->getFieldName()];
				$fieldPickListValues[$picklistValue] = \App\Language::translate($picklistValue, $this->getModuleName());
			}
			return $fieldPickListValues;
		}
		return parent::getPicklistValues($skipCheckingRole);
	}

	/**
	 * Function to returns all skins(themes)
	 * @return array
	 */
	public function getAllSkins()
	{
		return Vtiger_Theme::getAllSkins();
	}

	/**
	 * Function to retieve display value for a value
	 * @param string $value - value which need to be converted to display value
	 * @return string - converted display value
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$fieldName = $this->getFieldName();
		if (($fieldName === 'currency_decimal_separator' || $fieldName === 'currency_grouping_separator') && ($value == '&nbsp;')) {
			return \App\Language::translate('LBL_SPACE', 'Users');
		}
		return parent::getDisplayValue($value, $record, $recordInstance, $rawText);
	}

	/**
	 * Function returns all the User Roles
	 * @return array
	 */
	public function getAllRoles()
	{
		$roleModels = Settings_Roles_Record_Model::getAll();
		$roles = [];
		foreach ($roleModels as $roleId => $roleModel) {
			$roleName = $roleModel->getName();
			$roles[$roleName] = $roleId;
		}
		return $roles;
	}

	/**
	 * Function to check whether this field editable or not
	 * @return boolean true/false
	 */
	public function isEditable()
	{
		$isEditable = $this->get('editable');
		if (!$isEditable) {
			$this->set('editable', parent::isEditable());
		}
		return $this->get('editable');
	}

	/**
	 * Function which will check if empty piclist option should be given
	 * @return boolean
	 */
	public function isEmptyPicklistOptionAllowed()
	{
		if ($this->getFieldName() === 'reminder_interval') {
			return true;
		}
		return false;
	}
}
