<?php

namespace App\TextParser;

/**
 * Products table images class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class ProductsTableImages extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_IMAGES';

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
			if (!empty($firstRow) && null !== $firstRow['currency']) {
				$currency = $firstRow['currency'];
			} else {
				$currency = $baseCurrency['id'];
			}
			$currencyData = \App\Fields\Currency::getById($currency);
			$currencySymbol = $currencyData['currency_symbol'];
		}
		if (empty($fields[1])) {
			return '';
		}
		$fieldsColumnQuotes = ['Quantity', 'GrossPrice', 'Name', 'UnitPrice', 'TotalPrice'];
		$fieldsTextRight = ['Quantity', 'GrossPrice', 'UnitPrice', 'TotalPrice'];
		$fieldsWithCurrency = ['TotalPrice', 'GrossPrice', 'UnitPrice'];
		$displayFields = [];
		foreach ($fields[1] as $field) {
			if (!$field->isVisible() || !\in_array($field->getType(), $fieldsColumnQuotes)) {
				continue;
			}
			// header
			$item = [];
			$item['headerHtml'] = '<th class="col-type-' . $field->getType() . '" style="padding:0px 4px;text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
			$item['model'] = $field;
			// footer
			$footerHtml = '<th class="col-type-' . $field->getType() . '" style="padding:0px 4px;text-align:right;">';
			if ($field->isSummary()) {
				$sum = 0;
				foreach ($inventoryRows as $inventoryRow) {
					$sum += $inventoryRow[$field->getColumnName()];
				}
				$footerHtml .= \CurrencyField::appendCurrencySymbol(\CurrencyField::convertToUserFormat($sum, null, true), $currencySymbol);
			}
			$footerHtml .= '</th>';
			$item['footerHtml'] = $footerHtml;
			$displayFields[] = $item;
		}
		array_splice($displayFields, 1, 0, [[
			'headerHtml' => '<th style="padding:0px 4px;text-align:center;">' . \App\Language::translate('PLL_IMAGE', 'Settings:PDF') . '</th>',
			'model' => 'image',
			'footerHtml' => '<th></th>',
		]]);
		// content
		$displayRows = [];
		foreach ($inventoryRows as $inventoryRow) {
			$rowHtml = '';
			foreach ($displayFields as $field) {
				$fieldModel = $field['model'];
				if ('image' === $fieldModel) {
					$inventoryModel = \Vtiger_Record_Model::getInstanceById($inventoryRow['name']);
					$imageDataJson = $inventoryModel->get('imagename');
					$imageData = null;
					if ($imageDataJson) {
						$imageData = \App\Json::decode($imageDataJson);
					}
					$image = '';
					if (!empty($imageData) && !empty($imageData[0]['path'])) {
						$base64 = \App\Fields\File::getImageBaseData($imageData[0]['path']);
						$image = '<img src="' . $base64 . '" style="width:80px;height:auto;">';
					}
					$columnHtml = "<td class=\"col-type-image\" style=\"border:1px solid #ddd;padding:0px 4px;text-align:center;\">$image</td>";
				} elseif ('ItemNumber' === $fieldModel->getType()) {
					$columnHtml = '<td class="col-type-ItemNumber" style="padding:0px 4px;border:1px solid #ddd;font-weight:bold;">' . $inventoryRow['seq'] . '</td>';
				} elseif ('ean' === $fieldModel->getColumnName()) {
					$code = $inventoryRow[$fieldModel->getColumnName()];
					$columnHtml = '<td class="col-type-barcode"><div data-barcode="EAN13" data-code="' . $code . '" data-size="1" data-height="16"></div></td>';
				} else {
					$itemValue = $inventoryRow[$fieldModel->getColumnName()];
					$itemHtml = '<td class="col-type-' . $field->getType() . '" style="border:1px solid #ddd;padding:0px 4px;' . (\in_array($fieldModel->getType(), $fieldsTextRight) ? 'text-align:right;' : '') . '">';
					if ('Name' === $fieldModel->getType()) {
						$itemHtml .= '<strong>' . $fieldModel->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
						foreach ($inventory->getFieldsByType('Comment') as $commentField) {
							if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()])) {
								$comment = $commentField->getDisplayValue($value, $inventoryRow);
								if ($comment) {
									$itemHtml .= '<br />' . $comment;
								}
							}
						}
					} elseif (\in_array($fieldModel->getType(), $fieldsWithCurrency, true)) {
						$itemHtml .= \CurrencyField::appendCurrencySymbol($fieldModel->getDisplayValue($itemValue, $inventoryRow), $currencySymbol);
					} else {
						$itemHtml .= $fieldModel->getDisplayValue($itemValue, $inventoryRow);
					}
					$itemHtml .= '</td>';
					$columnHtml = $itemHtml;
				}
				$rowHtml .= $columnHtml;
			}
			$displayRows[] = $rowHtml;
		}
		$html .= '<table class="products-table-images" style="width:100%; border-collapse:collapse;"><thead><tr>';
		foreach ($displayFields as $field) {
			$html .= $field['headerHtml'];
		}
		$html .= '</tr></thead><tbody>';
		$counter = 0;
		foreach ($displayRows as $rowHtml) {
			++$counter;
			$html .= "<tr class=\"row-$counter\">$rowHtml</tr>";
		}
		$html .= '</tbody><tfoot><tr>';
		foreach ($displayFields as $field) {
			$html .= $field['footerHtml'];
		}
		return $html .= '</tr></tfoot></table>';
	}
}
