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

class Vtiger_Reminder_UIType extends Vtiger_Date_UIType
{
	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$reminderValue = '';
		$reminderTime = $this->getEditViewDisplayValue($value, $recordModel);
		if (!empty($reminderTime[0])) {
			$reminderValue = $reminderTime[0] . ' ' . \App\Language::translate('LBL_DAYS');
		}
		if (!empty($reminderTime[1])) {
			$reminderValue = $reminderValue . ' ' . $reminderTime[1] . ' ' . \App\Language::translate('LBL_HOURS');
		}
		if (!empty($reminderTime[2])) {
			$reminderValue = $reminderValue . ' ' . $reminderTime[2] . ' ' . \App\Language::translate('LBL_MINUTES');
		}
		return $reminderValue;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (!empty($value)) {
			$days = floor($value / (24 * 60));
			$hours = floor(($value - $days * 24 * 60) / 60);
			$minutes = ($value - ($days * 24 * 60)) % 60;
			return [$days, $hours, $minutes];
		}
		return '';
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (($isUserFormat && !\in_array($value, [0, 1, '1', '0', 'on'])) || (!$isUserFormat && !(empty($value) || is_numeric($value)))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getBoolean($requestFieldName);
		$this->validate($value, true);
		if ($value) {
			if (!$request->has('remdays') || !$request->has('remhrs') || !$request->has('remmin')) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
			$value = $request->getInteger('remdays') * 24 * 60 + $request->getInteger('remhrs') * 60 + $request->getInteger('remmin');
		}
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Reminder.tpl';
	}

	/** {@inheritdoc} */
	public function getDetailViewTemplateName()
	{
		return 'Detail/Field/Reminder.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['y', 'ny'];
	}

	/** {@inheritdoc} */
	public function isActiveSearchView()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}
}
