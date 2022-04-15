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

class Vtiger_Boolean_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		if ('on' === $value || 1 === (int) $value) {
			return 1;
		}
		return 0;
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		if (!\in_array($value, [0, 1, '1', '0', 'on', 'off'])) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (null !== $value && (1 === $value || '1' === $value || 'on' === strtolower($value) || 'yes' === strtolower($value) || true === $value)) {
			return App\Language::translate('LBL_YES', $this->getFieldModel()->getModuleName());
		}
		if (null !== $value && (0 === $value || '0' === $value || 'off' === strtolower($value) || 'no' === strtolower($value) || false === $value)) {
			return App\Language::translate('LBL_NO', $this->getFieldModel()->getModuleName());
		}
		return '';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Boolean.tpl';
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Boolean.tpl';
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['tinyint', 'smallint'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny', 'ef', 'nf'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/Boolean.tpl';
	}
}
