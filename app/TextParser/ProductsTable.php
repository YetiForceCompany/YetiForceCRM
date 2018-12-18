<?php

namespace App\TextParser;

/**
 * Products table class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ProductsTable extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		if (!$this->textParser->recordModel->getModule()->isInventory()) {
			return $html;
		}
		$inventory = \Vtiger_Inventory_Model::getInstance($this->textParser->moduleName);
		$fields = $inventory->getFieldsByBlocks();
		if (isset($fields[0])) {
			$inventoryRows = $this->textParser->recordModel->getInventoryData();
			$firstRow = current($inventoryRows);
			$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
			if ($inventory->isField('currency')) {
				$currency = $inventoryRows && $firstRow['currency'] ? $firstRow['currency'] : $baseCurrency['id'];
				$currencyData = \App\Fields\Currency::getById($currency);
			}
		}
		if (isset($fields[0])) {
			$html .= '<table style="border-collapse:collapse;width:100%;">
				<thead>
					<tr>
						<th></th>';
			foreach ($fields[0] as $field) {
				$html .= '<th>
								<span>' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . ':</span> ';
				switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
					case 'DetailViewBase.tpl':
						$html .= $field->getDisplayValue($firstRow[$field->getColumnName()]);
						break;
					case 'DetailViewTaxMode.tpl':
					case 'DetailViewDiscountMode.tpl':
						$html .= \App\Language::translate($field->getDisplayValue($firstRow[$field->getColumnName()]), $this->textParser->moduleName);
						break;
					default:
						break;
				}
				$html .= '</th>';
			}
			$html .= '</tr>
				</thead>
			</table>';

			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table style="border-collapse:collapse;width:100%;">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<th style="padding:0px 4px;text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';

			foreach ($inventoryRows as $key => &$inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if ($field->isVisible()) {
						$itemValue = $inventoryRow[$field->getColumnName()];
						$html .= '<td style="padding:0px 4px;border:1px solid #ddd;' . (in_array($field->getType(), $fieldsTextAlignRight) ? 'text-align:right;' : '') . '">';
						switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue, [], true) . '</strong>';
								break;
							case 'DetailViewBase.tpl':
								$html .= $field->getDisplayValue($itemValue);
								break;
							default:
								break;
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody>
					<tfoot>
						<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<th style="padding:0px 4px;text-align:right;">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $key => &$inventoryRow) {
							$sum += $inventoryRow[$field->getColumnName()];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true);
					}
					$html .= '</th>';
				}
			}
			$html .= '</tr>
					</tfoot>
				</table>';
		}
		return $html;
	}
}
