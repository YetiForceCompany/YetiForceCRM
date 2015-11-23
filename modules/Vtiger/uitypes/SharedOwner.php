<?php

/**
 * UIType sharedOwner Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_sharedOwner_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/SharedOwner.tpl';
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/SharedOwnerFieldSearchView.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($values, $record = false, $recordInstance = false, $rawText = false)
	{
		if ($values == '')
			return;

		foreach (Vtiger_Functions::getArrayFromValue($values) as $value) {
			if (Vtiger_Owner_UIType::getOwnerType($value) === 'User') {
				$userModel = Users_Record_Model::getCleanInstance('Users');
				$userModel->set('id', $value);
				$detailViewUrl = $userModel->getDetailViewUrl();
				$currentUser = Users_Record_Model::getCurrentUserModel();
				if ($currentUser->isAdminUser() && !$rawText) {
					$displayvalue[] = '<a href=' . $detailViewUrl . '>' . rtrim(getOwnerName($value)) . '</a>';
				} else {
					$displayvalue[] = rtrim(getOwnerName($value));
				}
			} else {
				$currentUser = Users_Record_Model::getCurrentUserModel();
				if ($currentUser->isAdminUser() && !$rawText) {
					$recordModel = new Settings_Groups_Record_Model();
					$recordModel->set('groupid', $value);
					$detailViewUrl = $recordModel->getDetailViewUrl();
					$displayvalue[] = '<a href=' . $detailViewUrl . '>' . rtrim(getOwnerName($value)) . '</a>';
				} else {
					$displayvalue[] = rtrim(getOwnerName($value));
				}
			}
		}
		$displayvalue = implode(', ', $displayvalue);
		return $displayvalue;
	}
}
