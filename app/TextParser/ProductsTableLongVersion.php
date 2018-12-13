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
		$fields = $inventory->getFieldsByBlocks();
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$firstRow = current($inventoryRows);
		if ($inventory->isField('currency')) {
			if (\count($firstRow) > 0 && $firstRow['currency'] !== null) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		}
		if (\count($fields[1])) {
			$fieldsTextAlignRight = ['Name', 'Value', 'Quantity', 'UnitPrice', 'TotalPrice', 'Discount', 'NetPrice', 'Tax', 'GrossPrice'];
			$html .= '<table style="width:100%;font-size:8px;border-collapse:collapse;">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && in_array($field->getType(), $fieldsTextAlignRight) && ($field->getColumnName() !== 'subunit')) {
					if ($field->getType() === 'Quantity' || $field->getType() === 'Value') {
						$html .= '<th class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
					} elseif ($field->getType() === 'Name') {
						$html .= '<th class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
					} else {
						$html .= '<th style="text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
					}
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';
			foreach ($inventoryRows as &$inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if (!$field->isVisible() || !in_array($field->getType(), $fieldsTextAlignRight) || ($field->getColumnName() === 'subunit')) {
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
						switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue, true) . '</strong>';
								foreach ($fields[2] as $commentKey => $value) {
									$COMMENT_FIELD = $fields[2][$commentKey];
									$html .= '<br />' . $COMMENT_FIELD->getDisplayValue($inventoryRow[$COMMENT_FIELD->getColumnName()]);
								}
								break;
							case 'DetailViewBase.tpl':
								if ($field->getColumnName() === 'Quantity' || $field->getColumnName() === 'Value') {
									$html .= $field->getDisplayValue($itemValue);
								} else {
									$html .= $field->getDisplayValue($itemValue) . ' ' . $currencySymbol;
								}
								break;
							default:
								break;
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr style="background-color:#ddd;">';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && in_array($field->getColumnName(), $fieldsTextAlignRight) && ($field->getColumnName() !== 'subunit')) {
					$html .= '<td class="textAlignRight ';
					if ($field->isSummary()) {
						$html .= 'summaryContainer';
					}
					$html .= '">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as &$inventoryRow) {
							$sum += $inventoryRow[$field->getColumnName()];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true) . ' ' . $currencySymbol;
					}
					$html .= '</td>';
				}
			}
			$html .= '</tr></tfoot></table>';
		}
		return $html;
	}
}
