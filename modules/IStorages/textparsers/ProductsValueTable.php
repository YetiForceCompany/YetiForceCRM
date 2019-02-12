<?php

/**
 * IStorages storage products value table parser class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class IStorages_ProductsValueTable_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_VALUE_TABLE';

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
		$relationModuleName = 'Products';
		$relationListView = Vtiger_RelationListView_Model::getInstance($this->textParser->recordModel, $relationModuleName);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 0);
		$entries = $relationListView->getEntries($pagingModel);
		$headers = $relationListView->getHeaders();
		$columns = ['Product Name', 'FL_EAN_13', 'Product Category', 'Unit Price'];
		$html .= '<style>' .
			'.productTable {color:#000; font-size:10px; width:100%}' .
			'.productTable th {text-transform: uppercase;font-weight:normal}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tr td{border-bottom: 1px solid #ddd; padding:5px;text-align:center; }' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .width30 {width:30%}' .
			'.productTable .width20 {width:20%}' .
			'.productTable .width10 {width:10%}' .
			'</style>';
		if ($entries) {
			$html .= '<table border="0" cellpadding="0" cellspacing="0" class="productTable"><thead><tr>';
			foreach ($headers as $header) {
				$label = $header->get('label');
				if (in_array($label, $columns)) {
					switch ($label) {
						default:
							$class = 'class="width10"';
							break;
						case 'Product Name':
						case 'Procuct Category':
							$class = 'class="width20"';
							break;
					}
					$html .= '<th ' . $class . ' style="padding:10px">' . \App\Language::translate($header->get('label'), 'Products') . '</th>';
				}
			}
			$html .= '<th class="width10" style="padding:10px">' . \App\Language::translate('Qty In Stock', $relationModuleName) . '</th>';
			$html .= '<th class="width10" style="padding:10px">' . \App\Language::translate('Qty/Unit', $relationModuleName) . '</th>';
			$html .= '<th class="width10" style="padding:10px">' . \App\Language::translate('LBL_VALUE') . '</th>';
			$html .= '</tr></thead><tbody>';
			$totalValue = 0;
			foreach ($entries as $entry) {
				$html .= '<tr>';
				$entryId = $entry->getId();
				$entryRecordModel = Vtiger_Record_Model::getInstanceById($entryId, $relationModuleName);
				$qtyInStock = $entryRecordModel->get('qtyinstock');
				$qtyPerUnit = $entryRecordModel->get('qty_per_unit');
				$unitPrice = $entryRecordModel->get('unit_price');
				$value = $qtyInStock * $unitPrice;
				$totalValue += $value;
				$valueFormatted = CurrencyField::convertToUserFormat($value, null, true);
				foreach ($headers as $header) {
					$label = $header->get('label');
					$colName = $header->get('name');
					if (in_array($label, $columns)) {
						$html .= '<td>' . $entry->getDisplayValue($colName) . '</td>';
					}
				}
				$html .= '<td>' . $qtyInStock . '</td>';
				$html .= '<td>' . $qtyPerUnit . '</td>';
				$html .= '<td>' . $valueFormatted . '</td>';
				$html .= '</tr>';
			}
			$totalValueFormatted = CurrencyField::convertToUserFormat($totalValue, null, true);
			$html .= '<tr style="background:#fff">
							<td colspan="6"></td>
							<td style="background:#eee;"><b>' . $totalValueFormatted . '<b></td>
						</tr></tbody></table>';
		}
		return $html;
	}
}
