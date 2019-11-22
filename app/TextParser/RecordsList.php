<?php
/**
 * Records list table.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\TextParser;

/**
 * RecordsList class.
 */
class RecordsList extends Base
{
	/** @var string Class name */
	public $name = 'LBL_TEXT_PARSER_RECORDS_LIST';

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
		$moduleModel = \Vtiger_Module_Model::getInstance($this->textParser->moduleName);
		$pdf = $this->textParser->getParam('pdf');
		$ids = $pdf->getVariable('recordsId');
		if (!\is_array($ids)) {
			$ids = [$ids];
		}
		$params = $this->params ?? [];
		$bodyStyle = 'font-size:9px;';

		$cvId = $this->textParser->getParam('pdf')->getVariable('viewname');
		$listView = \Vtiger_ListView_Model::getInstance($moduleModel->getName(), $cvId);
		$fields = $listView->getListViewHeaders();
		$cvId = $listView->get('viewId');
		if (\in_array('viewName', $params) && $cvId) {
			$customView = \CustomView_Record_Model::getInstanceById($cvId);
			$html .= "<div style=\"{$bodyStyle}\">" . \App\Language::translate($customView->get('viewname'), $moduleModel->getName()) . '</div>';
		}
		if (\in_array('conditions', $params)) {
			if (($value = $pdf->getVariable('search_value')) && ($operator = $pdf->getVariable('operator'))) {
				$html .= "<div style=\"{$bodyStyle}\">" . \App\Language::translate(\App\Condition::STANDARD_OPERATORS[$operator], $moduleModel->getName()) . ': ' . $value . '</div>';
			}
			if ($searchParams = $pdf->getVariable('search_params')) {
				foreach ($searchParams as $conditions) {
					foreach ($conditions as $condition) {
						$fieldModel = $fields[$condition[0]];
						$fieldName = \App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
						$value = $condition[2];
						$html .= "<div style=\"{$bodyStyle}\">" .
											$fieldName . ': ' . \App\Language::translate($value, $fieldModel->getModuleName()) .
										'</div>';
					}
				}
			}
		}

		$html .= '<table border="1" class="products-table" style="border-collapse:collapse;width:100%;"><thead><tr>';
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px4px;';

		foreach ($fields as $fieldModel) {
			$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate($fieldModel->getFieldLabel(), $this->textParser->moduleName) . '</th>';
		}

		$html .= '</tr></thead><tbody>';
		foreach ($ids as $recordId) {
			$html .= '<tr>';
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->textParser->moduleName);
			if (!$recordModel->isViewable()) {
				continue;
			}
			foreach ($fields as $fieldModel) {
				$value = '';
				if (($sourceField = $fieldModel->get('source_field_name')) && !$recordModel->isEmpty($sourceField) && \App\Record::isExists($recordModel->get($sourceField))) {
					$sourceRecordModel = \Vtiger_Record_Model::getInstanceById($recordModel->get($sourceField));
					$value = $sourceRecordModel->getDisplayValue($fieldModel->getName(), $recordModel->getId(), true);
				} elseif (!$fieldModel->get('source_field_name')) {
					$value = $recordModel->getDisplayValue($fieldModel->getName(), $recordModel->getId(), true);
				}
				$html .= "<td style=\"{$bodyStyle}\">" . $value . '</td>';
			}
			$html .= '</tr>';
		}
		return $html .= '</tbody</table>';
	}
}
