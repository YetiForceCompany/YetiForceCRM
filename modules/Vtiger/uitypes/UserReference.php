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

class Vtiger_UserReference_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_numeric($value) || !\App\User::isExists($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
		}
		$maximumLength = $this->getFieldModel()->get('maximumlength');
		if ($maximumLength) {
			$rangeValues = explode(',', $maximumLength);
			if (($rangeValues[1] ?? $rangeValues[0]) < $value || (isset($rangeValues[1]) ? $rangeValues[0] : 0) > $value) {
				throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getFieldName() . '||' . $value, 406);
			}
		}
		$this->validate = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if ($value) {
			return \App\Fields\Owner::getLabel($value);
		}

		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$displayValue = \App\TextParser::textTruncate($this->getEditViewDisplayValue($value, $recordModel), is_int($length) ? $length : false);
		if (App\User::getCurrentUserModel()->isAdmin() && !$rawText) {
			$recordModel = Users_Record_Model::getCleanInstance('Users');
			$recordModel->setId($value);

			return '<a href="' . $recordModel->getDetailViewUrl() . '">' . $displayValue . '</a>';
		}

		return $displayValue;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Reference.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return ['integer', 'smallint'];
	}
}
