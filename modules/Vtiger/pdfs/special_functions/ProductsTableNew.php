<?php

/**
 * Special function displaying products table
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Pdf_ProductsTableNew extends Vtiger_SpecialFunction_Pdf
{

	public $permittedModules = ['all'];

	public function process($module, $id, Vtiger_PDF_Model $pdf)
	{
		$html = '';
		$recordId = $id;
		$record = Vtiger_Record_Model::getInstanceById($recordId);
		$moduleModel = $record->getModule();
		if (!$moduleModel->isInventory()) {
			return $html;
		}
		$inventoryField = Vtiger_InventoryField_Model::getInstance($module);
		$fields = $inventoryField->getFields(true);
		$columns = $inventoryField->getColumns();
		$inventoryRows = $record->getInventoryData();

		if (in_array("currency", $columns)) {
			if (count($inventoryRows) > 0 && $inventoryRows[0]['currency'] != NULL) {
				$currency = $inventoryRows[0]['currency'];
			} else {
				$baseCurrency = Vtiger_Util_Helper::getBaseCurrency();
				$currency = $baseCurrency['id'];
			}
			$currencySymbolRate = Vtiger_Functions::getCurrencySymbolandRate($currency);
		}
		$html .='<style>' .
			'.productTable{color:#000; font-size:10px}' .
			'.productTable th {text-transform: uppercase;}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tbody tr td{border-bottom: 1px solid #ddd; padding:5px}' .
			'.colapseBorder {border-collapse: collapse;}' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .summaryContainer{background:#ccc;padding:5px}' .
			'</style>';

		if (count($fields[1]) != 0) {
			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table border="0" cellpadding="0" cellspacing="0" class="productTable">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible($inventoryRows)) {
					$html .= '<th colspan="' . $field->get('colspan') . '" class="textAlignCenter tBorder tHeader">' . vtranslate($field->get('label'), $module) . '</th>';
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';
			foreach ($inventoryRows as $key => &$inventoryRow) {
				$rowNo = $key + 1;
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if ($field->isVisible($inventoryRows)) {
						$itemValue = $inventoryRow[$field->get('columnname')];
						$html .= '<td ' . ($field->getName() == 'Name' ? 'width="40%;" ' : '') . ' class="' . (in_array($field->getName(), $fieldsTextAlignRight) ? 'textAlignRight ' : '') . 'tBorder">';
						switch ($field->getTemplateName('DetailView', $module)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue) . '</strong>';
								if (isset($fields[2]['comment' . $rowNo])) {
									$COMMENT_FIELD = $fields[2]['comment' . $rowNo];
									$html .= '<br/>' . $COMMENT_FIELD->getDisplayValue($inventoryRow[$COMMENT_FIELD->get('columnname')]);
								}
								break;

							case 'DetailViewBase.tpl':
								$html .= $field->getDisplayValue($itemValue);
								break;
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody>
					<tfoot>
						<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible($inventoryRows)) {
					$html .= '<td colspan="' . $field->get('colspan') . '" class="textAlignRight ';
					if ($field->isSummary()) {
						$html .= 'summaryContainer';
					}
					$html .= '">';

					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as $key => &$inventoryRow) {
							$sum += $inventoryRow[$field->get('columnname')];
						}
						$html .= CurrencyField::convertToUserFormat($sum, null, true);
					}
					$html .= '</td>';
				}
			}
			$html .= '</tr>
					</tfoot>
				</table>';
		}
		return $html;
	}
}
