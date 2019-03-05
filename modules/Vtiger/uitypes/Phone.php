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
		if (\App\Config::main('phoneFieldAdvancedVerification', false)) {
			$value = str_replace(' ', '', $value);
		}
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		return $this->getDBValue($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (\App\Config::main('phoneFieldAdvancedVerification', false)) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$phoneUtil->isValidNumber($phoneUtil->parse($value));
			} catch (\libphonenumber\NumberParseException $e) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
			$this->validate[$value] = true;
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
		$rfc3966 = $international = \App\Purifier::encodeHtml($value);
		if (\App\Config::main('phoneFieldAdvancedVerification', false)) {
			if ($recordModel) {
				$extra = $recordModel->getDisplayValue($this->getFieldModel()->getFieldName() . '_extra');
				if ($extra) {
					$extra = ' ' . $extra;
				}
			}
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$swissNumberProto = $phoneUtil->parse($value);
				$international = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
				$rfc3966 = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::RFC3966);
			} catch (\libphonenumber\NumberParseException $e) {
			}
		} else {
			$rfc3966 = 'tel:' . $rfc3966;
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
		$rfc3966 = $international = \App\Purifier::encodeHtml($value);
		if (\App\Config::main('phoneFieldAdvancedVerification', false)) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$swissNumberProto = $phoneUtil->parse($value);
				$international = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
				$rfc3966 = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::RFC3966);
			} catch (\libphonenumber\NumberParseException $e) {
			}
		} else {
			$rfc3966 = 'tel:' . $rfc3966;
		}
		if ($rawText) {
			return $international;
		}
		if (!\App\Integrations\Pbx::isActive()) {
			return '<a href="' . $rfc3966 . '">' . $international . '</a>';
		}
		return '<a class="phoneField" onclick="Vtiger_Index_Js.performPhoneCall(\'' . preg_replace('/(?<!^)\+|[^\d+]+/', '', $international) . '\',' . $record . ')"><span class="fas fa-phone" aria-hidden="true"></span> ' . $international . '</a>';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Phone.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny'];
	}
}
