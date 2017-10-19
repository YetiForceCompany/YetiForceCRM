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

class Vtiger_Url_UIType extends Vtiger_Base_UIType
{

	/**
	 * Allowed url protocols
	 * @var array string[]
	 */
	const ALLOWED_PROTOCOLS = ['http', 'https', 'ftp', 'ftps', 'telnet'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!preg_match('/^([^\:]+)\:/i', $value, $m)) {
			throw new \App\Exceptions\SaveRecord('ERR_ILLEGAL_FIELD_VALUE||' . $this->get('field')->getFieldName() . '||' . $value, 406);
		}
		if (!(filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower($m[1]), static::ALLOWED_PROTOCOLS) )) {
			throw new \App\Exceptions\SaveRecord('ERR_ILLEGAL_FIELD_VALUE||' . $this->get('field')->getFieldName() . '||' . $value, 406);
		}
		$this->validate = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$rawValue = $value;
		$value = \App\Purifier::encodeHtml($value);
		preg_match("^[\w]+:\/\/^", $rawValue, $matches);
		if (!empty($matches[0])) {
			$value = 'http://' . $value;
		}
		return '<a class="urlField cursorPointer" title="' . $value . '" href="' . $value . '" target="_blank" rel="noreferrer">' . \App\Purifier::encodeHtml(\vtlib\Functions::textLength($rawValue)) . '</a>';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$rawValue = $value;
		$value = \App\Purifier::encodeHtml($value);
		preg_match("^[\w]+:\/\/^", $rawValue, $matches);
		if (!empty($matches[0])) {
			$value = 'http://' . $value;
		}
		return '<a class="urlField cursorPointer" title="' . $value . '" href="' . $value . '" target="_blank" rel="noreferrer">' . \App\Purifier::encodeHtml(\vtlib\Functions::textLength($rawValue, $this->get('field')->get('maxlengthtext'))) . '</a>';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateName()
	{
		return 'uitypes/Url.tpl';
	}
}
