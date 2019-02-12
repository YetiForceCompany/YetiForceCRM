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

	/** @var mixed Column names */
	protected $columnNames = ['name', ['date_start', 'time_start'], 'assigned_user_id', 'sum_time'];

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
			'.table thead th {border-bottom: 1px solid grey; width: ' . (100 / count($this->columnNames)) . '%;}' .
			'.table tbody tr {border-bottom: 1px solid grey}' .
			'.table tbody tr:nth-child(even) {background-color: #F7F7F7;}' .
			'.center {text-align: center;}' .
			'.summary {border-top: 1px solid grey;}' .
			'</style>';
		$html .= '<table class="table"><thead><tr>';
		foreach ($this->columnNames as $column) {
			if (is_array($column)) {
				$fieldModel = $fields[current($column)];
			} else {
				$fieldModel = $fields[$column];
			}
			$html .= '<th><span>' . \App\Language::translate($fieldModel->get('label'), $this->textParser->moduleName) . '</span>&nbsp;</th>';
		}
		$html .= '</tr></thead><tbody>';
		$summary = [];
		foreach ($ids as $recordId) {
			$html .= '<tr>';
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->textParser->moduleName);
			$class = '';
			foreach ($this->columnNames as $column) {
				if (is_array($column)) {
					$fieldModel = $fields[current($column)];
				} else {
					$fieldModel = $fields[$column];
				}
				$html .= '<td class="' . $class . '">' . $this->getDisplayValue($recordModel, $column) . '</td>';
				if ($column === 'sum_time') {
					$summary['sum_time'] += $recordModel->get($fieldModel->getName());
				}
				$class = 'center';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody><tfoot><tr>';
		foreach ($this->columnNames as $column) {
			$class = $content = '';
			if ($column === 'sum_time') {
				$content = '<strong>' . \App\Fields\Time::formatToHourText($summary['sum_time'], 'short') . '</strong>';
				$class = 'center';
			} elseif ($column === 'name') {
				$content = '<strong>' . \App\Language::translate('LBL_SUMMARY', $this->textParser->moduleName) . ':' . '</strong>';
			}
			$html .= '<td class="summary ' . $class . '">' . $content . '</td>';
		}
		return $html . '</tr></tfoot></table>';
	}

	/**
	 * Function to retieve display value for fields.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param string|string[]      $fields
	 *
	 * @return string
	 */
	private function getDisplayValue($recordModel, $fields)
	{
		$result = [];
		$fields = is_array($fields) ? $fields : [$fields];
		foreach ($fields as $fieldName) {
			$result[] = $recordModel->getDisplayValue($fieldName, $recordModel->getId(), true);
		}
		return implode(' ', $result);
	}
}
