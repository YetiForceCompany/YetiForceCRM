<?php

/**
 * IStorages storage products value table parser class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$relationListView = \Vtiger_RelationListView_Model::getInstance($this->textParser->recordModel, $relationModuleName);
		$productModel = $relationListView->getRelatedModuleModel();
		$entries = $relationListView->getAllEntries();
		if ($entries) {
			$html .= '<table border="1" class="products-table" style="border-collapse:collapse;width:100%;"><thead><tr>';
			$columns = [];
			$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
			$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
			foreach (['productname', 'ean', 'pscategory', 'unit_price'] as $fieldName) {
				$fieldModel = $productModel->getFieldByName($fieldName);
				if (!$fieldModel || !$fieldModel->isActiveField()) {
					continue;
				}
				$columns[$fieldName] = $fieldModel;
				$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->getFieldLabel(), $relationModuleName) . '</th>';
			}
			$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate('Qty In Stock', $relationModuleName) . '</th>';
			$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate('Qty/Unit', $relationModuleName) . '</th>';
			$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate('LBL_VALUE') . '</th>';
			$html .= '</tr></thead><tbody>';
			$totalValue = 0;
			$currencyInfo = \App\Fields\Currency::getDefault();
			$currencyId = $currencyInfo['id'];
			foreach ($entries as $entry) {
				$html .= '<tr>';
				$entryId = $entry->getId();
				$entryRecordModel = \Vtiger_Record_Model::getInstanceById($entryId, $relationModuleName);
				$qtyInStock = $entryRecordModel->get('qtyinstock');
				$qtyPerUnit = $entryRecordModel->get('qty_per_unit');
				$unitPriceField = $entryRecordModel->getField('unit_price');
				$unitPriceUiTypeModel = $unitPriceField->getUITypeModel();
				$unitPrice = $unitPriceUiTypeModel->getValueForCurrency($entryRecordModel->get($unitPriceField->getName()), $currencyId);
				$value = $qtyInStock * $unitPrice;
				$totalValue += $value;
				foreach ($columns as $header) {
					$html .= "<td style=\"{$bodyStyle}\">" . $entryRecordModel->getDisplayValue($header->getName()) . '</td>';
				}
				$html .= "<td style=\"{$bodyStyle}\">" . \App\Fields\Double::formatToDisplay($qtyInStock, false) . '</td>';
				$html .= "<td style=\"{$bodyStyle}\">" . \App\Fields\Double::formatToDisplay($qtyPerUnit, false) . '</td>';
				$html .= "<td style=\"{$bodyStyle}\">" . \CurrencyField::convertToUserFormatSymbol($value, true, $currencyInfo['currency_symbol']) . '</td>';
				$html .= '</tr>';
			}
			$html .= '<tr style="background:#fff">
							<td colspan="' . (\count($columns) + 2) . '" style="border-top:1px solid #ddd;"></td>
							<td style="font-size:9px;border:1px solid #ddd;background:#eee;"><b>' . \CurrencyField::convertToUserFormatSymbol($totalValue, true, $currencyInfo['currency_symbol']) . '<b></td>
						</tr></tbody></table>';
		}
		return $html;
	}
}
