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

/**
 * Class Vtiger_Multiowner_UIType.
 */
class Vtiger_Multiowner_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		foreach ($value as $shownerid) {
			if (!is_numeric($shownerid)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
			}
		}
		$this->validate = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($value === null && !is_array($value)) {
			return '';
		}
		foreach ($value as $row) {
			if (self::getOwnerType($row) === 'User') {
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

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiOwner.tpl';
	}
}
