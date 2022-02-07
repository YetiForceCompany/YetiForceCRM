<?php

namespace App\TextParser;

/**
 * Products table related module class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ProductsTableRelatedModule extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_RELATED_MODULE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template
	 * Example: $(custom : ProductsTableRelatedModule|FInvoiceProforma|finvoiceproformaid|seq,name,qty,discount,currency,discountmode,taxmode,price,gross,net,tax,total)$
	 */
	public $default = '$(custom : ProductsTableRelatedModule|__MODULE_NAME__|__RELATION_FIELD_NAME__|__INVENTORY_FIELDS__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		[$relatedModuleName, $relatedModuleField, $fieldsToShow] = array_pad($this->params, 3, null);
		$fieldsToShow = !empty($fieldsToShow) ? explode(',', $fieldsToShow) : ['seq', 'name', 'qty', 'discount', 'currency', 'discountmode', 'taxmode', 'price', 'gross', 'net', 'tax', 'total'];
		$relatedModuleRecordId = $this->textParser->recordModel->get($relatedModuleField);
		if (!empty($relatedModuleRecordId) && \App\Record::isExists($relatedModuleRecordId, $relatedModuleName)) {
			$relatedModuleRecordModel = \Vtiger_Record_Model::getInstanceById($relatedModuleRecordId, $relatedModuleName);
			if (!$relatedModuleRecordModel->getModule()->isInventory() || empty($fieldsToShow)) {
				return $html;
			}
			$inventory = \Vtiger_Inventory_Model::getInstance($relatedModuleName);
			$inventoryRows = $relatedModuleRecordModel->getInventoryData();
			$headerStyle = 'font-size:9px;padding:0px 4px;';
			$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
			$html .= '<table class="products-table-related-module" style="width:100%;border-collapse:collapse;"><thead><tr>';
			$groupModels = [];
			foreach ($fieldsToShow as $fieldName) {
				if (!($fieldModel = $inventory->getField($fieldName)) || !$fieldModel->isVisible()) {
					continue;
				}
				$html .= "<th style=\"{$headerStyle}text-align:center;\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . '</th>';
				$groupModels[$fieldName] = $fieldModel;
			}
			$html .= '</tr></thead>';
			if (!empty($groupModels)) {
				$html .= '<tbody>';
				$counter = 1;
				foreach ($inventoryRows as $inventoryRow) {
					$html .= '<tr class="row-' . $counter . '">';
					foreach ($groupModels as $fieldModel) {
						$columnName = $fieldModel->getColumnName();
						$typeName = $fieldModel->getType();
						$fieldStyle = $bodyStyle;
						if ('ItemNumber' === $typeName) {
							$html .= "<td class=\"col-type-ItemNumber\" style=\"{$fieldStyle}font-weight:bold;text-align:center;\">" . $counter++ . '</td>';
						} elseif ('ean' === $columnName) {
							$code = $inventoryRow[$columnName];
							$html .= "<td class=\"col-type-barcode\" style=\"{$fieldStyle}font-weight:bold;text-align:center;\"><div data-barcode=\"EAN13\" data-code=\"{$code}\" data-size=\"1\" data-height=\"16\">{$code}</div></td>";
						} else {
							$itemValue = $inventoryRow[$columnName];
							if ('Name' === $typeName) {
								$fieldValue = '<strong>' . $fieldModel->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
								foreach ($inventory->getFieldsByType('Comment') as $commentField) {
									if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()]) && $comment = $commentField->getDisplayValue($value, $inventoryRow)) {
										$fieldValue .= '<br />' . $comment;
									}
								}
							} elseif (\in_array($typeName, ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice'])) {
								$fieldValue = $fieldModel->getDisplayValue($itemValue, $inventoryRow);
								$fieldStyle = $bodyStyle . 'text-align:right;';
							} else {
								$fieldValue = $fieldModel->getDisplayValue($itemValue, $inventoryRow);
							}
							$html .= "<td class=\"col-type-{$typeName}\" style=\"{$fieldStyle}\">{$fieldValue}</td>";
						}
					}
					$html .= '</tr>';
				}
				$html .= '</tbody><tfoot><tr>';
				foreach ($groupModels as $fieldModel) {
					$html .= "<th class=\"col-type-{$fieldModel->getType()}\" style=\"{$headerStyle}text-align:right;\">";
					if ($fieldModel->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $inventoryRow) {
							$sum += $inventoryRow[$fieldModel->getColumnName()];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true);
					}
					$html .= '</th>';
				}
				$html .= '</tr></tfoot></table>';
			}
		}
		return $html;
	}
}
