<?php

namespace App\QueryField;

/**
 * String Query Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class TreeField extends StringField
{
	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		if (strpos($this->value, '##') === false) {
			return $this->value;
		}
		return explode('##', $this->value);
	}

	/**
	 * Get order by.
	 *
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		$this->queryGenerator->addJoin(['LEFT JOIN', 'vtiger_trees_templates_data', $this->getColumnName() . ' =  vtiger_trees_templates_data.tree AND vtiger_trees_templates_data.templateid = :template', [':template' => $this->fieldModel->getFieldParams()]]);
		if ($order && strtoupper($order) === 'DESC') {
			return ['vtiger_trees_templates_data.name' => SORT_DESC];
		} else {
			return ['vtiger_trees_templates_data.name' => SORT_ASC];
		}
	}
}
