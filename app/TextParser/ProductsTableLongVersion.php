<?php

namespace App\TextParser;

/**
 * Products table long version class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class ProductsTableLongVersion extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_LONG_VERSION';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$moduleName = $this->textParser->moduleName;
		$html = $this->getTableStyle($this->textParser->recordModel->getModule()->isInventory());
		$inventoryField = \Vtiger_InventoryField_Model::getInstance($moduleName);
		$fields = $inventoryField->getFields(true);
		$inventoryRows = $this->textParser->recordModel->getInventoryData();
		$currencySymbol = $this->getSymbol($inventoryField, $inventoryRows);
		if (count($fields[1]) != 0) {
			$fieldsTextAlignRight = ['Name', 'Value', 'Quantity', 'UnitPrice', 'TotalPrice', 'Discount', 'NetPrice', 'Tax', 'GrossPrice'];
			$html .= '<table  border="0" cellpadding="0" cellspacing="0" class="productTable">
				<thead>
					<tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible() && in_array($field->getName(), $fieldsTextAlignRight) && ($field->get('columnname') !== 'subunit')) {
					if ($field->getName() === 'Quantity' || $field->getName() === 'Value') {
						$html .= '<th style="width: 9%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $moduleName) . '</th>';
					} elseif ($field->getName() === 'Name') {
						$html .= '<th style="width: 30%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $moduleName) . '</th>';
					} else {
						$html .= '<th style="width: 12%;" class="textAlignCenter tBorder tHeader">' . \App\Language::translate($field->get('label'), $moduleName) . '</th>';
					}
				}
			}
			$html .= '</tr>
				</thead>
				<tbody>';
			$html = $this->getTableBody($inventoryRows, $fields, $fieldsTextAlignRight, $moduleName, $html, $currencySymbol);
			$html .= '</tbody><tfoot><tr>';
			$html = $this->getTableFoot($inventoryRows, $fields, $html, $currencySymbol);
			$html .= '</tr>
					</tfoot>
				</table>';
		}
		return $html;
	}

	/**
	 * Function get table style.
	 *
	 * @param bool $isInventory
	 *
	 * @return string
	 */
	public function getTableStyle($isInventory)
	{
		$html = '';
		if (!$isInventory) {
			return $html;
		}
		$html .= '<style>' .
			'.productTable{color:#000; font-size:10px; width:100%}' .
			'.productTable th {text-transform: capitalize;font-weight:normal}' .
			'.productTable .tHeader {background:#ddd, text-transform: capitalize !important;}' .
			'.productTable tbody tr:nth-child(odd){background:#eee}' .
			'.productTable tr td{border-bottom: 1px solid #ddd; padding:5px;text-align:center; }' .
			'.colapseBorder {border-collapse: collapse;}' .
			'.productTable td, th {padding-left: 5px; padding-right: 5px;}' .
			'.productTable .summaryContainer{background:#ddd;padding:5px}' .
			'.barcode {padding: 1.5mm;margin: 0;vertical-align: top;color: #000000}' .
			'</style>';
		return $html;
	}

	/**
	 * Function get symbol.
	 *
	 * @param Vtiger_InventoryField_Model $inventoryField
	 * @param array                       $inventoryRows
	 *
	 * @return string
	 */
	public function getSymbol($inventoryField, $inventoryRows)
	{
		if (in_array('currency', $inventoryField->getColumns())) {
			if (count($inventoryRows) > 0 && $inventoryRows[0]['currency'] !== null) {
				$currency = $inventoryRows[0]['currency'];
			} else {
				$currency = \Vtiger_Util_Helper::getBaseCurrency()['id'];
			}
			return \vtlib\Functions::getCurrencySymbolandRate($currency)['symbol'];
		}
	}

	/**
	 * Function get table body.
	 *
	 * @param array  $inventoryRows
	 * @param array  $fields
	 * @param array  $fieldsTextAlignRight
	 * @param string $moduleName
	 * @param string $html
	 * @param string $currencySymbol
	 *
	 * @return string
	 */
	public function getTableBody($inventoryRows, $fields, $fieldsTextAlignRight, $moduleName, $html, $currencySymbol)
	{
		foreach ($inventoryRows as $key => &$inventoryRow) {
			$html .= '<tr>';
			foreach ($fields[1] as $field) {
				if (!$field->isVisible() || ($field->get('columnname') === 'subunit')) {
					continue;
				}
				if ($field->getName() === 'ItemNumber') {
					$html .= '<td><strong>' . $inventoryRow['seq'] . '</strong></td>';
				} elseif ($field->get('columnname') === 'ean') {
					$code = $inventoryRow[$field->get('columnname')];
					$html .= '<td><barcode code="' . $code . '" type="EAN13" size="0.5" height="0.5" class="barcode" /></td>';
				} elseif ($field->isVisible()) {
					$itemValue = $inventoryRow[$field->get('columnname')];
					$html .= '<td class="' . (in_array($field->getName(), $fieldsTextAlignRight) ? 'textAlignRight ' : '') . 'tBorder">';
					switch ($field->getTemplateName('DetailView', $moduleName)) {
						case 'DetailViewName.tpl':
							$html .= '<strong>' . $field->getDisplayValue($itemValue, true) . '</strong>';
							foreach ($fields[2] as $commentKey => $value) {
								$COMMENT_FIELD = $fields[2][$commentKey];
								$html .= '<br />' . $COMMENT_FIELD->getDisplayValue($inventoryRow[$COMMENT_FIELD->get('columnname')]);
							}
							break;
						case 'DetailViewBase.tpl':
							if ($field->getName() === 'Quantity' || $field->getName() === 'Value') {
								$html .= $field->getDisplayValue($itemValue);
							} else {
								$html .= $field->getDisplayValue($itemValue) . ' ' . $currencySymbol;
							}
							break;
					}
					$html .= '</td>';
				}
			}
			$html .= '</tr>';
		}
		return $html;
	}

	/**
	 * Function get table foot.
	 *
	 * @param array  $inventoryRows
	 * @param array  $fields
	 * @param string $html
	 * @param string $currencySymbol
	 *
	 * @return string
	 */
	public function getTableFoot($inventoryRows, $fields, $html, $currencySymbol)
	{
		foreach ($fields[1] as $field) {
			if ($field->isVisible() && ($field->get('columnname') !== 'subunit')) {
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
					$html .= \CurrencyField::convertToUserFormat($sum, null, true) . ' ' . $currencySymbol;
				}
				$html .= '</td>';
			}
		}
		return $html;
	}
}
