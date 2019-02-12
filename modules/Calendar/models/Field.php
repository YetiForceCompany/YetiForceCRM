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
 * Calendar Field Model Class.
 */
class Calendar_Field_Model extends Vtiger_Field_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getValidator()
	{
		$validator = [];
		if ($this->getName() === 'due_date') {
			$funcName = ['name' => 'greaterThanDependentField',
				'params' => ['date_start'], ];
			array_push($validator, $funcName);
		} else {
			$validator = parent::getValidator();
		}
		return $validator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFieldDataType()
	{
		if ($this->getName() == 'date_start' || $this->getName() == 'due_date') {
			return 'datetime';
		} elseif ($this->getUIType() === 30) {
			return 'reminder';
		}
		return parent::getFieldDataType();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($recordModel) {
			if ($this->getName() === 'date_start') {
				$dateTimeValue = $value . ' ' . $recordModel->get('time_start');
				$value = $this->getUITypeModel()->getDisplayValue($dateTimeValue);
				list($startDate, $startTime) = explode(' ', $value);

				return $startDate . ' ' . $startTime;
			} elseif ($this->getName() === 'due_date') {
				$dateTimeValue = $value . ' ' . $recordModel->get('time_end');
				$value = $this->getUITypeModel()->getDisplayValue($dateTimeValue);
				list($startDate, $startTime) = explode(' ', $value);

				return $startDate . ' ' . $startTime;
			}
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}

	/**
	 * Function to get Edit view display value.
	 *
	 * @param string Data base value
	 *
	 * @return string value
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value)) {
			$fieldName = $this->getName();
			if ($fieldName === 'date_start') {
				return DateTimeField::convertToUserFormat(date('Y-m-d'));
			} elseif ($fieldName === 'due_date') {
				$userModel = \App\User::getCurrentUserModel();
				$defaultType = $userModel->getDetail('defaultactivitytype');
				$typeByDuration = \App\Json::decode($userModel->getDetail('othereventduration'));
				$typeByDuration = array_column($typeByDuration, 'duration', 'activitytype');
				$minutes = $typeByDuration[$defaultType] ?? 0;
				return DateTimeField::convertToUserFormat(date('Y-m-d', strtotime("+$minutes minutes")));
			}
		}
		return parent::getEditViewDisplayValue($value, $recordModel);
	}

	/**
	 * Function to get the advanced filter option names by Field type.
	 *
	 * @return <Array>
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{
		$filterOpsByFieldType = parent::getAdvancedFilterOpsByFieldType();
		$filterOpsByFieldType['O'] = ['e', 'n'];
		return $filterOpsByFieldType;
	}

	/**
	 * Function which will check if empty piclist option should be given.
	 */
	public function isEmptyPicklistOptionAllowed()
	{
		if ($this->getFieldName() == 'visibility') {
			return false;
		}
		return true;
	}

	/**
	 * Function to get the field details.
	 *
	 * @return <Array> - array of field values
	 */
	public function getFieldInfo()
	{
		parent::getFieldInfo();
		//Change the default search operator
		if ($this->get('name') == 'date_start') {
			$searchParams = App\Condition::validSearchParams('Calendar', \App\Request::_getArray('search_params'));
			if (!empty($searchParams)) {
				foreach ($searchParams[0] as $value) {
					if ($value[0] == 'date_start') {
						$this->fieldInfo['searchOperator'] = $value[1];
					}
				}
			}
		}
		return $this->fieldInfo;
	}
}
