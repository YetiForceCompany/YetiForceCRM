<?php
/**
 * Table group summary class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\TextParser;

/**
 * Summary of Advanced Block Groups class.
 */
class TableGroupSummary extends Base
{
	/** @var string Class name */
	public $name = 'LBL_INV_TABLE_GROUP_SUMMARY';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = '$(custom : TableGroupSummary)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		if (!$this->textParser->recordModel || !$this->textParser->recordModel->getModule()->isInventory()) {
			return '';
		}
		$html = '';
		$inventory = \Vtiger_Inventory_Model::getInstance($this->textParser->moduleName);
		$fieldNames = !empty($this->params[0]) ? explode(',', $this->params[0]) : array_unique(array_merge(['grouplabel'], array_keys($inventory->getFields())));

		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$currencyId = current($inventoryRows)['currency'] ?? null;
		if (!$currencyId) {
			$currencyId = \App\Fields\Currency::getDefault()['id'];
			foreach ($inventoryRows as &$row) {
				$row['currency'] = $currencyId;
			}
		}

		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;text-align:center;';
		$html .= '<table class="products-table-new" style="width:100%;border-collapse:collapse;"><thead><tr>';
		$groupModels = [];
		foreach ($fieldNames as $fieldName) {
			$fieldModel = $inventory->getField($fieldName);
			if (!$fieldModel || (!$fieldModel->isSummary() && 'grouplabel' !== $fieldModel->getColumnName()) || !$fieldModel->isVisible()) {
				continue;
			}
			$html .= "<th class=\"col-type-{$fieldModel->getType()}\" style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->getLabel(), $this->textParser->moduleName) . '</th>';
			$groupModels[$fieldModel->getColumnName()] = $fieldModel;
		}

		$html .= '</tr></thead>';
		if (!empty($groupModels)) {
			$html .= '<tbody>';
			$number = 0;
			$counter = 0;
			foreach ($inventory->transformData($inventoryRows) as $inventoryRow) {
				++$number;
				++$counter;
				$html .= '<tr class="row-' . $number . '">';
				foreach ($groupModels as $fieldModel) {
					$columnName = $fieldModel->getColumnName();
					$typeName = $fieldModel->getType();
					$fieldStyle = $bodyStyle;

					if ($fieldModel->isSummary()) {
						$fieldValue = $fieldModel->getDisplayValue($inventoryRow[$columnName], $inventoryRow);
						$fieldStyle = $bodyStyle . 'text-align:right;white-space: nowrap;';
					} else {
						$fieldValue = \App\Purifier::encodeHtml($fieldModel->getDisplayValue($inventoryRow[$columnName], $inventoryRow, true));
					}
					$html .= "<td class=\"col-type-{$typeName}\" style=\"{$fieldStyle}\">{$fieldValue}</td>";
				}
				$html .= '</tr>';
			}
			$html .= '</tbody></table>';
		}

		return $html;
	}
}
