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
 * Calendar Field Model Class.
 */
class Calendar_Field_Model extends Vtiger_Field_Model
{
	/** {@inheritdoc} */
	public function getValidator()
	{
		$validator = [];
		if ('due_date' === $this->getName()) {
			$funcName = ['name' => 'greaterThanDependentField',
				'params' => ['date_start'], ];
			$validator[] = $funcName;
		} else {
			$validator = parent::getValidator();
		}
		return $validator;
	}

	/** {@inheritdoc} */
	public function getFieldDataType()
	{
		if ('date_start' == $this->getName() || 'due_date' == $this->getName()) {
			return 'datetime';
		}
		if (30 === $this->getUIType()) {
			return 'reminder';
		}
		return parent::getFieldDataType();
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($recordModel) {
			if ('date_start' === $this->getName()) {
				$value = $value . ' ' . $recordModel->get('time_start');
			} elseif ('due_date' === $this->getName()) {
				$value = $value . ' ' . $recordModel->get('time_end');
			}
		}
		return parent::getDisplayValue($value, $record, $recordModel, $rawText, $length);
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (empty($value)) {
			$fieldName = $this->getName();
			if ('date_start' === $fieldName) {
				return DateTimeField::convertToUserFormat(date('Y-m-d'));
			}
			if ('due_date' === $fieldName) {
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
		if ('visibility' == $this->getFieldName()) {
			return false;
		}
		return true;
	}

	/** {@inheritdoc} */
	public function getFieldInfo(): array
	{
		$this->loadFieldInfo();
		//Change the default search operator
		if ('date_start' == $this->get('name')) {
			$searchParams = App\Condition::validSearchParams('Calendar', \App\Request::_getArray('search_params'));
			if (!empty($searchParams)) {
				foreach ($searchParams[0] as $value) {
					if ('date_start' == $value[0]) {
						$this->fieldInfo['searchOperator'] = $value[1];
					}
				}
			}
		}
		return $this->fieldInfo;
	}
}
