<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * *********************************************************************************** */

/**
 * User Field Model Class.
 */
class Users_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function to check whether the current field is read-only.
	 *
	 * @return bool - true/false
	 */
	public function isReadOnly()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (($currentUserModel->isAdminUser() === false && $this->get('uitype') == 98) || $this->get('uitype') == 156) {
			return true;
		}
		return parent::isReadOnly();
	}

	/**
	 * Function to check if the field is shown in detail view.
	 *
	 * @return bool - true/false
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
	 * Function to check if the field is export table.
	 *
	 * @return bool
	 */
	public function isExportTable()
	{
		return $this->isViewable() || $this->getUIType() === 99;
	}

	/**
	 * Function to check whether field is ajax editable'.
	 *
	 * @return bool
	 */
	public function isAjaxEditable()
	{
		if (!$this->isEditable() || $this->get('uitype') === 105 ||
			$this->get('uitype') === 106 || $this->get('uitype') === 98 || $this->get('uitype') === 101 || 'date_format' === $this->getFieldName() || 'email1' === $this->getFieldName()) {
			return false;
		}
		if ($this->getFieldName() === 'login_method') {
			return \App\User::getCurrentUserModel()->isAdmin();
		}
		return parent::isAjaxEditable();
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @return array List of picklist values if the field is of type picklist or multipicklist, null otherwise
	 */
	public function getPicklistValues($skipCheckingRole = false)
	{
		if ($this->get('uitype') == 115) {
			$fieldPickListValues = [];
			$query = (new \App\Db\Query())->select([$this->getFieldName()])->from('vtiger_' . $this->getFieldName());
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$picklistValue = $row[$this->getFieldName()];
				$fieldPickListValues[$picklistValue] = \App\Language::translate($picklistValue, $this->getModuleName());
			}
			$dataReader->close();

			return $fieldPickListValues;
		}
		return parent::getPicklistValues($skipCheckingRole);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$fieldName = $this->getFieldName();
		if (($fieldName === 'currency_decimal_separator' || $fieldName === 'currency_grouping_separator') && ($value === ' ')) {
			return \App\Language::translate('LBL_SPACE', 'Users');
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}

	/**
	 * Function returns all the User Roles.
	 *
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
	 * Function to check whether this field editable or not.
	 *
	 * @return bool true/false
	 */
	public function isEditable()
	{
		if (($this->get('uitype') === 115 && (!\App\User::getCurrentUserModel()->isAdmin() || \App\User::getCurrentUserId() === $this->get('rocordId')))) {
			return false;
		}
		if ($this->getColumnName() === 'authy_secret_totp') {
			return $this->get('rocordId') === \App\User::getCurrentUserId();
		}
		if (!$this->get('editable')) {
			$this->set('editable', parent::isEditable());
		}
		return $this->get('editable');
	}

	/**
	 * {@inheritdoc}
	 */
	public function isViewable()
	{
		if ($this->getColumnName() === 'authy_secret_totp') {
			return $this->get('rocordId') === \App\User::getCurrentUserId();
		}
		return parent::isViewable();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEditableReadOnly()
	{
		if ($this->getColumnName() === 'login_method' && !\App\User::getCurrentUserModel()->isAdmin()) {
			return true;
		}
		return parent::isEditableReadOnly();
	}

	/**
	 * Function which will check if empty piclist option should be given.
	 *
	 * @return bool
	 */
	public function isEmptyPicklistOptionAllowed()
	{
		if ($this->getFieldName() === 'reminder_interval') {
			return true;
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isWritable()
	{
		if ($this->getFieldName() === 'is_admin' && \App\User::getCurrentUserModel()->isAdmin()) {
			return true;
		}
		return parent::isWritable();
	}
}
