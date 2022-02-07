<?php

/**
 * Time control list parser class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSTimeControl_List_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_TIME_CONTROL_LIST';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$moduleModel = \Vtiger_Module_Model::getInstance($this->textParser->moduleName);
		$fields = $moduleModel->getFields();
		$ids = $this->textParser->getParam('pdf')->getVariable('recordsId');
		if (!\is_array($ids)) {
			$ids = [$ids];
		}
		$html = '<table border="1" class="products-table" style="border-collapse:collapse;width:100%;"><thead><tr>';
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px4px;';
		$columns = [];
		foreach (['name', 'link', 'time_start', 'time_end', 'sum_time'] as $column) {
			$fieldModel = $fields[$column];
			if (!$fieldModel || !$fieldModel->isActiveField()) {
				continue;
			}
			$columns[$fieldModel->getName()] = $fieldModel;
			$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . '</th>';
		}
		$html .= '</tr></thead><tbody>';
		$summary = [];
		foreach ($ids as $recordId) {
			$html .= '<tr>';
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->textParser->moduleName);
			if (!$recordModel->isViewable()) {
				continue;
			}
			foreach ($columns as $column) {
				$style = $bodyStyle;
				$columnName = $column->getName();
				if (\in_array($columnName, ['time_start', 'time_end', 'due_date', 'date_start', 'sum_time'])) {
					$style = $bodyStyle . 'text-align:center;';
				}
				$html .= "<td style=\"{$style}\">" . $recordModel->getDisplayValue($columnName, $recordId, true) . '</td>';
				if ('sum_time' === $columnName) {
					$summary['sum_time'] = $summary['sum_time'] ?? 0;
					$summary['sum_time'] += $recordModel->get($columnName);
				}
			}
			$html .= '</tr>';
		}
		$html .= '</tbody><tfoot><tr>';
		foreach ($columns as $column) {
			$style = $bodyStyle;
			$columnName = $column->getName();
			$content = '';
			if ('sum_time' === $columnName) {
				$content = '<strong>' . \App\Fields\RangeTime::displayElapseTime($summary['sum_time']) . '</strong>';
				$style = $bodyStyle . 'text-align:center;';
			} elseif ('name' === $columnName) {
				$content = '<strong>' . \App\Language::translate('LBL_SUMMARY', $this->textParser->moduleName) . ':' . '</strong>';
			}
			$html .= "<td style=\"{$style}\">" . $content . '</td>';
		}
		return $html . '</tr></tfoot></table>';
	}
}
