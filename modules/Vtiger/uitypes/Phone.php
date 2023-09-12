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
		if (\App\Config::component('Phone', 'advancedVerification', false)) {
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
		if (\App\Config::component('Phone', 'advancedVerification', false)) {
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
		$title = $extra = '';
		$href = $international = ($value ? \App\Purifier::encodeHtml($value) : '');
		if (\App\Config::component('Phone', 'advancedVerification', false)
			&& ($format = \App\Config::component('Phone', 'advancedFormat', \libphonenumber\PhoneNumberFormat::INTERNATIONAL)) !== false) {
			if ($recordModel && $recordModel->get($this->getFieldModel()->getName() . '_extra')) {
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

				$title .= ' ' . \libphonenumber\geocoding\PhoneNumberOfflineGeocoder::getInstance()
					->getDescriptionForNumber($swissNumberProto, \App\Language::getLanguage());
				$title .= ' ' . \libphonenumber\PhoneNumberToCarrierMapper::getInstance()
					->getNameForValidNumber($swissNumberProto, \App\Language::getShortLanguageName());
			} catch (\libphonenumber\NumberParseException $e) {
			}
			if (\libphonenumber\PhoneNumberFormat::RFC3966 !== $format) {
				$href = 'tel:' . $href;
			}
		} else {
			$href = 'tel:' . $href;
		}
		$label = $international . $extra;
		if ($rawText || (empty($international))) {
			return $label;
		}
		if (!\App\Integrations\Pbx::isActive()) {
			return '<a href="' . $href . '" class="js-popover-tooltip" title="' . $label . ' ' . trim($title) . '">' . $international . '</a>' . $extra;
		}
		$button = "<button type=\"button\" class=\"btn btn-primary btn-xs float-right clipboard\" data-copy-attribute=\"clipboard-text\" data-clipboard-text=\"{$international}\" title=\"" . \App\Language::translate('BTN_COPY_TO_CLIPBOARD', $recordModel->getModuleName()) . '"><span class="fa-regular fa-copy"></span></button>';
		$data = 'data-phone="' . preg_replace('/(?<!^)\+|[^\d+]+/', '', $international) . '"';
		if ($record) {
			$data .= ' data-record="' . $record . '"';
		}
		$data .= ' title="' . $label . ' ' . trim($title) . '"';
		return '<a class="u-cursor-pointer js-phone-perform-call js-popover-tooltip" ' . $data . ' data-js="click|container"><span class="fas fa-phone" aria-hidden="true"></span> ' . $label . '</a>' . $button;
	}

	/** {@inheritdoc} */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		if (empty($value)) {
			return '';
		}
		$href = $international = \App\Purifier::encodeHtml($value);
		if (\App\Config::component('Phone', 'advancedVerification', false) && ($format = \App\Config::component('Phone', 'advancedFormat', \libphonenumber\PhoneNumberFormat::RFC3966)) !== false) {
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
		if (\App\Config::component('Phone', 'advancedVerification', false)) {
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
		if (\App\Config::component('Phone', 'advancedVerification', false)) {
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
