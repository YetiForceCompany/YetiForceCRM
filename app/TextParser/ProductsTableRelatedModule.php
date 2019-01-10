<?php

namespace App\TextParser;

/**
 * Products table related module class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ProductsTableRelatedModule extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_RELATED_MODULE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var array Allowed modules */
	public $allowedModules = ['IGRNC', 'IGDNC'];

	/** @var array Related modules fields */
	protected $relatedModulesFields = ['IGRNC' => 'igrnid', 'IGDNC' => 'igdnid'];

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		$relatedModuleRecordId = $this->textParser->recordModel->get($this->relatedModulesFields[$this->textParser->moduleName]);
		$relatedModuleRecordModel = \Vtiger_Record_Model::getInstanceById($relatedModuleRecordId);
		if (!$relatedModuleRecordModel->getModule()->isInventory()) {
			return $html;
		}
		$relatedModuleName = $relatedModuleRecordModel->getModuleName();
		$inventory = \Vtiger_Inventory_Model::getInstance($relatedModuleName);
		$fields = $inventory->getFieldsByBlocks();
		$inventoryRows = $relatedModuleRecordModel->getInventoryData();
		if (!empty($fields[1])) {
			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table style="width:100%;border-collapse:collapse;"><thead><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<th style="padding:0px 4px;text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
				}
			}
			$html .= '</tr></thead><tbody>';
			$counter = 1;
			foreach ($inventoryRows as $inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if (!$field->isVisible()) {
						continue;
					}
					if ($field->getType() === 'ItemNumber') {
						$html .= '<td style="font-weight:bold;">' . $counter++ . '</td>';
					} elseif ($field->getColumnName() === 'ean') {
						$code = $inventoryRow[$field->getColumnName()];
						$html .= '<td><div data-barcode="EAN13" data-code="' . $code . '" data-size="1" data-height="16"></div></td>';
					} else {
						$itemValue = $inventoryRow[$field->getColumnName()];
						$html .= '<td style="font-size:8px;border:1px solid #ddd;padding:0px 4px;' . (in_array($field->getType(), $fieldsTextAlignRight) ? 'text-align:right;' : '') . '">';
						if ($field->getType() === 'Name') {
							$html .= '<strong>' . $field->getDisplayValue($itemValue, $inventoryRow) . '</strong>';
							foreach ($inventory->getFieldsByType('Comment') as $commentField) {
								if ($commentField->isVisible() && ($value = $inventoryRow[$commentField->getColumnName()])) {
									$html .= '<br />' . $commentField->getDisplayValue($value, $inventoryRow);
								}
							}
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
				if ($field->isVisible()) {
					$html .= '<th style="padding:0px 4px;text-align:right">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $inventoryRow) {
							$sum += $inventoryRow[$field->getColumnName()];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true);
					}
					$html .= '</th>';
				}
			}
			$html .= '</tr></tfoot></table>';
		}
		return $html;
	}
}
