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

class Vtiger_Phone_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return '';
		}
		if (\Config\Main::$phoneFieldAdvancedVerification ?? false) {
			$value = str_replace(' ', '', $value);
		}
		return \App\Purifier::decodeHtml($value);
	}

	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		return $this->getDBValue($value);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (\Config\Main::$phoneFieldAdvancedVerification ?? false) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$phoneUtil->isValidNumber($phoneUtil->parse($value));
			} catch (\libphonenumber\NumberParseException $e) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
			}
			$this->validate[$value] = true;
		} else {
			parent::validate($value, $isUserFormat);
		}
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$extra = '';
		$href = $international = \App\Purifier::encodeHtml($value);
		if ((\Config\Main::$phoneFieldAdvancedVerification ?? false) && ($format = \App\Config::main('phoneFieldAdvancedHrefFormat', \libphonenumber\PhoneNumberFormat::RFC3966)) !== false) {
			if ($recordModel) {
				$extra = $recordModel->getDisplayValue($this->getFieldModel()->getName() . '_extra');
				if ($extra) {
					$extra = ' ' . $extra;
				}
			}
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$swissNumberProto = $phoneUtil->parse($value);
				$international = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
				$href = $phoneUtil->format($swissNumberProto, $format);
			} catch (\libphonenumber\NumberParseException $e) {
			}
			if (\libphonenumber\PhoneNumberFormat::RFC3966 !== $format) {
				$href = 'tel:' . $href;
			}
		} else {
			$href = 'tel:' . $href;
		}
		if ($rawText) {
			return $international . $extra;
		}
		if (!\App\Integrations\Pbx::isActive()) {
			return '<a href="' . $href . '">' . $international . $extra . '</a>';
		}
		return '<a class="phoneField" onclick="Vtiger_Index_Js.performPhoneCall(\'' . preg_replace('/(?<!^)\+|[^\d+]+/', '', $international) . '\',' . $record . ')"><span class="fas fa-phone" aria-hidden="true"></span> ' . $international . $extra . '</a>';
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$href = $international = ($value ? \App\Purifier::encodeHtml($value) : '');
		if ((\Config\Main::$phoneFieldAdvancedVerification ?? false) && ($format = \App\Config::main('phoneFieldAdvancedHrefFormat', \libphonenumber\PhoneNumberFormat::RFC3966)) !== false) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$swissNumberProto = $phoneUtil->parse($value);
				$international = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
				$href = $phoneUtil->format($swissNumberProto, $format);
			} catch (\libphonenumber\NumberParseException $e) {
			}
			if (\libphonenumber\PhoneNumberFormat::RFC3966 !== $format) {
				$href = 'tel:' . $href;
			}
		} else {
			$href = 'tel:' . $href;
		}
		if ($rawText) {
			return $international;
		}
		if (!\App\Integrations\Pbx::isActive()) {
			return '<a href="' . $href . '">' . $international . '</a>';
		}
		return '<a class="phoneField" onclick="Vtiger_Index_Js.performPhoneCall(\'' . preg_replace('/(?<!^)\+|[^\d+]+/', '', $international) . '\',' . $record . ')"><span class="fas fa-phone" aria-hidden="true"></span> ' . $international . '</a>';
	}

	/** {@inheritdoc} */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		if (empty($value)) {
			return '';
		}
		$href = $international = \App\Purifier::encodeHtml($value);
		if ((\Config\Main::$phoneFieldAdvancedVerification ?? false) && ($format = \App\Config::main('phoneFieldAdvancedHrefFormat', \libphonenumber\PhoneNumberFormat::RFC3966)) !== false) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$swissNumberProto = $phoneUtil->parse($value);
				$international = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
				$href = $phoneUtil->format($swissNumberProto, $format);
			} catch (\libphonenumber\NumberParseException $e) {
				\App\Log::info($e->__toString(), __CLASS__);
			}
			if (\libphonenumber\PhoneNumberFormat::RFC3966 !== $format) {
				$href = 'tel:' . $href;
			}
		} else {
			$href = 'tel:' . $href;
		}
		return '<a href="' . $href . '">' . $international . '</a>';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Phone.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 's', 'ew', 'c', 'k', 'y', 'ny', 'ef', 'nf'];
	}

	/**
	 * Get phone details.
	 *
	 * @param string      $number
	 * @param string|null $country
	 *
	 * @return array
	 */
	public function getPhoneDetails(string $number, ?string $country): array
	{
		$details = [
			'rawNumber' => $number,
			'rawCountry' => $country,
			'fieldName' => $this->getFieldModel()->getName(),
		];
		if (\App\Config::main('phoneFieldAdvancedVerification', false)) {
			$phoneDetails = \App\Fields\Phone::getDetails($number, $country);
			if (isset($phoneDetails['number'])) {
				$details = array_merge($details, $phoneDetails);
			} else {
				$details['fieldName'] = $details['fieldName'] . '_extra';
				$details['number'] = $this->getDBValue($number);
			}
		}
		return $details;
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		if ('' === $value && null !== $defaultValue) {
			$value = $defaultValue;
		}
		if (\Config\Main::$phoneFieldAdvancedVerification ?? false) {
			$value = preg_replace('/[^+\d]/', '', $value);
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function delete()
	{
		$fieldModel = $this->getFieldModel();
		$fieldName = $fieldModel->getName();
		if ($extraFieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['fieldname' => "{$fieldName}_extra", 'tabid' => $fieldModel->getModuleId()])->scalar()) {
			\Settings_LayoutEditor_Field_Model::getInstance($extraFieldId)->delete();
		}

		parent::delete();
	}
}
