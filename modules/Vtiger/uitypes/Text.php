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

class Vtiger_Text_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\Utils\Completions::encodeAll(\App\Purifier::decodeHtml($value));
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
		$value = $request->getForHtml($requestFieldName, '');
		$this->validate($value);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!is_string($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength && strlen($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return \App\Utils\Completions::encode(parent::getEditViewDisplayValue($value, $recordModel));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		if (is_int($length)) {
			$value = \App\TextParser::htmlTruncate($value, $length);
		}
		if ($rawText) {
			$value = \App\Purifier::purifyHtml($value);
		} else {
			$value = \App\Utils\Completions::decode(\App\Purifier::purifyHtml($value));
		}
		if (300 !== $this->getFieldModel()->getUIType()) {
			$value = nl2br($value);
		}
		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return parent::getListViewDisplayValue(trim(strip_tags($value)), $record, $recordModel, $rawText);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Text.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'];
	}
}
