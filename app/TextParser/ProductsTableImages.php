<?php

namespace App\TextParser;

/**
 * Products table images class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$baseCurrency = \Vtiger_Util_Helper::getBaseCurrency();
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
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
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$displayFields = [];

		foreach (['ItemNumber', 'Name', 'Quantity', 'UnitPrice', 'TotalPrice', 'GrossPrice'] as $fieldType) {
			foreach ($inventory->getFieldsByType($fieldType) as $fieldModel) {
				$columnName = $fieldModel->getColumnName();
				if (!$fieldModel->isVisible()) {
					continue;
				}
				$item = [];
				$item['headerHtml'] = "<th class=\"col-type-{$fieldModel->getType()}\" style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . '</th>';
				$item['model'] = $fieldModel;
				$footerHtml = "<th class=\"col-type-{$fieldModel->getType()}\" style=\"{$headerStyle}\">";
				if ($fieldModel->isSummary()) {
					$sum = 0;
					foreach ($inventoryRows as $inventoryRow) {
						$sum += $inventoryRow[$columnName];
					}
					$footerHtml .= \CurrencyField::appendCurrencySymbol(\CurrencyField::convertToUserFormat($sum, null, true), $currencySymbol);
				}
				$footerHtml .= '</th>';
				$item['footerHtml'] = $footerHtml;
				$displayFields[] = $item;
			}
		}
		if (empty($displayFields)) {
			return '';
		}
		array_splice($displayFields, 1, 0, [[
			'headerHtml' => "<th style=\"{$headerStyle}\">" . \App\Language::translate('PLL_IMAGE', 'Settings:PDF') . '</th>',
			'model' => 'image',
			'footerHtml' => '<th></th>',
		]]);
		$displayRows = [];
		$counter = 1;
		foreach ($inventoryRows as $inventoryRow) {
			$rowHtml = '';
			foreach ($displayFields as $field) {
				$fieldModel = $field['model'];
				$fieldStyle = $bodyStyle;
				if ('image' === $fieldModel) {
					$imageDataJson = \Vtiger_Record_Model::getInstanceById($inventoryRow['name'])->get('imagename');
					$imageData = \App\Json::decode($imageDataJson);
					$image = '';
					if (!empty($imageData) && !empty($imageData[0]['path'])) {
						$base64 = \App\Fields\File::getImageBaseData($imageData[0]['path']);
						$image = '<img src="' . $base64 . '" style="width:80px;height:auto;">';
					}
					$columnHtml = "<td class=\"col-type-image\" style=\"border:1px solid #ddd;padding:0px 4px;text-align:center;\">$image</td>";
				} else {
					$columnName = $fieldModel->getColumnName();
					$typeName = $fieldModel->getType();
					if ('ItemNumber' === $typeName) {
						$columnHtml = "<td class=\"col-type-{$typeName}\" style=\"{$bodyStyle}text-align:center;\"><strong>" . $counter++ . '</strong></td>';
					} elseif ('ean' === $columnName) {
						$code = $inventoryRow[$columnName];
						$columnHtml = "<td class=\"col-type-barcode\" style=\"{$bodyStyle}\"><div data-barcode=\"EAN13\" data-code=\"$code\" data-size=\"1\" data-height=\"16\">{$code}</div></td>";
					} else {
						$itemValue = $inventoryRow[$columnName];
						if ('Name' === $typeName) {
							$fieldValue = '<strong>' . $fieldModel->getDisplayValue($itemValue) . '</strong>';
							foreach ($inventory->getFieldsByType('Comment') as $commentField) {
								if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()]) && $comment = $commentField->getDisplayValue($value, $inventoryRow)) {
									$fieldValue .= '<br />' . $comment;
								}
							}
						} elseif (\in_array($typeName, ['TotalPrice', 'GrossPrice', 'UnitPrice'])) {
							$fieldValue = \CurrencyField::appendCurrencySymbol($fieldModel->getDisplayValue($itemValue, $inventoryRow), $currencySymbol);
							$fieldStyle = $bodyStyle . 'text-align:right;';
						} else {
							$fieldValue = $fieldModel->getDisplayValue($itemValue);
						}
						$itemHtml = "<td class=\"col-type-{$typeName}\" style=\"{$fieldStyle}\">{$fieldValue}</td>";
						$columnHtml = $itemHtml;
					}
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
		$html .= '</tr></tfoot></table>';
		return $html;
	}
}
