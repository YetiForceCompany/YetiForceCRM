<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_UserRole_UIType extends Vtiger_Picklist_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if ('H' !== substr($value, 0, 1) || !is_numeric(substr($value, 1)) || null === \App\PrivilegeUtil::getRoleName($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$roleName = \App\PrivilegeUtil::getRoleName($value);
		if ($rawText) {
			return \App\Language::translate($roleName, $this->getFieldModel()->getModuleName());
		}
		$displayValue = \App\Language::translate($roleName, $this->getFieldModel()->getModuleName(), false, false);
		$displayValue = \App\TextUtils::textTruncate($displayValue, \is_int($length) ? $length : false);
		if (\App\User::getCurrentUserModel()->isAdmin()) {
			$roleRecordModel = new Settings_Roles_Record_Model();
			$roleRecordModel->set('roleid', $value);
			return '<a href="' . $roleRecordModel->getEditViewUrl() . '">' . \App\Purifier::encodeHtml($displayValue) . '</a>';
		}
		return \App\Purifier::encodeHtml($displayValue);
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @return string[]
	 */
	public function getPicklistValues()
	{
		$roles = [];
		foreach (Settings_Roles_Record_Model::getAll() as $roleId => $roleModel) {
			$roles[$roleId] = \App\Language::translate($roleModel->getName(), $this->getFieldModel()->getModuleName(), false, false);
		}
		return $roles;
	}

	/**
	 * Function searches for value data.
	 *
	 * @param string $value
	 *
	 * @return string[]
	 */
	public function getSearchValues($value)
	{
		return (new App\Db\Query())->select(['roleid', 'rolename'])->from('vtiger_role')->where(['like', 'rolename', $value])
			->createCommand()->queryAllByGroup();
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/UserRole.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'c'];
	}

	/** {@inheritdoc} */
	public function getDetailViewTemplateName()
	{
		return 'Detail/Field/UserRole.tpl';
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		if ('e' === $operator) {
			return 'ConditionBuilder/UserRole.tpl';
		}
		return 'ConditionBuilder/Base.tpl';
	}
}
