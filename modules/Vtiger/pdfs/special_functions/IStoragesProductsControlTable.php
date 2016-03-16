<?php

/**
 * Special function displaying storage products table
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class Pdf_IStoragesProductsControlTable extends Vtiger_SpecialFunction_Pdf
{

	public $permittedModules = ['IStorages'];

	public function process($module, $id, Vtiger_PDF_Model $pdf)
	{
		$html = '';
		$recordId = $id;
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$relationModuleName = 'Products';
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relationModuleName);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$entries = $relationListView->getEntries($pagingModel);
		$headers = $relationListView->getHeaders();
		$columns = ['Product Name', 'FL_EAN_13', 'Product Category'];

		$html .='<style>' .
			'.productTable {color:#000; font-size:10px; width:100%}' .
			'.productTable th {text-transform: uppercase;font-weight:normal}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tr td{border-bottom: 1px solid #ddd; padding:5px;text-align:center; }' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .width30 {width:30%}' .
			'.productTable .width25 {width:25%}' .
			'.productTable .width15 {width:15%}' .
			'</style>';

		if (count($entries) > 0) {
			$html .=
				'<table border="0" cellpadding="0" cellspacing="0" class="productTable">
				<thead>
					<tr>';
			foreach ($headers as $header) {
				$label = $header->get('label');
				if (in_array($label, $columns)) {
					switch ($label) {
						default:
							$class = 'class="width15"';
							break;
						case 'Product Name':
							$class = 'class="width30"';
							break;
						case 'Procuct Category':
							$class = 'class="width25"';
							break;
					}

					$html .= '<th ' . $class . ' style="padding:10px">' . vtranslate($header->get('label'), 'Products') . '</th>';
				}
			}
			$html .= '<th class="width15" style="padding:10px">' . vtranslate('Qty In Stock', $relationModuleName) . '</th>';
			$html .= '<th class="width15" style="padding:10px">' . vtranslate('Qty/Unit', $relationModuleName) . '</th>';
			$html .=
				'</tr>
				</thead>
				<tbody>';
			foreach ($entries as $entry) {
				$html .= '<tr>';
				$entryId = $entry->getId();
				$entryRecordModel = Vtiger_Record_Model::getInstanceById($entryId, $relationModuleName);
				$qtyPerUnit = $entryRecordModel->get('qty_per_unit');
				foreach ($headers as $header) {
					$label = $header->get('label');
					$colName = $header->get('name');
					if (in_array($label, $columns)) {
						$html .= '<td>' . $entry->getDisplayValue($colName) . '</td>';
					}
				}

				$html .= '<td></td>';
				$html .= '<td>' . $qtyPerUnit . '</td>';
				$html .= '</tr>';
			}
			$html .= '</tbody>
					</table>';
		}
		return $html;
	}
}
