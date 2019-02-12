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
 * Uitype: 80.
 */
class Vtiger_Datetime_UIType extends Vtiger_Date_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		$arrayDateTime = explode(' ', $value, 2);
		$cnt = count($arrayDateTime);
		if ($cnt === 1) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
		} elseif ($cnt === 2) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
			(new Vtiger_Time_UIType())->validate($arrayDateTime[1], $isUserFormat); //Time
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return '';
		}
		if ($this->getFieldModel()->getUIType() === 79) {
			return App\Fields\DateTime::formatToDb($value);
		} else {
			return parent::getDBValue($value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		if ($this->getFieldModel()->getUIType() === 80) {
			return $rawText ? Vtiger_Util_Helper::formatDateDiffInStrings($value) : '<span title="' . App\Fields\DateTime::formatToDisplay($value) . '">' . Vtiger_Util_Helper::formatDateDiffInStrings($value) . '</span>';
		} else {
			return App\Fields\DateTime::formatToDisplay($value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		if ($this->getFieldModel()->getUIType() === 80) {
			return $rawText ? \App\Fields\DateTime::formatToViewDate($value) : '<span title="' . App\Fields\DateTime::formatToDisplay($value) . '">' . \App\Fields\DateTime::formatToViewDate($value) . '</span>';
		}
		return \App\TextParser::textTruncate($this->getDisplayValue($value, $record, $recordModel, $rawText), $this->getFieldModel()->get('maxlengthtext'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		if ($this->getFieldModel()->getUIType() === 79) {
			return 'Edit/Field/DateTimeField.tpl';
		} else {
			return 'Edit/Field/DateTime.tpl';
		}
	}

	/**
	 * Generate valid sample value.
	 *
	 * @throws \Exception
	 *
	 * @return false|string
	 */
	public function getSampleValue()
	{
		return date('Y-m-d H:i:s', random_int(strtotime('-1 month'), strtotime('+1 month')));
	}
}
