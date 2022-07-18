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

class Vtiger_Text_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\Utils\Completions::encodeAll(\App\Purifier::decodeHtml($value));
	}

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getForHtml($requestFieldName, '');
		$this->validate($value);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!\is_string($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->getMaxValue();
		if ($maximumLength && \strlen($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return \App\Utils\Completions::encode(parent::getEditViewDisplayValue($value, $recordModel));
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$size = 'mini';
		if (empty($length)) {
			$length = 400;
		} elseif (\is_string($length)) {
			$size = $length;
			$length = 200;
		}
		if (300 === $this->getFieldModel()->getUIType()) {
			$value = \App\Purifier::purifyHtml($value);
			if (!$rawText) {
				$value = \App\Layout::truncateHtml(\App\Utils\Completions::decode($value), $size, $length);
			}
		} else {
			$value = \App\Purifier::purify($value);
			if (!$rawText) {
				$value = \App\Layout::truncateText($value, $length, true, true);
			}
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		if (empty($value)) {
			return '';
		}
		if (300 === $this->getFieldModel()->getUIType()) {
			$value = \App\Utils\Completions::decodeEmoji(\App\Purifier::purifyHtml($value));
		} else {
			$value = nl2br(\App\Purifier::purify($value));
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return $this->getDisplayValue($value, $record, $recordModel, $rawText, $this->getFieldModel()->get('maxlengthtext') ?: 50);
	}

	/** {@inheritdoc} */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel, array $params = [])
	{
		$value = \App\Utils\Completions::decode($value, \App\Utils\Completions::FORMAT_TEXT);
		return $this->getDisplayValue($value, $recordModel->getId(), $recordModel, true, false);
	}

	/** {@inheritdoc} */
	public function getHistoryDisplayValue($value, Vtiger_Record_Model $recordModel, $rawText = false)
	{
		if (\in_array(\App\Anonymization::MODTRACKER_DISPLAY, $this->getFieldModel()->getAnonymizationTarget())) {
			return '****';
		}
		$value = \App\Utils\Completions::decode($value, \App\Utils\Completions::FORMAT_TEXT);
		return $this->getDisplayValue($value, $recordModel->getId(), $recordModel, $rawText, \App\Config::module('ModTracker', 'TEASER_TEXT_LENGTH'));
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Text.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny', 'ef', 'nf'];
	}

	/** {@inheritdoc} */
	public function getDetailViewTemplateName()
	{
		return 'Detail/Field/Text.tpl';
	}
}
