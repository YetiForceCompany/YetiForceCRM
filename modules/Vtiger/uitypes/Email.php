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

class Vtiger_Email_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		if (in_array($operator, ['e', 'n'])) {
			$this->validate($value, true);
		}
		return $this->getDBValue($value);
	}

	/**
	 * Verification of data.
	 *
	 * @param string $value
	 * @param bool   $isUserFormat
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!filter_var($value, FILTER_VALIDATE_EMAIL) || $value !== filter_var($value, FILTER_SANITIZE_EMAIL)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength && App\TextParser::getTextLength($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$internalMailer = (int) \App\User::getCurrentUserModel()->getDetail('internal_mailer');
		if ($value && !$rawText) {
			$moduleName = $this->getFieldModel()->get('block')->module->name;
			$fieldName = $this->getFieldModel()->get('name');
			$rawValue = \App\Purifier::encodeHtml($value);
			$value = \App\Purifier::encodeHtml(App\TextParser::textTruncate($value));
			if ($internalMailer === 1 && \App\Privilege::isPermitted('OSSMail')) {
				$url = OSSMail_Module_Model::getComposeUrl($moduleName, $record, 'Detail', 'new');
				$mailConfig = OSSMail_Module_Model::getComposeParameters();
				return "<a class = \"u-cursor-pointer sendMailBtn\" data-url=\"$url\" data-module=\"$moduleName\" data-record=\"$record\" data-to=\"$rawValue\" data-popup=\"" . $mailConfig['popup'] . '" title="' . \App\Language::translate('LBL_SEND_EMAIL') . "\">$value</a>";
			} else {
				if ($moduleName === 'Users' && $fieldName === 'user_name') {
					return "<a class='u-cursor-pointer' href='mailto:" . $rawValue . "'>" . $value . '</a>';
				} else {
					return "<a class='emailField u-cursor-pointer'  href='mailto:" . $rawValue . "'>" . $value . '</a>';
				}
			}
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$internalMailer = (int) \App\User::getCurrentUserModel()->getDetail('internal_mailer');
		if ($value && !$rawText) {
			$moduleName = $this->getFieldModel()->get('block')->module->name;
			$fieldName = $this->getFieldModel()->get('name');
			$rawValue = \App\Purifier::encodeHtml($value);
			$value = \App\Purifier::encodeHtml(App\TextParser::textTruncate($value, $this->getFieldModel()->get('maxlengthtext')));
			if ($internalMailer === 1 && \App\Privilege::isPermitted('OSSMail')) {
				$url = OSSMail_Module_Model::getComposeUrl($moduleName, $record, 'Detail', 'new');
				$mailConfig = OSSMail_Module_Model::getComposeParameters();
				return "<a class = \"u-cursor-pointer sendMailBtn\" data-url=\"$url\" data-module=\"$moduleName\" data-record=\"$record\" data-to=\"$rawValue\" data-popup=\"" . $mailConfig['popup'] . '" title="' . \App\Language::translate('LBL_SEND_EMAIL') . "\">{$value}</a>";
			} else {
				if ($moduleName === 'Users' && $fieldName === 'user_name') {
					return "<a class='u-cursor-pointer' href='mailto:" . $rawValue . "'>" . $value . '</a>';
				} else {
					return "<a class='emailField u-cursor-pointer'  href='mailto:" . $rawValue . "'>" . $value . '</a>';
				}
			}
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function to get the Template name for the current UI Type object.
	 *
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Email.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'];
	}

	/**
	 * Returns template for operator.
	 *
	 * @param string $operator
	 *
	 * @return string
	 */
	public function getOperatorTemplateName(string $operator = '')
	{
		if (!in_array($operator, ['e', 'n'])) {
			return 'ConditionBuilder/BaseNoValidation.tpl';
		}
		return parent::getOperatorTemplateName($operator);
	}
}
