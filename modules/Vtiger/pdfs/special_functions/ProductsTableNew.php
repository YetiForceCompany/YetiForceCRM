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
		$inventoryRows = $record->getInventoryData();

		$html .='<style>' .
			'.productTable{color:#000; font-size:10px; width:100%}' .
			'.productTable th {text-transform: uppercase;font-weight:normal}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tr td{border-bottom: 1px solid #ddd; padding:5px;text-align:center; }' .
			'.colapseBorder {border-collapse: collapse;}' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .summaryContainer{background:#ccc;padding:5px}' .
			'.barcode {padding: 1.5mm;margin: 0;vertical-align: top;color: #000000}' .
			'</style>';

		if (count($fields[1]) != 0) {
			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table  border="0" cellpadding="0" cellspacing="0" class="productTable">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible($inventoryRows)) {
					$html .= '<th style="width:' . $field->get('colspan') . '%;" class="textAlignCenter tBorder tHeader">' . vtranslate($field->get('label'), $module) . '</th>';
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';
			foreach ($inventoryRows as $key => &$inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if ($field->getName() == 'ItemNumber') {
						$html .= '<td><strong>' . $inventoryRow['seq'] . '</strong></td>';
					} else if ($field->get('columnname') == 'ean') {
						$code = $inventoryRow[$field->get('columnname')];
						$html .= '<td><barcode code="' . $code . '" type="EAN13" size="0.5" height="0.5" class="barcode" /></td>';
					} else if ($field->isVisible($inventoryRows)) {
						$itemValue = $inventoryRow[$field->get('columnname')];
						$html .= '<td class="' . (in_array($field->getName(), $fieldsTextAlignRight) ? 'textAlignRight ' : '') . 'tBorder">';
						switch ($field->getTemplateName('DetailView', $module)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue) . '</strong>';
								foreach ($fields[2] as $commentKey => $value) {
									$COMMENT_FIELD = $fields[2][$commentKey];
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
					$html .= '<td class="textAlignRight ';
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
