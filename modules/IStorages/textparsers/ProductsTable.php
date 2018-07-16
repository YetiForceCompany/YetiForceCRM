<?php

/**
 * IStorages storage products table parser class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class IStorages_ProductsTable_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE';

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
		$columns = ['Product Name', 'FL_EAN_13', 'Product Category'];
		// Gets sum of products quantity in current storage
		$productsQty = [];
		$dataReader = (new App\Db\Query())->select(['qtyinstock' => new yii\db\Expression('SUM(qtyinstock)'), 'relcrmid'])
			->from('u_#__istorages_products')
			->where(['crmid' => $this->textParser->record])
			->groupBy(['relcrmid'])->createCommand()->query();
		while ($row = $dataReader->read()) {
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
