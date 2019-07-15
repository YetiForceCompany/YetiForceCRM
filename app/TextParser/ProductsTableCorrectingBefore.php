<?php

namespace App\TextParser;

/**
 * Products table correcting before class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		}
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$html .= '<table class="products-table-correcting-before" style="border-collapse:collapse;width:100%"><thead><tr>';
		$fieldName = [];
		foreach (['currency', 'discountmode', 'taxmode', 'name', 'qty', 'discount', 'marginp', 'margin', 'tax', 'comment1', 'price', 'total', 'net', 'purchase', 'gross', 'unit', 'subunit', 'ean', 'seq'] as $field) {
			$fieldModel = $inventory->getField($field);
			if (!$fieldModel || !$inventory->isField($field)) {
				continue;
			}
			if (\in_array($field, ['currency', 'discountmode', 'taxmode'])) {
				$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . ': ' . $fieldModel->getDisplayValue($firstRow[$fieldModel->getColumnName()]) . '</th>';
			} else {
				$fieldName[$field] = $fieldModel;
			}
		}
		$html .= '</tr></thead></table>';
		$html .= '<table class="products-table-header" style="border-collapse:collapse;width:100%;"><thead><tr>';
		foreach ($fieldName as $field) {
			if ($field->isVisible() && 'comment1' !== $field->getColumnName()) {
				$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
			}
		}
		$html .= '</tr></thead><tbody>';
		$counter = 1;
		foreach ($inventoryRows as $inventoryRow) {
			$html .= '<tr>';
			foreach ($fieldName as $field) {
				$name = $field->getColumnName();
				if ($field->isVisible() && 'comment1' !== $name) {
					if ('seq' === $name) {
						$html .= "<td style=\"{$bodyStyle}font-weight:bold;\">" . $counter++ . '</td>';
					} elseif ('ean' === $name) {
						$code = $inventoryRow[$name];
						$html .= "<td style=\"{$bodyStyle}\"><div data-barcode=\"EAN13\" data-code=\"{$code}\" data-size=\"1\" data-height=\"16\"></div></td>";
					} else {
						$itemValue = $inventoryRow[$name];
						$styleRight = $bodyStyle . ' text-align:right;';
						$html .= '<td ';
						if ('name' === $name) {
							$html .= "style=\"{$bodyStyle}\"><strong>" . $field->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
							foreach ($inventory->getFieldsByType('Comment') as $commentField) {
								if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()]) && $comment = $commentField->getDisplayValue($value, $inventoryRow)) {
									$html .= '<br />' . $comment;
								}
							}
						} elseif (\in_array($name, ['price', 'purchase', 'net', 'gross', 'UnitPrice', 'discount', 'margin', 'tax'], true)) {
							$html .= "style=\"{$styleRight}\">" . \CurrencyField::appendCurrencySymbol($field->getDisplayValue($itemValue, $inventoryRow), $currencySymbol);
						} else {
							$html .= "style=\"{$styleRight}\">" . $field->getDisplayValue($itemValue, $inventoryRow);
						}
						$html .= '</td>';
					}
				}
			}
			$html .= '</tr>';
		}
		$html .= '</tbody><tfoot><tr>';
		foreach ($fieldName as $field) {
			$name = $field->getColumnName();
			if ($field->isVisible() && 'comment1' !== $name) {
				$html .= "<th style=\"{$headerStyle}\">";
				if ($field->isSummary()) {
					$sum = 0;
					foreach ($inventoryRows as $inventoryRow) {
						$sum += $inventoryRow[$name];
					}
					$html .= \CurrencyField::appendCurrencySymbol(\CurrencyField::convertToUserFormat($sum, null, true), $currencySymbol);
				}
				$html .= '</th>';
			}
		}
		$html .= '</tr></tfoot></table>';
		return $html;
	}
}
