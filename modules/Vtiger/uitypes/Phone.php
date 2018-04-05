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

class Vtiger_Phone_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return '';
		}
		if (AppConfig::main('phoneFieldAdvancedVerification', false)) {
			$value = str_replace(' ', '', $value);
		}

		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (AppConfig::main('phoneFieldAdvancedVerification', false)) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$phoneUtil->isValidNumber($phoneUtil->parse($value));
			} catch (\libphonenumber\NumberParseException $e) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
			}
			$this->validate = true;
		} else {
			parent::validate($value, $isUserFormat);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$extra = '';
		if ($recordModel) {
			$extra = $recordModel->getDisplayValue($this->getFieldModel()->getFieldName() . '_extra');
			if ($extra) {
				$extra = ' ' . $extra;
			}
		}
		$rfc3966 = $international = \App\Purifier::encodeHtml($value);
		if (AppConfig::main('phoneFieldAdvancedVerification', false)) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$swissNumberProto = $phoneUtil->parse($value);
				$international = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
				$rfc3966 = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::RFC3966);
			} catch (\libphonenumber\NumberParseException $e) {
			}
		}
		if ($rawText) {
			return $international . $extra;
		}
		if (!\App\Integrations\Pbx::isActive()) {
			return '<a href="' . $rfc3966 . '">' . $international . $extra . '</a>';
		}

		return '<a class="phoneField" onclick="Vtiger_Index_Js.performPhoneCall(\'' . preg_replace('/(?<!^)\+|[^\d+]+/', '', $international) . '\',' . $record . ')"><span class="fas fa-phone" aria-hidden="true"></span> ' . $international . $extra . '</a>';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$extra = '';
		if ($recordModel) {
			$extra = $recordModel->getDisplayValue($this->getFieldModel()->getFieldName() . '_extra');
			if ($extra) {
				$extra = ' ' . $extra;
			}
		}
		$rfc3966 = $international = \App\Purifier::encodeHtml($value);
		if (AppConfig::main('phoneFieldAdvancedVerification', false)) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$swissNumberProto = $phoneUtil->parse($value);
				$international = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
				$rfc3966 = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::RFC3966);
			} catch (\libphonenumber\NumberParseException $e) {
			}
		}
		if ($rawText) {
			return $international . $extra;
		}
		if (!\App\Integrations\Pbx::isActive()) {
			return '<a href="' . $rfc3966 . '">' . $international . $extra . '</a>';
		}

		return '<a class="phoneField" onclick="Vtiger_Index_Js.performPhoneCall(\'' . preg_replace('/(?<!^)\+|[^\d+]+/', '', $international) . '\',' . $record . ')"><span class="fas fa-phone" aria-hidden="true"></span> ' . $international . $extra . '</a>';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Phone.tpl';
	}
}
