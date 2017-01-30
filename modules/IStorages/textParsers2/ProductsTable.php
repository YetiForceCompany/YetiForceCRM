<?php

/**
 * IStorages storage products table parser class
 * @package YetiForce.TextParser
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class IStorages_ProductsTable_TextParser extends \App\TextParser\Base
{

	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process
	 * @return string
	 */
	public function process()
	{
		$html = '';
		$relationModuleName = 'Products';
		$relationListView = Vtiger_RelationListView_Model::getInstance($this->textParser->recordModel, $relationModuleName);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 'no_limit');
		$entries = $relationListView->getEntries($pagingModel);
		$headers = $relationListView->getHeaders();
		$columns = ['Product Name', 'FL_EAN_13', 'Product Category'];
		$db = PearDatabase::getInstance();
		// Gets sum of products quantity in current storage
		$productsQty = [];
		$query = 'SELECT SUM(qtyinstock) AS qtyinstock, relcrmid FROM u_yf_istorages_products WHERE crmid = ? GROUP BY relcrmid';
		$result = $db->pquery($query, [$this->textParser->record]);
		while ($row = $db->getRow($result)) {
			if ($row['qtyinstock'] > 0) {
				$productsQty[$row['relcrmid']] = $row['qtyinstock'];
			}
		}
		$html .= '<style>' .
			'.productTable {color:#000; font-size:10px; width:100%}' .
			'.productTable th {text-transform: uppercase;font-weight:normal}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tr td{border-bottom: 1px solid #ddd; padding:5px;text-align:center; }' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .width30 {width:30%}' .
			'.productTable .width25 {width:25%}' .
			'.productTable .width15 {width:15%}' .
			'</style>';
		if ($entries) {
			$html .= '<table border="0" cellpadding="0" cellspacing="0" class="productTable"><thead><tr>';
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

					$html .= '<th ' . $class . ' style="padding:10px">' . \App\Language::translate($header->get('label'), 'Products') . '</th>';
				}
			}
			$html .= '<th class="width15" style="padding:10px">' . \App\Language::translate('Qty In Stock', $relationModuleName) . '</th>';
			$html .= '<th class="width15" style="padding:10px">' . \App\Language::translate('Qty/Unit', $relationModuleName) . '</th>';
			$html .= '</tr></thead><tbody>';
			foreach ($entries as $entry) {
				$entryId = $entry->getId();
				$entryRecordModel = Vtiger_Record_Model::getInstanceById($entryId, $relationModuleName);
				$productId = $entryRecordModel->get('id');
				if (isset($productsQty[$productId])) {
					$html .= '<tr>';
					$qtyInStock = $productsQty[$productId];
					$qtyPerUnit = $entryRecordModel->get('qty_per_unit');
					foreach ($headers as $header) {
						$label = $header->get('label');
						$colName = $header->get('name');
						if (in_array($label, $columns)) {
							$html .= '<td>' . $entry->getDisplayValue($colName) . '</td>';
						}
					}
					$html .= '<td>' . $qtyInStock . '</td>';
					$html .= '<td>' . $qtyPerUnit . '</td>';
					$html .= '</tr>';
				}
			}
			$html .= '</tbody></table>';
		}
		return $html;
	}
}
