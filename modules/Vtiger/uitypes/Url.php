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

class Vtiger_Url_UIType extends Vtiger_Base_UIType
{
	/**
	 * Allowed url protocols.
	 *
	 * @var array string[]
	 */
	const ALLOWED_PROTOCOLS = ['http', 'https', 'ftp', 'ftps', 'telnet'];

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		$value = $request->getRaw($requestFieldName);
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		$maximumLength = $this->getFieldModel()->getMaxValue();
		if ($maximumLength && App\TextUtils::getTextLength($value) > $maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		if (!($scheme = parse_url($value, PHP_URL_SCHEME))) {
			$scheme = 'http';
			$value = "{$scheme}://{$value}";
		}
		if (!(preg_match('/^([^\:]+)\:/i', $value) && \App\Validator::url($value) && \in_array(strtolower($scheme), static::ALLOWED_PROTOCOLS))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		return $value ? $value : '';
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (empty($value)) {
			return '';
		}
		$rawValue = $value;
		$value = \App\Purifier::encodeHtml($value);
		preg_match('^[\\w]+:\\/\\/^', $value, $matches);
		if (empty($matches[0])) {
			$value = 'http://' . $value;
		}
		if ($rawText) {
			return $value;
		}
		$rawValue = \App\TextUtils::textTruncate($rawValue, \is_int($length) ? $length : false);

		return '<a class="urlField u-cursor-pointer" title="' . $value . '" href="' . $value . '" target="_blank" rel="noreferrer noopener">' . \App\Purifier::encodeHtml($rawValue) . '</a>';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Url.tpl';
	}
}
