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

class Vtiger_Theme_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDbConditionBuilderValue($value, string $operator)
	{
		$values = [];
		if (!\is_array($value)) {
			$value = $value ? explode('##', $value) : [];
		}
		foreach ($value as $val) {
			$values[] = parent::getDbConditionBuilderValue($val, $operator);
		}
		return implode('##', $values);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		$allSkins = Vtiger_Theme::getAllSkins();
		if (!isset($allSkins[$value])) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if ($rawText) {
			return $value;
		}
		$skinColor = Vtiger_Theme::getAllSkins()[$value] ?? '';
		$value = \App\Utils::mbUcfirst($value);
		return "<div style='width: 24px; height: 24px; background-color:$skinColor;' title='$value'>&nbsp;</div>";
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/Theme.tpl';
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/Theme.tpl';
	}
}
