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

class Calendar_Time_UIType extends Vtiger_Time_UIType
{
	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		$fieldName = $this->get('field')->getFieldName();
		if (empty($value) && ('time_end' === $fieldName || 'time_start' === $fieldName)) {
			$minutes = 0;
			if ('time_end' === $fieldName) {
				$userModel = \App\User::getCurrentUserModel();
				$defaultType = $userModel->getDetail('defaultactivitytype');
				$typeByDuration = \App\Json::decode($userModel->getDetail('othereventduration'));
				$typeByDuration = array_column($typeByDuration, 'duration', 'activitytype');
				$minutes = $typeByDuration[$defaultType] ?? 0;
			}
			$value = date('H:i', strtotime("+$minutes minutes"));
		}
		return parent::getEditViewDisplayValue($value, $recordModel);
	}
}
