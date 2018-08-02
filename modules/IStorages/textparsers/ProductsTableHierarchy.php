<?php
/**
 * IStorages products table with storages hierarchy parser class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Class IStorages_ProductsTableHierarchy_TextParser.
 */
class IStorages_ProductsTableHierarchy_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_HIERARCHY';

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
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', 0);
		$relationModuleName = 'Products';
		$columns = ['Product Name', 'FL_EAN_13', 'Product Category'];
		// Products from main storage
		$relationListView = Vtiger_RelationListView_Model::getInstance($this->textParser->recordModel, $relationModuleName);
		// Summary table with products from all storages
		$allEntries[$this->textParser->record] = $relationListView->getEntries($pagingModel);
		$headers = $relationListView->getHeaders();
		// Hierarchy of main storage (contains child storages)
		$focus = $this->textParser->recordModel->getEntity();
		$storageList[$this->textParser->record] = [
			'depth' => 0,
			'subject' => $this->textParser->recordModel->get('subject'),
			'assigned_user_id' => $this->textParser->recordModel->get('assigned_user_id_label'),
		];
		$storageList = $focus->getChildIStorages($this->textParser->record, $storageList[$this->textParser->record], $storageList[$this->textParser->record]['depth']);
		$hierarchyList = $focus->getHierarchyData($this->textParser->record, $storageList, $this->textParser->record, [], true);
		// String with all storages (main and its children) names
		$storageSubjectList = '';
		$storegeSubjectArray = [];
		$storageIdsArray = [];
		$rowNum = 1;
		foreach ($hierarchyList as $storageId => $storageInfo) {
			$storegeSubjectArray[$storageId]['name'] = $storageInfo[0];
			$storegeSubjectArray[$storageId]['rowNum'] = $rowNum;
			++$rowNum;
			if ($storageId !== $this->textParser->record) {
				$storageSubjectList .= $storageInfo[0] . ', ';
			}
			$storageIdsArray[] = $storageId;
			if (is_array($storageInfo) && (int) $storageId && $storageId != $this->textParser->record) {
				// Getting storage products if it is child of main storage
				$storageRecordModel = Vtiger_Record_Model::getInstanceById($storageId);
				$storageRelationListView = Vtiger_RelationListView_Model::getInstance($storageRecordModel, $relationModuleName);
				$allEntries[$storageId] = $storageRelationListView->getEntries($pagingModel);
			}
		}
		$storageSubjectList = rtrim($storageSubjectList, ', ');
		// Gets the sum of products quantity in all storages
		$productsQty = [];
		$dataReader = (new \App\Db\Query())->select(['qtyinstock', 'relcrmid', 'crmid'])->from('u_#__istorages_products')->where(['crmid' => $storageIdsArray])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$productId = $row['relcrmid'];
			$storageId = $row['crmid'];
			$qty = $row['qtyinstock'];
			if (isset($productsQty[$productId]) === false) {
				$productsQty[$productId] = 0;
			}
			foreach ($storegeSubjectArray as $i => $storageData) {
				if (isset($storegeSubjectArray[$i]['products'][$productId]) === false) {
					$storegeSubjectArray[$i]['products'][$productId] = 0;
				}
			}
			$storegeSubjectArray[$storageId]['products'][$productId] += (float) $qty;
			$productsQty[$productId] += (float) $qty;
		}
		$dataReader->close();
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
		if ($storageSubjectList != '') {
			$html .= '<div style="width:50%;float:right;">';
			$html .= '<table style="width:100%;border-collapse:collapse;font-size:10px;padding:5px;">';
			foreach ($storegeSubjectArray as $storageData) {
				$html .= '<tr><td style="width:100%;white-space:nowrap;text-align:right">' . $storageData['rowNum'] . '.</td>';
				$html .= '<td style="white-space:nowrap;">' . $storageData['name'] . '</td></tr>';
			}
			$html .= '</table>';
			$html .= '</div>';
		}
		if (count($productsQty) > 0) {
			$html .= '<div style="width:100%"><table border="0" cellpadding="0" cellspacing="0" class="productTable"><thead><tr>';
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
			$productsInTable = [];
			foreach ($allEntries as $entries) {
				foreach ($entries as $entry) {
					$entryId = $entry->getId();
					$entryRecordModel = Vtiger_Record_Model::getInstanceById($entryId, $relationModuleName);
					$productId = $entryRecordModel->get('id');
					if (isset($productsQty[$productId]) && in_array($productId, $productsInTable) === false) {
						$storagesQtyString = '[';
						foreach ($storegeSubjectArray as $storageData) {
							$storagesQtyString .= $storageData['products'][$productId] . ',';
						}
						$storagesQtyString = rtrim($storagesQtyString, ',');
						$storagesQtyString .= ']';
						$productsInTable[] = $productId;
						$html .= '<tr>';
						foreach ($headers as $header) {
							$label = $header->get('label');
							$colName = $header->get('name');
							if (in_array($label, $columns)) {
								$html .= '<td>' . $entry->getDisplayValue($colName) . '</td>';
							}
						}
						$html .= '<td>' . $productsQty[$productId] . ' ' . $storagesQtyString . '</td>';
						$html .= '<td>' . $entryRecordModel->get('qty_per_unit') . '</td>';
						$html .= '</tr>';
					}
				}
			}
			$html .= '</tbody></table></div>';
		}
		return $html;
	}
}
