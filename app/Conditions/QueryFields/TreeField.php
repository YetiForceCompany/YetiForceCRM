<?php

namespace App\Conditions\QueryFields;

/**
 * String Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class TreeField extends StringField
{
	/**
	 * Condition separator.
	 *
	 * @var string
	 */
	protected $conditionSeparator = '##';

	/**
	 * Separator.
	 *
	 * @var string
	 */
	protected $separator = ',';

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		if (false === strpos($this->value, '##')) {
			return $this->value;
		}
		return explode('##', $this->value);
	}

	/**
	 * Get order by.
	 *
	 * @param mixed $order
	 *
	 * @return array
	 */
	public function getOrderBy($order = false): array
	{
		$this->queryGenerator->addJoin(['LEFT JOIN', 'vtiger_trees_templates_data', $this->getColumnName() . ' =  vtiger_trees_templates_data.tree AND vtiger_trees_templates_data.templateid = :template', [':template' => $this->fieldModel->getFieldParams()]]);
		if ($order && 'DESC' === strtoupper($order)) {
			return ['vtiger_trees_templates_data.name' => SORT_DESC];
		}
		return ['vtiger_trees_templates_data.name' => SORT_ASC];
	}

	/**
	 * Contains hierarchy operator.
	 *
	 * @return array
	 */
	public function operatorCh()
	{
		$searchValue = \is_array($this->getValue()) ? implode($this->conditionSeparator, $this->getValue()) : $this->getValue();
		$fieldValue = \Settings_TreesManager_Record_Model::getChildren($searchValue, $this->fieldModel->getColumnName(), \Vtiger_Module_Model::getInstance($this->getModuleName()));
		$condition = ['or'];
		foreach (explode($this->conditionSeparator, $fieldValue) as $value) {
			array_push($condition, [$this->getColumnName() => $value], ['or like', $this->getColumnName(),
				[
					"%{$this->separator}{$value}{$this->separator}%",
					"{$value}{$this->separator}%",
					"%{$this->separator}{$value}",
				], false,
			]);
		}
		return $condition;
	}
}
