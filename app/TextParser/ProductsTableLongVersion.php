<?php

namespace App\TextParser;

/**
 * Products table long version class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class ProductsTableLongVersion extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_LONG_VERSION';

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
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$firstRow = current($inventoryRows);
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		if ($inventory->isField('currency')) {
			if (!empty($firstRow) && null !== $firstRow['currency']) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		}

		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$html .= '<table class="products-table-long-version" style="width:100%;font-size:8px;border-collapse:collapse;">
				<thead>
					<tr>';
		$groupModels = [];
		foreach (['Name', 'Value', 'Quantity', 'UnitPrice', 'TotalPrice', 'Discount', 'NetPrice', 'Tax', 'GrossPrice']  as $fieldType) {
			foreach ($inventory->getFieldsByType($fieldType) as $fieldModel) {
				$columnName = $fieldModel->getColumnName();
				if (!$fieldModel || !$inventory->isField($columnName)) {
					continue;
				}
				if ($fieldModel->isVisible()) {
					$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . '</th>';
				}
				$groupModels[$columnName] = $fieldModel;
			}
		}
		$html .= '</tr></thead>';
		if ($groupModels) {
			$html .= '<tbody>';
			$counter = 0;
			foreach ($inventoryRows as $inventoryRow) {
				++$counter;
				$html .= '<tr class="row-' . $counter . '">';
				foreach ($groupModels as $fieldModel) {
					$columnName = $fieldModel->getColumnName();
					$typeName = $fieldModel->getType();
					$fieldStyle = $bodyStyle;
					if ($fieldModel->isVisible()) {
						if ('ItemNumber' === $typeName) {
							$fieldValue = $inventoryRow[$columnName];
						} elseif ('ean' === $columnName) {
							$fieldValue = '<div data-barcode="EAN13" data-code="' . $inventoryRow[$columnName] . '" data-size="1" data-height="16"></div>';
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
				if ($fieldModel->isVisible()) {
					$html .= "<td class=\"col-type-{$typeName}\" style=\"{$bodyStyle}text-align:right;background:#dbdbdb;font-weight: 700;\">";
					if ($fieldModel->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $inventoryRow) {
							$sum += $inventoryRow[$fieldModel->getColumnName()];
						}
						$html .= \CurrencyField::appendCurrencySymbol(\CurrencyField::convertToUserFormat($sum, null, true), $currencySymbol);
					}
					$html .= '</td>';
				}
			}
			$html .= '</tr></tfoot></table>';
		}
		return $html;
	}
}
