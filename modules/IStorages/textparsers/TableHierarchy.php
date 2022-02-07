<?php

/**
 * IStorages storage hierarchy parser class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class IStorages_TableHierarchy_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_TABLE_HIERARCHY';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$hierarchy = $this->textParser->recordModel->getEntity()->getHierarchy($this->textParser->record, false, false);
		$rowNum = 1;
		$thDataArray = [
			['label' => \App\Language::translate('LBL_ROW_NUM', $this->textParser->moduleName), 'width' => '10%', 'align' => 'center'],
			['label' => \App\Language::translate('SINGLE_IStorages', $this->textParser->moduleName), 'width' => '50%', 'align' => 'left'],
			['label' => \App\Language::translate('LBL_STORAGE_OWNER', $this->textParser->moduleName), 'width' => '40%', 'align' => 'center'],
		];
		$html = '<style>';
		$html .= '.storagesTable{width:100%;font-size:10px;border:1px solid #ddd;border-collapse:collapse}';
		$html .= '.storagesTable td,.storagesTable th{padding:5px}';
		$html .= '.storagesTable tbody tr:nth-child(odd){background:#eee}';
		$html .= '</style>';
		$html .= '<table class="storagesTable"><thead><tr>';
		foreach ($thDataArray as $thData) {
			$html .= '<th style="width:' . $thData['width'] . ';text-align:' . $thData['align'] . ';">' . $thData['label'] . '</th>';
		}
		$html .= '</tr></thead><tbody>';
		foreach ($hierarchy['entries'] as $entry) {
			$html .= '<tr><td style="text-align:' . $thDataArray[0]['align'] . ';">' . $rowNum . '.</td>';
			$html .= '<td style="text-align:' . $thDataArray[1]['align'] . ';">' . $entry[0] . '</td>';
			$html .= '<td style="text-align:' . $thDataArray[2]['align'] . ';">' . $entry[1] . '</td></tr>';
			++$rowNum;
		}
		return $html . '</tbody></table>';
	}
}
