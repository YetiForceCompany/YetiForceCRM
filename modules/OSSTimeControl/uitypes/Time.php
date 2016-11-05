<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSTimeControl_Time_UIType extends Vtiger_Time_UIType
{

	public function getEditViewDisplayValue($value, $record = false)
	{
		if (!empty($value)) {
			return parent::getEditViewDisplayValue($value, $record);
		}

		$specialTimeFields = array('time_start', 'time_end');

		$fieldInstance = $this->get('field')->getWebserviceFieldObject();
		$fieldName = $fieldInstance->getFieldName();

		if (!in_array($fieldName, $specialTimeFields)) {
			return parent::getEditViewDisplayValue($value, $record);
		} else {
			return $this->getDisplayTimeDifferenceValue($fieldName, $value);
		}
	}

	/**
	 * Function to get the calendar event call duration value in hour format
	 * @param type $fieldName
	 * @param type $value
	 * @return <Vtiger_Time_UIType> - getTimeValue 
	 */
	public function getDisplayTimeDifferenceValue($fieldName, $value)
	{
		$userModel = Users_Privileges_Model::getCurrentUserModel();
		$date = new DateTime($value);

		if ($fieldName == 'time_end' && empty($value)) {
			$date->modify("+15 minutes");
		}

		$dateTimeField = new DateTimeField($date->format('Y-m-d H:i:s'));
		$value = $dateTimeField->getDisplayTime();
		return $value;
	}
}
