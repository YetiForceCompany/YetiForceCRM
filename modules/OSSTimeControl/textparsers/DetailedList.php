<?php
/**
 * Time control detailed list parser.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Time control detailed list parser class.
 */
class OSSTimeControl_DetailedList_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_TIME_CONTROL_DETAILED_LIST';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$moduleModel = Vtiger_Module_Model::getInstance($this->textParser->moduleName);
		$fields = $moduleModel->getFields();
		$ids = $this->textParser->getParam('pdf')->getVariable('recordsId');
		if (!\is_array($ids)) {
			$ids = [$ids];
		}
		$html = '';
		$html .= '<table border="1" class="products-table" style="border-collapse:collapse;width:100%;"><thead><tr>';
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px4px;text-align:center;';
		$columns = [];
		foreach (['name',  'date_start', 'time_start', 'assigned_user_id', 'sum_time'] as $fieldName) {
			$fieldModel = $fields[$fieldName];
			if (!$fieldModel || !$fieldModel->isActiveField()) {
				continue;
			}
			if ('time_start' !== $fieldName) {
				$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->getFieldLabel(), $this->textParser->moduleName) . '</th>';
				$columns[$fieldModel->getName()] = $fieldModel;
			}
		}
		$html .= '</tr></thead><tbody>';
		$summary = ['sum_time' => 0];
		foreach ($ids as $recordId) {
			$html .= '<tr>';
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->textParser->moduleName);
			foreach ($columns as $column) {
				if ('date_start' === $column->getName()) {
					$value = \App\Fields\DateTime::formatToViewDate($recordModel->getDisplayValue($column->getName()) . ' ' . $recordModel->getDisplayValue('time_start'));
				} else {
					$value = $recordModel->getDisplayValue($column->getName());
				}
				$html .= "<td style=\"{$bodyStyle}\">" . $value . '</td>';
				if ('sum_time' === $column->getName()) {
					$summary['sum_time'] += $recordModel->get($column->getName());
				}
			}
			$html .= '</tr>';
		}
		$html .= '</tbody><tfoot><tr>';
		foreach ($columns as $column) {
			$content = '';
			if ('sum_time' === $column->getName()) {
				$content = '<strong>' . \App\Fields\RangeTime::formatHourToDisplay($summary['sum_time'], 'short') . '</strong>';
			} elseif ('name' === $column->getName()) {
				$content = '<strong>' . \App\Language::translate('LBL_SUMMARY', $this->textParser->moduleName) . ':' . '</strong>';
			}
			$html .= "<td style=\"{$bodyStyle}\">" . $content . '</td>';
		}
		return $html . '</tr></tfoot></table>';
	}
}
