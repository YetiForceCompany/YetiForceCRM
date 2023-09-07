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

class Vtiger_Email_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		if (\in_array($operator, ['e', 'n'])) {
			$this->validate($value, true);
		}
		return $this->getDBValue($value);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!filter_var($value, FILTER_VALIDATE_EMAIL) || $value !== filter_var($value, FILTER_SANITIZE_EMAIL)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->getMaxValue();
		if ($maximumLength && App\TextUtils::getTextLength($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($value && !$rawText) {
			$rawValue = $value;
			$value = \App\Purifier::encodeHtml(App\TextUtils::textTruncate($value, $length));
			$data = 'title="' . \App\Language::translate('LBL_SEND_EMAIL') . '" ' . \App\Mail::getComposeAttr($rawValue, $record, 'Detail', 'new');
			$icon = $button = '';
			if ('Base' !== \App\Mail::getMailComposer()) {
				$icon = '<span class="fa-solid fa-envelope" aria-hidden="true"></span> ';
				$moduleName = $recordModel->getModuleName();
				$url = "index.php?module={$moduleName}&action=Fields&mode=getCopyValue&fieldName={$this->getFieldModel()->getName()}&record={$recordModel->getId()}";
				$button = "<button type=\"button\" class=\"btn btn-primary btn-xs ml-1 js-copy-clipboard-url\" data-url=\"$url\" title=\"" . \App\Language::translate('BTN_COPY_TO_CLIPBOARD', $moduleName) . '"><span class="fa-regular fa-copy"></span></button>';
			}
			return "<a class=\"u-cursor-pointer js-email-compose \" {$data} data-js=\"click|container\">{$icon}{$value}</a>$button";
		}
		$value = $value ? \App\Purifier::encodeHtml($value) : '';
		return $length ? App\TextUtils::textTruncate($value, $length) : $value;
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		return $this->getDisplayValue($value, $record, $recordModel, $rawText, $this->getFieldModel()->get('maxlengthtext') ?: false);
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Email.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny', 'ef', 'nf'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		if (!\in_array($operator, ['e', 'n'])) {
			return 'ConditionBuilder/BaseNoValidation.tpl';
		}
		return parent::getOperatorTemplateName($operator);
	}
}
