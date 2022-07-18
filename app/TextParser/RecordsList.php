<?php
/**
 * Records list table.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$cvId = $pdf->getVariable('viewname');
		$listView = \Vtiger_ListView_Model::getInstance($moduleModel->getName(), $cvId);
		$fields = $listView->getListViewHeaders();
		$cvId = $listView->get('viewId');
		if (\in_array('viewName', $params) && $cvId) {
			$customView = \CustomView_Record_Model::getInstanceById($cvId);
			$html .= "<div style=\"{$bodyStyle}\">" . \App\Language::translate($customView->get('viewname'), $moduleModel->getName()) . '</div>';
		}
		if (\in_array('conditions', $params)) {
			$roles = [];
			if (($value = $pdf->getVariable('search_value')) && ($operator = $pdf->getVariable('operator')) && ($fieldModel = $fields[$pdf->getVariable('search_key')])) {
				$roles[] = [
					'fieldname' => $fieldModel->getCustomViewSelectColumnName((string) $fieldModel->get('source_field_name')),
					'operator' => $operator,
					'value' => $value,
				];
			}
			if ($searchParams = $pdf->getVariable('search_params')) {
				$roles[] = $listView->getQueryGenerator()->parseSearchParams($searchParams);
			}
			$roles[] = \App\CustomView::getConditions($cvId);
			$conditions = [
				'condition' => 'AND',
				'rules' => array_filter($roles),
			];
			$html .= $this->parseConditions($conditions, $listView->getQueryGenerator());
		}
		$html .= '<table class="records-list" style="border-collapse:collapse;width:100%;border:1px solid  #ddd;"><thead><tr>';
		$headerStyle = 'text-align:center;background-color:#ddd;';
		$bodyStyle = 'border:1px solid #ddd;padding:4px; ';
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
					$value = $fieldModel->getUITypeModel()->getTextParserDisplayValue($sourceRecordModel->get($fieldModel->getName()), $sourceRecordModel, []);
				} elseif (!$fieldModel->get('source_field_name')) {
					$value = $fieldModel->getUITypeModel()->getTextParserDisplayValue($recordModel->get($fieldModel->getName()), $recordModel, []);
				}
				$html .= "<td style=\"{$bodyStyle}\">" . $value . '</td>';
			}
			$html .= '</tr>';
		}
		return $html .= '</tbody</table>';
	}

	/**
	 * Specific parse conditions.
	 *
	 * @param array|null          $conditions
	 * @param \App\QueryGenerator $queryGenerator
	 * @param string              $html
	 * @param bool                $show
	 *
	 * @return string
	 */
	private function parseConditions(?array $conditions, \App\QueryGenerator $queryGenerator, string $html = '', bool $show = false): string
	{
		if (empty($conditions)) {
			return '';
		}
		$group = strtoupper($conditions['condition']);
		$groupTranslation = \App\Language::translate("LBL_{$group}");
		$count = $counter = \count($conditions['rules']);
		$showGroup = $show || 'AND' !== $group;
		foreach ($conditions['rules'] as $rule) {
			if (isset($rule['condition'])) {
				$showPartGroup = strtoupper($rule['condition']) !== $group;
				$text = $this->parseConditions($rule, $queryGenerator, '', $showPartGroup);
				if ($showPartGroup) {
					$html .= "<div style=\"font-size:9px;\">{$groupTranslation} " . (\count($rule['rules']) > 1 ? "({$text})" : $text) . '</div>';
				} else {
					$html .= $text;
				}
			} else {
				[$fieldName, $moduleName, $sourceFieldName] = array_pad(explode(':', $rule['fieldname']), 3, false);
				if ($sourceFieldName) {
					$queryField = $queryGenerator->getQueryRelatedField([
						'relatedModule' => $moduleName,
						'relatedField' => $fieldName,
						'sourceField' => $sourceFieldName,
					]);
				} else {
					$queryField = $queryGenerator->getQueryField($fieldName);
				}
				$value = [];
				$queryField->setValue($rule['value']);
				$queryField->setOperator($rule['operator']);
				$operator = $queryField->getOperator();
				$fieldModel = $queryField->getField();
				$queryValue = method_exists($queryField, 'getArrayValue') ? $queryField->getArrayValue() : $queryField->getValue();
				if (!\is_array($queryValue)) {
					$separator = '##';
					$queryValue = false !== strpos($queryValue, $separator) ? explode($separator, $queryValue) : [$queryValue];
				}
				foreach ($queryValue as $val) {
					if ($fieldModel->isReferenceField()) {
						$value[] = \App\Language::translate($val, $fieldModel->getModuleName());
					} else {
						$value[] = \App\Language::translate($fieldModel->getDisplayValue($val, false, false, true), $fieldModel->getModuleName());
					}
				}
				$value = implode(', ', $value);

				if (!($operatorLabel = \App\Condition::STANDARD_OPERATORS[$operator] ?? '')) {
					$operatorLabel = \App\Condition::DATE_OPERATORS[$operator] ?? '';
				}
				$pre = '<span style="font-size:9px;">' .
					\App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName()) . '  <strong>' .
					lcfirst(\App\Language::translate($operatorLabel, $fieldModel->getModuleName())) . '</strong> ' .
					$value . '</span>';

				if ($counter && $count !== $counter && $showGroup) {
					$html .= "<span style=\"font-size:9px;\">{$groupTranslation} </span>{$pre}";
				} elseif (!$show) {
					$html .= "<div style=\"font-size:9px;\">{$pre}</div>";
				} else {
					$html .= "{$pre}";
				}
			}
			--$counter;
		}
		return $html;
	}
}
