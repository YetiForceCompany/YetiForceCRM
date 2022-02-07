<?php
/**
 * IStorages products table with storages hierarchy parser class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$relationModuleName = 'Products';
		// Products from main storage
		$relationListView = \Vtiger_RelationListView_Model::getInstance($this->textParser->recordModel, $relationModuleName);
		$productModel = $relationListView->getRelatedModuleModel();
		// Summary table with products from all storages
		$allEntries[$this->textParser->record] = $relationListView->getAllEntries();
		// Hierarchy of main storage (contains child storages)
		$focus = $this->textParser->recordModel->getEntity();
		$storageList[$this->textParser->record] = [
			'depth' => 0,
			'subject' => $this->textParser->recordModel->get('subject'),
			'assigned_user_id' => \App\Fields\Owner::getLabel($this->textParser->recordModel->get('assigned_user_id'))
		];
		$storageList = $focus->getChildIStorages($this->textParser->record, $storageList[$this->textParser->record], $storageList[$this->textParser->record]['depth']);
		$listviewEntries = [];
		$hierarchyList = $focus->getHierarchyData($this->textParser->record, $storageList, $this->textParser->record, $listviewEntries, true);
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
			if (\is_array($storageInfo) && (int) $storageId && $storageId != $this->textParser->record) {
				// Getting storage products if it is child of main storage
				$storageRecordModel = Vtiger_Record_Model::getInstanceById($storageId);
				$storageRelationListView = Vtiger_RelationListView_Model::getInstance($storageRecordModel, $relationModuleName);
				$allEntries[$storageId] = $storageRelationListView->getAllEntries();
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
			if (false === isset($productsQty[$productId])) {
				$productsQty[$productId] = 0;
			}
			foreach ($storegeSubjectArray as $i => $storageData) {
				if (false === isset($storegeSubjectArray[$i]['products'][$productId])) {
					$storegeSubjectArray[$i]['products'][$productId] = 0;
				}
			}
			$storegeSubjectArray[$storageId]['products'][$productId] += (float) $qty;
			$productsQty[$productId] += (float) $qty;
		}
		$dataReader->close();
		if ('' != $storageSubjectList) {
			$html .= '<div style="width:50%;float:right;">';
			$html .= '<table style="width:100%;border-collapse:collapse;font-size:10px;padding:5px;">';
			foreach ($storegeSubjectArray as $storageData) {
				$html .= '<tr><td style="width:100%;white-space:nowrap;text-align:right">' . $storageData['rowNum'] . '.</td>';
				$html .= '<td style="white-space:nowrap;">' . $storageData['name'] . '</td></tr>';
			}
			$html .= '</table>';
			$html .= '</div>';
		}
		$html .= '<table border="1" class="products-table" style="border-collapse:collapse;width:100%;"><thead><tr>';
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$columns = [];
		foreach (['productname', 'ean', 'pscategory'] as $fieldName) {
			$fieldModel = $productModel->getFieldByName($fieldName);
			if (!$fieldModel || !$fieldModel->isActiveField()) {
				continue;
			}
			$columns[$fieldName] = $fieldModel;
			$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->getFieldLabel(), $relationModuleName) . '</th>';
		}
		$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate('Qty In Stock', $relationModuleName) . '</th>';
		$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate('Qty/Unit', $relationModuleName) . '</th>';
		$html .= '</tr></thead><tbody>';
		if (\count($productsQty) > 0) {
			$productsInTable = [];
			foreach ($allEntries as $entries) {
				foreach ($entries as $entry) {
					$entryId = $entry->getId();
					$entryRecordModel = Vtiger_Record_Model::getInstanceById($entryId, $relationModuleName);
					$productId = $entryRecordModel->get('id');
					if (isset($productsQty[$productId]) && false === \in_array($productId, $productsInTable)) {
						$storagesQtyString = '[';
						foreach ($storegeSubjectArray as $storageData) {
							$storagesQtyString .= $storageData['products'][$productId] . ',';
						}
						$storagesQtyString = rtrim($storagesQtyString, ',');
						$storagesQtyString .= ']';
						$productsInTable[] = $productId;
						$html .= '<tr>';
						foreach ($columns as $header) {
							$html .= "<td style=\"{$bodyStyle}\">" . $entryRecordModel->getDisplayValue($header->getName()) . '</td>';
						}
						$html .= "<td style=\"{$bodyStyle}\">" . $productsQty[$productId] . ' ' . $storagesQtyString . '</td>';
						$html .= "<td style=\"{$bodyStyle}\">" . \App\Fields\Double::formatToDisplay($entryRecordModel->get('qty_per_unit'), false) . '</td>';
						$html .= '</tr>';
					}
				}
			}
			$html .= '</tbody></table></div>';
		}
		return $html;
	}
}
