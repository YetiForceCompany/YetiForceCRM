<?php
namespace App\QueryField;

/**
 * String Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class TreeField extends StringField
{

	/**
	 * Get value
	 * @return mixed
	 */
	public function getValue()
	{
		return explode(',', $this->value);
	}

	/**
	 * Get order by
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
