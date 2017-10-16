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
	 * {@inheritDoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel)
	{
		$fieldName = $this->get('field')->getFieldName();
		if ($this->get('field')->getUIType() === 300) {
			$value = $request->getForHtml($fieldName, '');
		} else {
			$value = $request->get($fieldName, '');
		}
		$this->validate($value);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_string($value)) {
			throw new \App\Exceptions\SaveRecord('ERR_ILLEGAL_FIELD_VALUE', 406);
		}
		//Check for HTML tags
		if ($this->getFieldModel()->getUIType() !== 300 && $value !== strip_tags($value)) {
			throw new \App\Exceptions\SaveRecord('ERR_ILLEGAL_FIELD_VALUE', 406);
		}
		$this->validate = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$uiType = $this->get('field')->get('uitype');
		if ($uiType === 300) {
			return App\Purifier::purifyHtml($value);
		} else {
			return nl2br(\App\Purifier::encodeHtml($value));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateName()
	{
		return 'uitypes/Text.tpl';
	}
}
