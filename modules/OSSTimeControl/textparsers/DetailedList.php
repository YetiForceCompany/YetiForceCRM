<?php
/**
 * Time control detailed list parser.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px4px;';
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
		$summary = [];
		foreach ($ids as $recordId) {
			$html .= '<tr>';
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->textParser->moduleName);
			if (!$recordModel->isViewable()) {
				continue;
			}
			foreach ($columns as $column) {
				$style = $bodyStyle;
				$styleDate = $bodyStyle . 'text-align:center;';
				$columnName = $column->getName();
				if ('date_start' === $columnName) {
					$style = $styleDate;
					$value = \App\Fields\DateTime::formatToDisplay($recordModel->getDisplayValue($columnName) . ' ' . $recordModel->getDisplayValue('time_start'));
				} else {
					$value = $recordModel->getDisplayValue($columnName);
				}
				if ('sum_time' === $columnName) {
					$style = $styleDate;
					$summary['sum_time'] = $summary['sum_time'] ?? 0;
					$summary['sum_time'] += $recordModel->get($columnName);
				}
				$html .= "<td style=\"{$style}\">" . $value . '</td>';
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
		$html .= '</tr></tfoot></table>';
		return $html;
	}
}
