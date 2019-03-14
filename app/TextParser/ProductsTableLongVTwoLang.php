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
			if (!empty($firstRow) && $firstRow['currency'] !== null) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		}
		if (!empty($fields[1])) {
			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$fieldsWithCurrency = ['TotalPrice', 'Purchase', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Discount', 'Margin', 'Tax'];
			$html .= '<table style="border-collapse:collapse;width:100%;"><thead><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && $field->getColumnName() !== 'subunit') {
					$html .= '<th style="padding:0px 4px;text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . ' / ' . \App\Language::translate($field->get('label'), $this->textParser->moduleName, \App\Language::DEFAULT_LANG) . '</th>';
				}
			}
			$html .= '</tr></thead><tbody>';
			foreach ($inventoryRows as $inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if (!$field->isVisible() || $field->getColumnName() === 'subunit') {
						continue;
					}
					if ($field->getType() === 'ItemNumber') {
						$html .= '<td style="padding:0px 4px;text-align:center;font-weight:bold;">' . $inventoryRow['seq'] . '</td>';
					} elseif ($field->getColumnName() === 'ean') {
						$code = $inventoryRow[$field->getColumnName()];
						$html .= '<td style="padding:0px 4px;text-align:center;"><div data-barcode="EAN13" data-code="' . $code . '" data-size="1" data-height="16"></div></td>';
					} elseif ($field->isVisible()) {
						$itemValue = $inventoryRow[$field->getColumnName()];
						$html .= '<td style="font-size:8px;border:1px solid #ddd;padding:0px 4px;' . (in_array($field->getType(), $fieldsTextAlignRight) ? 'text-align:right;' : '') . '">';
						if ($field->getType() === 'Name') {
							$html .= '<strong>' . $field->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
							foreach ($inventory->getFieldsByType('Comment') as $commentField) {
								if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()])) {
									$comment = $commentField->getDisplayValue($value, $inventoryRow);
									if ($comment) {
										$html .= '<br />' . $comment;
									}
								}
							}
						} elseif (\in_array($field->getType(), $fieldsWithCurrency, true) && !empty($currencySymbol)) {
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
					$html .= '<th style="padding:0px 4px;text-align:right;">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $inventoryRow) {
							$sum += $inventoryRow[$field->getColumnName()];
						}
						if (!empty($currencySymbol)) {
							$html .= \CurrencyField::appendCurrencySymbol(\CurrencyField::convertToUserFormat($sum, null, true), $currencySymbol);
						} else {
							$html .= \CurrencyField::convertToUserFormat($sum, null, true);
						}
					}
					$html .= '</th>';
				}
			}
			$html .= '</tr></tfoot></table>';
		}
		return $html;
	}
}
