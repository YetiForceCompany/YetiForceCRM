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
 * Calendar Field Model Class
 */
class Calendar_Field_Model extends Vtiger_Field_Model
{

	/**
	 * Function returns special validator for fields
	 * @return <Array>
	 */
	public function getValidator()
	{
		$validator = array();
		$fieldName = $this->getName();

		switch ($fieldName) {
			case 'due_date': $funcName = array('name' => 'greaterThanDependentField',
					'params' => array('date_start'));
				array_push($validator, $funcName);
				break;
			// NOTE: Letting user to add pre or post dated Event.
			/* case 'date_start' : $funcName = array('name'=>'greaterThanToday');
			  array_push($validator, $funcName);
			  break; */
			default : $validator = parent::getValidator();
				break;
		}
		return $validator;
	}

	/**
	 * Function to get the Webservice Field data type
	 * @return <String> Data type of the field
	 */
	public function getFieldDataType()
	{
		if ($this->getName() == 'date_start' || $this->getName() == 'due_date') {
			return 'datetime';
		} else if ($this->get('uitype') == '30') {
			return 'reminder';
		} else if ($this->getName() == 'recurringtype') {
			return 'recurrence';
		}
		return parent::getFieldDataType();
	}

	/**
	 * Customize the display value for detail view.
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		if ($recordInstance) {
			if ($this->getName() == 'date_start') {
				$dateTimeValue = $value . ' ' . $recordInstance->get('time_start');
				$value = $this->getUITypeModel()->getDisplayValue($dateTimeValue);
				list($startDate, $startTime, $meridiem) = explode(' ', $value);
				return $startDate . ' ' . $startTime . ' ' . $meridiem;
			} else if ($this->getName() == 'due_date') {
				$dateTimeValue = $value . ' ' . $recordInstance->get('time_end');
				$value = $this->getUITypeModel()->getDisplayValue($dateTimeValue);
				list($startDate, $startTime, $meridiem) = explode(' ', $value);
				return $startDate . ' ' . $startTime . ' ' . $meridiem;
			}
		}
		return parent::getDisplayValue($value, $record, $recordInstance, $rawText);
	}

	/**
	 * Function to get Edit view display value
	 * @param <String> Data base value
	 * @return <String> value
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		$fieldName = $this->getName();

		if ($fieldName == 'time_start' || $fieldName == 'time_end') {
			return $this->getUITypeModel()->getDisplayTimeDifferenceValue($fieldName, $value);
		}

		//Set the start date and end date
		if (empty($value)) {
			if ($fieldName === 'date_start') {
				return DateTimeField::convertToUserFormat(date('Y-m-d'));
			} elseif ($fieldName === 'due_date') {
				$currentUser = Users_Record_Model::getCurrentUserModel();
				$minutes = $currentUser->get('callduration');
				return DateTimeField::convertToUserFormat(date('Y-m-d', strtotime("+$minutes minutes")));
			}
		}
		return parent::getEditViewDisplayValue($value, $record);
	}

	/**
	 * Function which will give the picklist values for a recurrence field
	 * @param type $fieldName -- string
	 * @return type -- array of values
	 */
	public static function getReccurencePicklistValues()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$fieldModel = Vtiger_Field_Model::getInstance('recurringtype', Vtiger_Module_Model::getInstance('Events'));
		if ($fieldModel->isRoleBased() && !$currentUser->isAdminUser()) {
			$userModel = Users_Record_Model::getCurrentUserModel();
			$picklistValues = \includes\fields\Picklist::getRoleBasedPicklistValues('recurringtype', $userModel->get('roleid'));
		} else {
			$picklistValues = Vtiger_Util_Helper::getPickListValues('recurringtype');
		}
		foreach ($picklistValues as $value) {
			$fieldPickListValues[$value] = vtranslate($value, 'Events');
		}
		return $fieldPickListValues;
	}

	/**
	 * Function to get the advanced filter option names by Field type
	 * @return <Array>
	 */
	public static function getAdvancedFilterOpsByFieldType()
	{

		$filterOpsByFieldType = parent::getAdvancedFilterOpsByFieldType();
		$filterOpsByFieldType['O'] = array('e', 'n');

		return $filterOpsByFieldType;
	}

	/**
	 * Function which will check if empty piclist option should be given
	 */
	public function isEmptyPicklistOptionAllowed()
	{
		if ($this->getFieldName() == 'visibility') {
			return false;
		}
		return true;
	}

	/**
	 * Function to get visibilty permissions of a Field
	 * @param <String> $accessmode
	 * @return <Boolean>
	 */
	public function getPermissions($accessmode = 'readonly')
	{
		$user = Users_Record_Model::getCurrentUserModel();
		$privileges = $user->getPrivileges();
		if ($privileges->hasGlobalReadPermission()) {
			return true;
		} else {
			$modulePermission = Vtiger_Cache::get('modulePermission-' . $accessmode, $this->getModuleId());
			if (!$modulePermission) {
				$modulePermissionCalendar = self::preFetchModuleFieldPermission(vtlib\Functions::getModuleId('Calendar'), $accessmode);
				$modulePermissionEvents = self::preFetchModuleFieldPermission(vtlib\Functions::getModuleId('Events'), $accessmode);
				$modulePermission = $modulePermissionCalendar + $modulePermissionEvents;
				Vtiger_Cache::set('modulePermission-' . $accessmode, $this->getModuleId(), $modulePermission);
			}

			if (array_key_exists($this->getId(), $modulePermission)) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Function to get the field details
	 * @return <Array> - array of field values
	 */
	public function getFieldInfo()
	{
		parent::getFieldInfo();
		//Change the default search operator
		if ($this->get('name') == 'date_start') {
			$searchParams = AppRequest::get('search_params');
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
