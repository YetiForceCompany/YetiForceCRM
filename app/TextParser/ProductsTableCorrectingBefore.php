<?php

namespace App\TextParser;

/**
 * Products table correcting before class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class ProductsTableCorrectingBefore extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_CORRECTING_BEFORE';

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
		$beforeRecordModel = \Vtiger_Record_Model::getInstanceById($this->textParser->recordModel->get('finvoiceid'));
		$inventory = \Vtiger_Inventory_Model::getInstance($beforeRecordModel->getModuleName());
		$inventoryRows = $beforeRecordModel->getInventoryData();
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$firstRow = current($inventoryRows);
		if ($inventory->isField('currency')) {
			if (!empty($firstRow) && null !== $firstRow['currency']) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencySymbol = \App\Fields\Currency::getById($currency)['currency_symbol'];
		} else {
			$currencySymbol = \App\Fields\Currency::getDefault()['currency_symbol'];
		}
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;background-color:#ddd;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$html .= '<table class="products-table-correcting-before" style="border-collapse:collapse;width:100%"><thead><tr>';
		$groupModels = [];
		foreach (['Name', 'Quantity', 'UnitPrice', 'TotalPrice', 'Discount', 'NetPrice', 'Currency', 'DiscountMode', 'Tax', 'TaxMode', 'GrossPrice', 'Value'] as $fieldType) {
			foreach ($inventory->getFieldsByType($fieldType) as $fieldModel) {
				$columnName = $fieldModel->getColumnName();
				if (!$fieldModel->isVisible()) {
					continue;
				}
				if (\in_array($fieldModel->getType(), ['Currency', 'DiscountMode', 'TaxMode'])) {
					$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . ': ' . $fieldModel->getDisplayValue($firstRow[$columnName]) . '</th>';
				} else {
					$groupModels[$columnName] = $fieldModel;
				}
			}
		}
		$html .= '</tr></thead></table>';
		$html .= '<table class="products-table-header" style="border-collapse:collapse;width:100%;"><thead><tr>';
		foreach ($groupModels as $fieldModel) {
			$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . '</th>';
		}
		$html .= '</tr></thead><tbody>';
		$counter = 1;
		foreach ($inventoryRows as $inventoryRow) {
			$html .= '<tr>';
			foreach ($groupModels as $fieldModel) {
				$columnName = $fieldModel->getColumnName();
				$typeName = $fieldModel->getType();
				$styleField = $bodyStyle;
				if ('ItemNumber' === $typeName) {
					$html .= "<td style=\"{$bodyStyle}font-weight:bold;\">" . $counter++ . '</td>';
				} elseif ('ean' === $columnName) {
					$code = $inventoryRow[$columnName];
					$html .= "<td style=\"{$bodyStyle}\"><div data-barcode=\"EAN13\" data-code=\"{$code}\" data-size=\"1\" data-height=\"16\">{$code}</div></td>";
				} else {
					$itemValue = $inventoryRow[$columnName];
					if ('Name' === $typeName) {
						$fieldValue = '<strong>' . $fieldModel->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
						foreach ($inventory->getFieldsByType('Comment') as $commentField) {
							$commentFieldName = $commentField->getColumnName();
							if ($inventory->isField($commentFieldName) && $commentField->isVisible() && ($value = $inventoryRow[$commentFieldName]) && $comment = $commentField->getDisplayValue($value, $inventoryRow)) {
								$fieldValue .= '<br />' . $comment;
							}
						}
					} elseif (\in_array($typeName, ['TotalPrice', 'Purchase', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Discount', 'Margin', 'Tax'])) {
						$fieldValue = \CurrencyField::appendCurrencySymbol($fieldModel->getDisplayValue($itemValue, $inventoryRow), $currencySymbol);
						$styleField = $bodyStyle . ' text-align:right;';
					} else {
						$fieldValue = $fieldModel->getDisplayValue($itemValue, $inventoryRow);
					}
					$html .= "<td class=\"col-type-{$typeName}\" style=\"{$styleField}\">{$fieldValue}</td>";
				}
			}
			$html .= '</tr>';
		}
		$html .= '</tbody><tfoot><tr>';
		foreach ($groupModels as $fieldModel) {
			$html .= '<th style="font-size:9px;padding:0px 4px;text-align:right;background-color:#ddd;">';
			if ($fieldModel->isSummary()) {
				$sum = 0;
				foreach ($inventoryRows as $inventoryRow) {
					$sum += $inventoryRow[$fieldModel->getColumnName()];
				}
				$html .= \CurrencyField::appendCurrencySymbol(\CurrencyField::convertToUserFormat($sum, null, true), $currencySymbol);
			}
			$html .= '</th>';
		}
		$html .= '</tr></tfoot></table>';
		return $html;
	}
}
