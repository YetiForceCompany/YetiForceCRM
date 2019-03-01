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

class Vtiger_Boolean_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if ($value === 'on' || (int) $value === 1) {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!in_array($value, [0, 1, '1', '0', 'on'])) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($value === 1 || $value === '1' || strtolower($value) === 'on' || strtolower($value) === 'yes' || true === $value) {
			return App\Language::translate('LBL_YES', $this->getFieldModel()->getModuleName());
		} elseif ($value === 0 || $value === '0' || strtolower($value) === 'off' || strtolower($value) === 'no' || false === $value) {
			return App\Language::translate('LBL_NO', $this->getFieldModel()->getModuleName());
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Boolean.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Boolean.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return ['tinyint', 'smallint'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['e', 'n', 'y', 'ny'];
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
		return 'ConditionBuilder/Boolean.tpl';
	}
}
