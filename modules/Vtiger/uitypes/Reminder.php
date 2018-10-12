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

class Vtiger_Reminder_UIType extends Vtiger_Date_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$reminder_value = '';
		$reminder_time = $this->getEditViewDisplayValue($value, $recordModel);
		if (!empty($reminder_time[0])) {
			$reminder_value = $reminder_time[0] . ' ' . \App\Language::translate('LBL_DAYS');
		}
		if (!empty($reminder_time[1])) {
			$reminder_value = $reminder_value . ' ' . $reminder_time[1] . ' ' . \App\Language::translate('LBL_HOURS');
		}
		if (!empty($reminder_time[2])) {
			$reminder_value = $reminder_value . ' ' . $reminder_time[2] . ' ' . \App\Language::translate('LBL_MINUTES');
		}
		return $reminder_value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if ($value != 0) {
			$rem_days = floor($value / (24 * 60));
			$rem_hrs = floor(($value - $rem_days * 24 * 60) / 60);
			$rem_min = ($value - ($rem_days * 24 * 60)) % 60;
			$reminder_time = [$rem_days, $rem_hrs, $rem_min];

			return $reminder_time;
		} else {
			return '';
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (($isUserFormat && !in_array($value, [0, 1, '1', '0', 'on'])) || (!$isUserFormat && !(empty($value) || is_numeric($value)))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getBoolean($requestFieldName);
		$this->validate($value, true);
		if ($value) {
			if (!$request->has('remdays') || !$request->has('remhrs') || !$request->has('remmin')) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
			}
			$value = $request->getInteger('remdays') * 24 * 60 + $request->getInteger('remhrs') * 60 + $request->getInteger('remmin');
		}
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return (int) $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Reminder.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewTemplateName()
	{
		return 'Detail/Field/Reminder.tpl';
	}
}
