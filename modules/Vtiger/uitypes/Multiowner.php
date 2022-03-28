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

/**
 * Class Vtiger_Multiowner_UIType.
 */
class Vtiger_Multiowner_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return explode('|##|', $value);
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return implode('|##|', $value);
	}

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getArray($requestFieldName, 'Integer');
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$this->validate($value, true);
		return $this->getDBValue($value);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		$hashValue = \is_array($value) ? implode('|', $value) : $value;
		if (isset($this->validate[$hashValue]) || empty($value)) {
			return;
		}
		if (!$isUserFormat) {
			$value = explode('|##|', $value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		foreach ($value as $shownerid) {
			if (!is_numeric($shownerid)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
		}
		$this->validate[$hashValue] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		if (!\is_array($value)) {
			$value = explode('|##|', $value);
		}
		foreach ($value as $row) {
			if ('User' === self::getOwnerType($row)) {
				$userModel = Users_Record_Model::getCleanInstance('Users');
				$userModel->setId($row);
				$detailViewUrl = $userModel->getDetailViewUrl();
				$currentUser = Users_Record_Model::getCurrentUserModel();
				if (!$currentUser->isAdminUser()) {
					return \App\Fields\Owner::getLabel($row);
				}
			} else {
				$currentUser = Users_Record_Model::getCurrentUserModel();
				if (!$currentUser->isAdminUser()) {
					return \App\Fields\Owner::getLabel($row);
				}
				$recordModel = new Settings_Groups_Record_Model();
				$recordModel->set('groupid', $row);
				$detailViewUrl = $recordModel->getDetailViewUrl();
			}
			if ($rawText) {
				$displayvalue[] = \App\Fields\Owner::getLabel($row);
			} else {
				$displayvalue[] = '<a href=' . $detailViewUrl . '>' . \App\Fields\Owner::getLabel($row) . '</a>&nbsp;';
			}
		}
		return implode(',', $displayvalue);
	}

	/**
	 * Function to know owner is either User or Group.
	 *
	 * @param int $id userId/GroupId
	 *
	 * @return string User/Group
	 */
	public static function getOwnerType($id)
	{
		return \App\Fields\Owner::getType($id);
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiOwner.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny', 'ef', 'nf'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/SharedOwner.tpl';
	}
}
