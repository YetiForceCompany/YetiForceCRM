<?php

/**
 * Special function displaying storage hierarchy
 * @package YetiForce.SpecialFunction
 * @license licenses/License.html
 * @author Krzysztof Gastołek <krzysztof.gastolek@wars.pl>
 */
class Pdf_IStoragesTableHierarchy extends Vtiger_SpecialFunction_Pdf
{

	public $permittedModules = ['IStorages'];

	public function process($module, $id, Vtiger_PDF_Model $pdf)
	{
		$entity = CRMEntity::getInstance($module);
		$hierarchy = $entity->getHierarchy($id, false, false);
		$rowNum = 1;
		$thDataArray = [
			['label' => vtranslate('LBL_ROW_NUM', $module), 'width' => '10%', 'align' => 'center'],
			['label' => vtranslate('SINGLE_IStorages', $module), 'width' => '50%', 'align' => 'left'],
			['label' => vtranslate('LBL_STORAGE_OWNER', $module), 'width' => '40%', 'align' => 'center'],
		];
		$html = '<style>';
		$html.= '.storagesTable{width:100%;font-size:10px;border:1px solid #ddd;border-collapse:collapse}';
		$html.= '.storagesTable td,.storagesTable th{padding:5px}';
		$html.= '.storagesTable tbody tr:nth-child(odd){background:#eee}';
		$html.= '</style>';
		$html.= '<table class="storagesTable"><thead><tr>';
		foreach ($thDataArray as $thData) {
			$html.= '<th style="width:' . $thData['width'] . ';text-align:' . $thData['align'] . ';">' . $thData['label'] . '</th>';
		}
		$html.= '</tr></thead><tbody>';
		foreach ($hierarchy['entries'] as $entry) {
			$html.= '<tr><td style="text-align:' . $thDataArray[0]['align'] . ';">' . $rowNum . '.</td>';
			$html.= '<td style="text-align:' . $thDataArray[1]['align'] . ';">' . $entry[0] . '</td>';
			$html.= '<td style="text-align:' . $thDataArray[2]['align'] . ';">' . $entry[1] . '</td></tr>';
			$rowNum++;
		}
		$html.= '</tbody></table>';
		return $html;
	}
}
