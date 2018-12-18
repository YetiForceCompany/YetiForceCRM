<?php

namespace App\TextParser;

/**
 * Products table related module class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ProductsTableRelatedModule extends Base
{
	/** @var string Class name */
	public $name = 'LBL_PRODUCTS_TABLE_RELATED_MODULE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var array Allowed modules */
	public $allowedModules = ['IGRNC', 'IGDNC'];

	/** @var array Related modules fields */
	protected $relatedModulesFields = ['IGRNC' => 'igrnid', 'IGDNC' => 'igdnid'];

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		$relatedModuleRecordId = $this->textParser->recordModel->get($this->relatedModulesFields[$this->textParser->moduleName]);
		$relatedModuleRecordModel = \Vtiger_Record_Model::getInstanceById($relatedModuleRecordId);
		if (!$relatedModuleRecordModel->getModule()->isInventory()) {
			return $html;
		}
		$relatedModuleName = $relatedModuleRecordModel->getModuleName();
		$inventoryField = \Vtiger_InventoryField_Model::getInstance($relatedModuleName);
		$fields = $inventoryField->getFields(true);
		$inventoryRows = $relatedModuleRecordModel->getInventoryData();
		if (\count($fields[1])) {
			$fieldsTextAlignRight = ['TotalPrice', 'Tax', 'MarginP', 'Margin', 'Purchase', 'Discount', 'NetPrice', 'GrossPrice', 'UnitPrice', 'Quantity'];
			$html .= '<table style="width:100%;border-collapse:collapse;"><thead><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<th style="padding:0px 4px;text-align:center;">' . \App\Language::translate($field->get('label'), $this->textParser->moduleName) . '</th>';
				}
			}
			$html .= '</tr></thead><tbody>';
			$counter = 1;
			foreach ($inventoryRows as &$inventoryRow) {
				$html .= '<tr>';
				foreach ($fields[1] as $field) {
					if (!$field->isVisible()) {
						continue;
					}
					if ($field->getName() == 'ItemNumber') {
						$html .= '<td style="padding:0px 4px;border:1px solid #ddd;"><strong>' . $counter++ . '</strong></td>';
					} elseif ($field->get('columnname') == 'ean') {
						$code = $inventoryRow[$field->get('columnname')];
						$html .= '<td style="padding:0px 4px;border:1px solid #ddd;"><barcode code="' . $code . '" type="EAN13" size="0.5" height="0.5" class="barcode" /></td>';
					} elseif ($field->isVisible()) {
						$itemValue = $inventoryRow[$field->get('columnname')];
						$html .= '<td style="border:1px solid #ddd;' . (in_array($field->getName(), $fieldsTextAlignRight) ? 'text-align:right;' : '') . '">';
						switch ($field->getTemplateName('DetailView', $this->textParser->moduleName)) {
							case 'DetailViewName.tpl':
								$html .= '<strong>' . $field->getDisplayValue($itemValue) . '</strong>';
								if (isset($fields[2]['comment' . $inventoryRow['seq']])) {
									$COMMENT_FIELD = $fields[2]['comment' . $inventoryRow['seq']];
									$comment = $COMMENT_FIELD->getDisplayValue($inventoryRow[$COMMENT_FIELD->get('columnname')]);
									if ($comment) {
										$html .= '<br />' . $comment;
									}
								}
								break;
							case 'DetailViewBase.tpl':
								$html .= $field->getDisplayValue($itemValue);
								break;
							default:
								break;
						}
						$html .= '</td>';
					}
				}
				$html .= '</tr>';
			}
			$html .= '</tbody><tfoot><tr>';
			foreach ($fields[1] as $field) {
				if ($field->isVisible()) {
					$html .= '<th style="padding:0px 4px;text-align:right">';
					if ($field->isSummary()) {
						$sum = 0;
						foreach ($inventoryRows as &$inventoryRow) {
							$sum += $inventoryRow[$field->get('columnname')];
						}
						$html .= \CurrencyField::convertToUserFormat($sum, null, true);
					}
					$html .= '</th>';
				}
			}
			$html .= '</tr></tfoot></table>';
		}
		return $html;
	}
}
