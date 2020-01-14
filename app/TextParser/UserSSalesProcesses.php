<?php

/**
 * User SSalesProcesses class.
 *
 * @package 	App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\TextParser;

/**
 *  UserSSalesProcesses class.
 */
class UserSSalesProcesses extends Base
{
	/** @var string Class name */
	public $name = 'LBL_REPORT_SSALESPROCESSES';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = '$(custom : UserSSalesProcesses|__DATE_OPERATOR__|__SORT_CONDITION__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$html = '';
		$moduleName = 'SSalesProcesses';
		if (!empty($textParserParams = $this->textParser->getParam('textParserParams')) && isset($textParserParams['userId'])) {
			$userId = $textParserParams['userId'];
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$queryGenerator = (new \App\QueryGenerator($moduleName))
				->setFields(['id'])
				->addCondition('shownerid', $userId, 'e', false)
				->addCondition('assigned_user_id', $userId, 'e', false);
			if (\in_array('without_description', $this->params)) {
				$queryGenerator->addCondition('description', false, 'y');
			} elseif (isset($this->params[0], \App\Condition::DATE_OPERATORS[$this->params[0]])) {
				$queryGenerator->addCondition('createdtime', false, $this->params[0]);
			}
			if (\in_array('only_success', $this->params)) {
				$queryGenerator->addCondition('ssalesprocesses_status', 'PLL_SALE_COMPLETED', 'e');
			}
			$query = $queryGenerator->createQuery();
			if (\in_array('sort_newest', $this->params)) {
				$query->orderBy(['vtiger_crmentity.createdtime' => \SORT_DESC]);
			} elseif (\in_array('sort_highest_value', $this->params)) {
				$query->orderBy(['estimated' => \SORT_DESC]);
			}
			$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
			$dataReader = $query->createCommand()->query();
			$columns = [];
			foreach (['subject', 'related_to', 'estimated', 'estimated_date', 'ssalesprocesses_status'] as $column) {
				if (!($fieldModel = $moduleModel->getFieldByColumn($column)) || !$fieldModel->isActiveField()) {
					continue;
				}
				$columns[$column] = $fieldModel;
			}
			if (!empty($columns)) {
				$count = 1;
				while ($row = $dataReader->read()) {
					$relatedRecordModel = null;
					$recordModel = \Vtiger_Record_Model::getInstanceById($row['id']);
					if (($relatedTo = $recordModel->get('related_to')) && \App\Record::isExists($relatedTo)) {
						$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($relatedTo);
					}
					$html .= $count . '. ';
					if (isset($columns['subject'])) {
						$html .= ' <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordModel->getDisplayValue($columns['subject']->getName(), false, true) . '</a> ';
					}
					if (isset($columns['related_to'])) {
						if (!empty($relatedRecordModel) && $recordModel->isViewable()) {
							$html .= ' [<a href="' . \App\Config::main('site_URL') . $relatedRecordModel->getDetailViewUrl() . '">' . $recordModel->getDisplayValue($columns['related_to']->getName(), false, true) . '</a>] ';
						} else {
							$html .= ' [' . $recordModel->getDisplayValue($columns['related_to']->getName(), false, true) . '] ';
						}
					}
					if (isset($columns['estimated'])) {
						$html .= \App\Language::translate('LBL_FOR_VALUE', 'Other.Reports') . " <b>{$recordModel->getDisplayValue($columns['estimated']->getName(), false, true)}</b> ";
					}
					if (isset($columns['estimated_date'])) {
						$html .= \App\Language::translate('LBL_UNTIL_DAY', 'Other.Reports') . " <b>{$recordModel->getDisplayValue($columns['estimated_date']->getName(), false, true)}</b> ";
					}
					if (isset($columns['ssalesprocesses_status'])) {
						$html .= \App\Language::translate('LBL_ACTUAL_STATUS', 'Other.Reports') . " <b>{$recordModel->getDisplayValue($columns['ssalesprocesses_status']->getName(), false, true)}</b> ";
					}
					$html .= '<br>';
					++$count;
				}
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
