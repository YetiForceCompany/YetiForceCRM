<?php

/**
 * User SSalesProcesses.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/**
	 * @var string Default template
	 *
	 * @see \App\Condition::DATE_OPERATORS
	 */
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
				$query->orderBy(['vtiger_crmentity.createdtime' => SORT_DESC]);
			} elseif (\in_array('sort_highest_value', $this->params)) {
				$query->orderBy(['estimated' => SORT_DESC]);
			}
			$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
			$dataReader = $query->createCommand()->query();
			$fields = [];
			foreach (['subject' => '', 'related_to' => '', 'estimated' => 'LBL_FOR_VALUE', 'estimated_date' => 'LBL_UNTIL_DAY', 'ssalesprocesses_status' => 'LBL_ACTUAL_STATUS'] as $fieldName => $fieldLabel) {
				if (!($fieldModel = $moduleModel->getFieldByName($fieldName)) || !$fieldModel->isActiveField()) {
					continue;
				}
				$fields[$fieldName] = $fieldLabel;
			}
			if (!empty($fields)) {
				$count = 1;
				while ($row = $dataReader->read()) {
					$relatedRecordModel = null;
					$recordModel = \Vtiger_Record_Model::getInstanceById($row['id']);
					if (($relatedTo = $recordModel->get('related_to')) && \App\Record::isExists($relatedTo)) {
						$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($relatedTo);
					}
					$html .= $count . '. ';
					foreach ($fields as $fieldName => $fieldLabel) {
						if ('subject' === $fieldName) {
							$html .= ' <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordModel->getDisplayValue($fieldName, false, true) . '</a> ';
						} elseif ('related_to' === $fieldName) {
							if (!empty($relatedRecordModel) && $recordModel->isViewable()) {
								$html .= ' [<a href="' . \App\Config::main('site_URL') . $relatedRecordModel->getDetailViewUrl() . '">' . $recordModel->getDisplayValue($fieldName, false, true) . '</a>] ';
							} else {
								$html .= ' [' . $recordModel->getDisplayValue($fieldName, false, true) . '] ';
							}
						} else {
							$html .= \App\Language::translate($fieldLabel, 'Other.Reports') . " <b>{$recordModel->getDisplayValue($fieldName, false, true)}</b> ";
						}
					}
					$html .= '<br>';
					++$count;
				}
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
