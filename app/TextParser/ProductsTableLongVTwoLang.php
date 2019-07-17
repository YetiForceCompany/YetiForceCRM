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
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$firstRow = current($inventoryRows);
		if ($inventory->isField('currency')) {
			if (!empty($firstRow) && null !== $firstRow['currency']) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		}
		$headerStyle = 'font-size:9px;padding:0px 4px;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$html .= '<table class="products-table-long-v-two-lang" style="border-collapse:collapse;width:100%;"><thead><tr>';
		$groupModels = [];
		foreach (['Name', 'Quantity', 'Discount', 'Currency', 'DiscountMode', 'TaxMode', 'UnitPrice', 'GrossPrice', 'NetPrice', 'Tax', 'TotalPrice', 'Value'] as $fieldType) {
			foreach ($inventory->getFieldsByType($fieldType) as $fieldModel) {
				$columnName = $fieldModel->getColumnName();
				if (!$inventory->isField($columnName) && !$fieldModel) {
					continue;
				}
				if ($fieldModel->isVisible() && 'subunit' !== $fieldModel->getColumnName()) {
					$html .= "<th class=\"col-type-{$fieldModel->getType()}\" style=\"{$headerStyle}text-align:center;\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . ' / ' . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName, \App\Language::DEFAULT_LANG) . '</th>';
				}
				$groupModels[$columnName] = $fieldModel;
			}
		}
		$html .= '</tr></thead>';
		if (!empty($groupModels)) {
			$html .= '<tbody>';
			$counter = 0;
			foreach ($inventoryRows as $inventoryRow) {
				++$counter;
				$html .= '<tr class="row-' . $counter . '">';
				foreach ($groupModels as $fieldModel) {
					$typeName = $fieldModel->getType();
					$columnName = $fieldModel->getColumnName();
					$fieldStyle = $bodyStyle;
					if ($fieldModel->isVisible() && 'subunit' !== $fieldModel->getColumnName()) {
						if ('ItemNumber' === $typeName) {
							$fieldValue = $inventoryRow['seq'];
						} elseif ('ean' === $columnName) {
							$code = $inventoryRow[$columnName];
							$fieldValue = " <div data-barcode=\"EAN13\" data-code=\"$code\" data-size=\"1\" data-height=\"16\"></div>";
						} else {
							$itemValue = $inventoryRow[$columnName];
							if ('Name' === $typeName) {
								$fieldValue = '<strong>' . $fieldModel->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
								foreach ($inventory->getFieldsByType('Comment') as $commentField) {
									if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()]) && $comment = $commentField->getDisplayValue($value, $inventoryRow)) {
										$fieldValue .= '<br />' . $comment;
									}
								}
							} elseif (\in_array($typeName, ['TotalPrice', 'Purchase', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Discount', 'Margin', 'Tax']) && !empty($currencySymbol)) {
								$fieldValue = \CurrencyField::appendCurrencySymbol($fieldModel->getDisplayValue($itemValue, $inventoryRow), $currencySymbol);
								$fieldStyle = $bodyStyle . 'text-align:right;';
							} else {
								$fieldValue = $fieldModel->getDisplayValue($itemValue, $inventoryRow);
							}
							$html .= "<td class=\"col-type-{$typeName}\" style=\"{$fieldStyle}\">" . $fieldValue . '</td>';
						}
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr>';
			foreach ($groupModels as $fieldModel) {
				if ($fieldModel->isVisible() && 'subunit' !== $fieldModel->getColumnName()) {
					$html .= "<th class=\"col-type-{$fieldModel->getType()}\" style=\"{$headerStyle}text-align:right;\">";
					if ($fieldModel->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $inventoryRow) {
							$sum += $inventoryRow[$fieldModel->getColumnName()];
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
