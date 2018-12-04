<?php

/**
 * Time control list parser class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSTimeControl_List_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_TIME_CONTROL_LIST';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var mixed Column names */
	protected $columnNames = ['name', 'link', 'time_start', 'time_end', 'sum_time'];

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$moduleModel = Vtiger_Module_Model::getInstance($this->textParser->moduleName);
		$fields = $moduleModel->getFields();
		$ids = $this->textParser->getParam('pdf')->getRecordIds();
		if (!is_array($ids)) {
			$ids = [$ids];
		}
		$html = '<br /><style>' .
			'.table {width: 100%; border-collapse: collapse;}' .
			'.table thead th {border-bottom: 1px solid grey;}' .
			'.table tbody tr {border-bottom: 1px solid grey}' .
			'.table tbody tr:nth-child(even) {background-color: #F7F7F7;}' .
			'.center {text-align: center;}' .
			'.summary {border-top: 1px solid grey;}' .
			'</style>';

		$html .= '<table class="table"><thead><tr>';
		foreach ($this->columnNames as $column) {
			$fieldModel = $fields[$column];
			$html .= '<th><span>' . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . '</span>&nbsp;</th>';
		}
		$html .= '</tr></thead><tbody>';
		$summary = [];
		foreach ($ids as $recordId) {
			$html .= '<tr>';
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->textParser->moduleName);
			foreach ($this->columnNames as $column) {
				$fieldModel = $fields[$column];
				$class = '';
				if (in_array($column, ['time_start', 'time_end', 'due_date', 'date_start', 'sum_time'])) {
					$class = 'class="center"';
				}
				$html .= '<td ' . $class . '>' . $recordModel->getDisplayValue($fieldModel->getName(), $recordId, true) . '</td>';
				if ($column == 'sum_time') {
					$summary['sum_time'] += $recordModel->get($fieldModel->getName());
				}
			}
			$html .= '</tr>';
		}
		$html .= '</tbody><tfoot><tr>';
		foreach ($this->columnNames as $column) {
			$class = $content = '';
			if ($column == 'sum_time') {
				$content = '<strong>' . \App\Fields\Time::formatToHourText($summary['sum_time'], 'short') . '</strong>';
				$class = 'center';
			} elseif ($column == 'name') {
				$content = '<strong>' . \App\Language::translate('LBL_SUMMARY', $this->textParser->moduleName) . ':' . '</strong>';
			}
			$html .= '<td class="summary ' . $class . '">' . $content . '</td>';
		}
		return $html . '</tr></tfoot></table>';
	}
}
