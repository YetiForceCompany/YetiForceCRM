<?php

/**
 * User record filtered list class.
 *
 * @package 	App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\TextParser;

/**
 *  User Records Filtered List class.
 */
class UserRecordsFilteredList extends Base
{
	/** @var string Class name */
	public $name = 'LBL_USER_RECORD_FILTERED_LIST';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = '$(custom : UserRecordsFilteredList|__MODULE__|__FIELDS_TO_SHOW__|__FIELDS_TO_CHECK__|__OPERATOR__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$html = '';
		$moduleName = $this->params[0];
		if (!empty($textParserParams = $this->textParser->getParam('textParserParams')) && isset($textParserParams['userId']) &&
			!empty($userId = $textParserParams['userId']) && \App\User::isExists($userId) && \App\Module::isModuleActive($moduleName)
		) {
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$emptyFieldsName = !empty($this->params[2]) ? explode(':', $this->params[2]) : [];
			$fields = $emptyFields = [];
			$fieldsName = !empty($this->params[1]) ? explode(':', $this->params[1]) : $moduleModel->getNameFields();
			foreach ($fieldsName as $fieldName) {
				if (!($fieldModel = $moduleModel->getFieldByName($fieldName)) || !$fieldModel->isActiveField()) {
					continue;
				}
				$fields[$fieldName] = $fieldModel;
			}
			foreach ($emptyFieldsName as $emptyFieldName) {
				if (!($emptyFieldModel = $moduleModel->getFieldByName($emptyFieldName)) || !$emptyFieldModel->isActiveField()) {
					continue;
				}
				$emptyFields[$emptyFieldName] = $emptyFieldModel;
			}
			$queryGenerator = (new \App\QueryGenerator($moduleName))
				->addCondition('assigned_user_id', $userId, 'e', false)
				->setFields(array_merge(['id'], array_keys($fields)));
			if (isset(\App\Condition::STANDARD_OPERATORS[$this->params[3]])) {
				foreach ($emptyFieldsName as $field) {
					$queryGenerator->addCondition($field, false, $this->params[3], false);
				}
			}
			$query = $queryGenerator->createQuery();
			$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
			$dataReader = $query->createCommand()->query();
			if (!empty($fields)) {
				$count = 1;
				while ($row = $dataReader->read()) {
					$relatedRecordModel = null;
					$recordModel = $moduleModel->getRecordFromArray($row);
					if (($relatedTo = $recordModel->get('related_to')) && \App\Record::isExists($relatedTo)) {
						$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($relatedTo);
					}
					$html .= $count . '. ';
					foreach ($fields as $fieldName => $fieldModel) {
						if ('related_to' === $fieldName) {
							if (!empty($relatedRecordModel) && $recordModel->isViewable()) {
								$html .= ' [<a href="' . \App\Config::main('site_URL') . $relatedRecordModel->getDetailViewUrl() . '">' . $recordModel->getDisplayValue($fieldName, false, true) . '</a>] ';
							} else {
								$html .= ' [' . $recordModel->getDisplayValue($fieldName, false, true) . '] ';
							}
						} else {
							$html .= " {$recordModel->getDisplayValue($fieldName, false, true)} ";
						}
					}
					if ('y' === $this->params[3]) {
						$emptyFieldsValue = [];
						foreach ($emptyFields as $emptyFieldName => $emptyFieldModel) {
							if (empty($recordModel->getValueByField($emptyFieldName))) {
								$emptyFieldsValue[] = \App\Language::translate($emptyFieldModel->get('label'), $moduleName);
							}
						}
						$html .= '[' . \App\Language::translate('LBL_EMPTY_FIELDS', 'Other.Reports') . ': ' . implode(', ', $emptyFieldsValue) . ']';
					}
					$html .= ' <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . \App\Language::translate('LBL_GO_TO_PREVIEW') . '</a><br>';
					++$count;
				}
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
