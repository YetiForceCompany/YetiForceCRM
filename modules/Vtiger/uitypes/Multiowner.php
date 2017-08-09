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
 * Class Vtiger_Multiowner_UIType
 */
class Vtiger_Multiowner_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/MultiOwner.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param array $values
	 * @param int $record
	 * @param Vtiger_Record_Model $recordInstance
	 * @param bool $rawText
	 * @return object
	 */
	public function getDisplayValue($values, $record = false, $recordInstance = false, $rawText = false)
	{
		if ($values === null && !is_array($values))
			return;
		foreach ($values as $value) {
			if (self::getOwnerType($value) === 'User') {
				$userModel = Users_Record_Model::getCleanInstance('Users');
				$userModel->setId($value);
				$detailViewUrl = $userModel->getDetailViewUrl();
				$currentUser = Users_Record_Model::getCurrentUserModel();
				if (!$currentUser->isAdminUser()) {
					return \App\Fields\Owner::getLabel($value);
				}
			} else {
				$currentUser = Users_Record_Model::getCurrentUserModel();
				if (!$currentUser->isAdminUser()) {
					return \App\Fields\Owner::getLabel($value);
				}
				$recordModel = new Settings_Groups_Record_Model();
				$recordModel->set('groupid', $value);
				$detailViewUrl = $recordModel->getDetailViewUrl();
			}
			if ($rawText) {
				$displayvalue[] = \App\Fields\Owner::getLabel($value);
			} else {
				$displayvalue[] = "<a href=" . $detailViewUrl . ">" . \App\Fields\Owner::getLabel($value) . "</a>&nbsp";
			}
		}
		$displayvalue = implode(',', $displayvalue);
		return $displayvalue;
	}

	/**
	 * Function to know owner is either User or Group
	 * @param integer $id userId/GroupId
	 * @return string User/Group
	 */
	public static function getOwnerType($id)
	{
		$result = (new \App\Db\Query())->from('vtiger_users')->where(['id' => $id])->exists();
		if ($result) {
			return 'User';
		}
		return 'Group';
	}
}
