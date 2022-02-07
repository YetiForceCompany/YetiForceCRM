<?php

/**
 * IStorages storage products table parser class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$relationListView = \Vtiger_RelationListView_Model::getInstance($this->textParser->recordModel, $relationModuleName);
		$productModel = $relationListView->getRelatedModuleModel();
		$entries = $relationListView->getAllEntries();
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
		if ($entries) {
			$html .= '<table border="1" class="products-table" style="border-collapse:collapse;width:100%;"><thead><tr>';
			$columns = [];
			$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
			$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
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
			foreach ($entries as $entry) {
				$entryId = $entry->getId();
				$entryRecordModel = Vtiger_Record_Model::getInstanceById($entryId, $relationModuleName);
				$productId = $entryRecordModel->get('id');
				if (isset($productsQty[$productId])) {
					$html .= '<tr>';
					$qtyInStock = $productsQty[$productId];
					$qtyPerUnit = $entryRecordModel->get('qty_per_unit');
					foreach ($columns as $header) {
						$html .= "<td style=\"{$bodyStyle}\">" . $entryRecordModel->getDisplayValue($header->getName()) . '</td>';
					}
					$html .= "<td style=\"{$bodyStyle}\">" . \App\Fields\Double::formatToDisplay($qtyInStock, false) . '</td>';
					$html .= "<td style=\"{$bodyStyle}\">" . \App\Fields\Double::formatToDisplay($qtyPerUnit, false) . '</td>';
					$html .= '</tr>';
				}
			}
			$html .= '</tbody></table>';
		}
		return $html;
	}
}
