<?php

/**
 * UIType User Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_UserCreator_UIType extends Vtiger_Reference_UIType
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
	public function getDBValue($value, $recordModel = false)
	{
		return \App\User::getCurrentUserRealId();
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
		return \App\Fields\Owner::getLabel($value);
	}

	/** {@inheritdoc} */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Owner.tpl';
	}

	/** {@inheritdoc} */
	public function getReferenceModule($record): ?Vtiger_Module_Model
	{
		return Vtiger_Module_Model::getInstance('Users');
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny', 'om', 'nco'];
	}

	/** {@inheritdoc} */
	public function getOperatorTemplateName(string $operator = '')
	{
		return 'ConditionBuilder/Owner.tpl';
	}
}
