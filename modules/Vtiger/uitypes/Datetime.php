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
 * Uitype: 80.
 */
class Vtiger_Datetime_UIType extends Vtiger_Date_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		$arrayDateTime = explode(' ', $value, 2);
		$cnt = \count($arrayDateTime);
		if (1 === $cnt) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
		} elseif (2 === $cnt) { //Date
			parent::validate($arrayDateTime[0], $isUserFormat);
			(new Vtiger_Time_UIType())->validate($arrayDateTime[1], $isUserFormat); //Time
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return empty($value) ? '' : App\Fields\DateTime::formatToDb($value);
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		if (80 === $this->getFieldModel()->getUIType()) {
			return $rawText ? Vtiger_Util_Helper::formatDateDiffInStrings($value) : '<span title="' . App\Fields\DateTime::formatToDisplay($value) . '">' . Vtiger_Util_Helper::formatDateDiffInStrings($value) . '</span>';
		}
		return App\Fields\DateTime::formatToDisplay($value);
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		if (80 === $this->getFieldModel()->getUIType()) {
			return $rawText ? \App\Fields\DateTime::formatToViewDate($value) : '<span title="' . App\Fields\DateTime::formatToDisplay($value) . '">' . \App\Fields\DateTime::formatToViewDate($value) . '</span>';
		}
		return \App\TextUtils::textTruncate($this->getDisplayValue($value, $record, $recordModel, $rawText), $this->getFieldModel()->get('maxlengthtext'));
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if ($value) {
			$value = \App\Fields\DateTime::formatToDisplay($value);
		}
		return \App\Purifier::encodeHtml($value);
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		if ('' === $value) {
			$value = $defaultValue ?? '';
		}
		if (null === $value || '0000-00-00 00:00:00' === $value) {
			$value = '';
		}
		$valuesList = explode(' ', $value);
		if (1 === \count($valuesList)) {
			$value = '';
		}
		if (0 == preg_match('/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2} ([0-1][0-9]|[2][0-3])([:][0-5][0-9]){1,2}$/', $value)) {
			$value = '';
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/DateTime.tpl';
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/DateTime.tpl';
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'bw' === $operator ? 'ConditionBuilder/DateTimeRange.tpl' : parent::getOperatorTemplateName($operator);
	}

	/** {@inheritdoc} */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		if (!$params) {
			return $this->getDisplayValue($value, $recordModel->getId(), $recordModel, true);
		}
		$params = \App\TextParser::parseFieldParam($params);
		if (isset($params['format'])) {
			$return = \DateTimeField::convertToUserTimeZone($value)->format($params['format']);
		} else {
			$return = $this->getDisplayValue($value, $recordModel->getId(), $recordModel, true);
		}
		return $return;
	}

	/** {@inheritdoc} */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel, array $params = [])
	{
		$value = \App\Purifier::decodeHtml($this->getDisplayValue($value, $recordModel->getId(), $recordModel, true, false));
		if (80 === $this->getFieldModel()->getUIType()) {
			return $value;
		}
		return $value . date(' (T P)', strtotime($value));
	}
}
