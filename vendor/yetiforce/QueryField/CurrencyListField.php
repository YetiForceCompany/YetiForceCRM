<?php
namespace App\QueryField;

/**
 * CurrencyList Query Field Class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class CurrencyListField extends PicklistField
{

	/**
	 * Get order by
	 * @param string $order
	 * @return array
	 */
	public function getOrderBy($order = false)
	{
		$this->queryGenerator->addJoin(['LEFT JOIN', 'vtiger_currency_info', $this->getColumnName() . ' = vtiger_currency_info.id']);
		if ($order && strtolower($order) === 'desc') {
			return ['vtiger_currency_info.currency_name' => SORT_DESC];
		} else {
			return ['vtiger_currency_info.currency_name' => SORT_ASC];
		}
	}
}
