<?php

namespace App\TextParser;

/**
 * Products table long two lang class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class ProductsTableLongVTwoLang extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_LONG_TWO_LANG';

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
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$firstRow = current($inventoryRows);

		if ($inventory->isField('currency')) {
			if (!empty($firstRow) > 0 && $firstRow['currency'] !== null) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		}
		if (!empty($fields[1])) {
			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table style="border-collapse:collapse;width:100%;"><thead><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && ($field->getColumnName() !== 'subunit')) {
					if ($field->getType() === 'Quantity' || $field->getType() === 'Value') {
						$html .= '<th style="text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . ' / ' . \App\Language::translate($field->get('label'), $this->textParser->moduleName, 'en_us') . '</th>';
					} elseif ($field->getType() === 'Name') {
						$html .= '<th style="text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . ' / ' . \App\Language::translate($field->get('label'), $this->textParser->moduleName, 'en_us') . '</th>';
					} else {
						$html .= '<th style="text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . ' / ' . \App\Language::translate($field->get('label'), $this->textParser->moduleName, 'en_us') . '</th>';
					}
				}
			}
			$html .= '</tr></thead><tbody>';
			foreach ($inventoryRows as $inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if (!$field->isVisible() || ($field->getColumnName() === 'subunit')) {
						continue;
					}
					if ($field->getType() === 'ItemNumber') {
						$html .= '<td><strong>' . $inventoryRow['seq'] . '</strong></td>';
					} elseif ($field->getColumnName() === 'ean') {
						$code = $inventoryRow[$field->getColumnName()];
						$html .= '<td><barcode code="' . $code . '" type="EAN13" size="0.5" height="0.5" class="barcode" /></td>';
					} elseif ($field->isVisible()) {
						$itemValue = $inventoryRow[$field->getColumnName()];
						$html .= '<td style="font-size:8px" class="' . (in_array($field->getType(), $fieldsTextAlignRight) ? 'textAlignRight ' : '') . 'tBorder">';
						if ($field->getType() === 'Name') {
							$html .= '<strong>' . $field->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
							foreach ($inventory->getFieldsByType('Comment') as $commentField) {
								if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()])) {
									$html .= '<br />' . $commentField->getDisplayValue($value, $inventoryRow);
								}
							}
						} elseif (\in_array($field->getType(), $fieldsWithCurrency, true)) {
							$html .= $field->getDisplayValue($itemValue, $inventoryRow) . ' ' . $currencySymbol;
						} else {
							$html .= $field->getDisplayValue($itemValue, $inventoryRow);
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && ($field->getColumnName() !== 'subunit')) {
					$html .= '<th style="text-align:right;">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $inventoryRow) {
							$sum += $inventoryRow[$field->getColumnName()];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true) . ' ' . $currencySymbol;
					}
					$html .= '</th>';
				}
			}
			$html .= '</tr></tfoot></table>';
		}
		return $html;
	}
}
