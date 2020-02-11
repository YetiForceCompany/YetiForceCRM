<?php

/**
 * User record duplicate list class.
 *
 * @package 	App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\TextParser;

/**
 *  User Records Duplicate List class.
 */
class UserRecordsDuplicateList extends Base
{
	/** @var string Class name */
	public $name = 'LBL_USER_RECORD_DUPLICATE_LIST';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = '$(custom : UserRecordsDuplicateList|__MODULE__|__FIELDS_TO_SHOW__|__FIELDS_TO_CHECK__)$';

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
			$duplicatesByFieldName = !empty($this->params[2]) ? explode(':', $this->params[2]) : [];
			$count = 1;
			$entries = [];
			foreach ($duplicatesByFieldName as $duplicateByFieldName) {
				$fields = [];
				$fieldsName = !empty($this->params[1]) ? explode(':', $this->params[1]) : $moduleModel->getNameFields();
				foreach ($fieldsName as $fieldName) {
					if (!($fieldModel = $moduleModel->getFieldByName($fieldName)) || !$fieldModel->isActiveField()) {
						continue;
					}
					$fields[$fieldName] = $fieldModel;
				}
				if (!empty($fields)) {
					$queryGenerator = (new \App\QueryGenerator($moduleName))->addCondition('assigned_user_id', $userId, 'e', false);
					$queryGenerator->setFields(array_merge(['id'], array_keys($fields)));
					$queryGenerator->setSearchFieldsForDuplicates($duplicateByFieldName);
					$query = $queryGenerator->createQuery();
					$dataReader = $query->createCommand()->query();
					while ($row = $dataReader->read()) {
						$recordHtml = '';
						$entriesPart = [];
						$relatedRecordModel = null;
						$recordModel = $moduleModel->getRecordFromArray($row);
						if (($relatedTo = $recordModel->get('related_to')) && \App\Record::isExists($relatedTo)) {
							$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($relatedTo);
						}
						foreach ($fields as $field) {
							if ($recordModel->isEmpty($field->getName())) {
								continue;
							}
							$value = $recordModel->getDisplayValue($fieldName, false, true);
							if ('related_to' === $fieldName) {
								if (!empty($relatedRecordModel) && $recordModel->isViewable()) {
									$entries[] = ' [<a href="' . \App\Config::main('site_URL') . $relatedRecordModel->getDetailViewUrl() . '">' . $value . '</a>] ';
								} else {
									$entries[] = " [{$value}] ";
								}
							} else {
								$recordHtml .= " {$value} ";
							}
						}
						if (!empty($recordHtml)) {
							$entries[$recordModel->getId()] = "{$count}. " . implode(' ', $entriesPart) . ' <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordHtml . '</a>';
						}
						++$count;
					}
				}
			}
			$html = implode('<br>', $entries);
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
