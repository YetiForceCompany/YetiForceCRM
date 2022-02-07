<?php

/**
 * User record duplicate list class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public $default = '$(custom : UserRecordsDuplicateList|__MODULE__|__FIELDS_TO_SHOW__|__FIELDS_TO_CHECK__|__LIMIT__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$html = '';
		$moduleName = $this->params[0];
		if (!empty($textParserParams = $this->textParser->getParam('textParserParams')) && isset($textParserParams['userId'])
			&& !empty($userId = $textParserParams['userId']) && \App\User::isExists($userId) && \App\Module::isModuleActive($moduleName)
		) {
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$duplicatesByFieldName = !empty($this->params[2]) ? explode(':', $this->params[2]) : [];
			$count = 1;
			$entries = [];
			$limit = (int) ($this->params[3] ?? \App\Config::performance('REPORT_RECORD_NUMBERS'));
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
					$queryGenerator = (new \App\QueryGenerator($moduleName, $userId));
					$queryGenerator->setFields(array_merge(['id'], array_keys($fields)));
					$queryGenerator->setSearchFieldsForDuplicates($duplicateByFieldName);
					$queryGenerator->setOrder($duplicateByFieldName);
					$query = $queryGenerator->createQuery();
					$dataReader = $query->createCommand()->query();
					while ($row = $dataReader->read()) {
						if (isset($entries[$row['id']])) {
							continue;
						}
						$recordHtml = '';
						$entriesPart = [];
						$recordModel = $moduleModel->getRecordFromArray($row);
						foreach ($fields as $field) {
							if ($recordModel->isEmpty($field->getName())) {
								continue;
							}
							$value = $recordModel->getDisplayValue($field->getName(), false, true);
							if ($field->isReferenceField()) {
								$relModule = \App\Record::getType($recordModel->get($field->getName()));
								if ($relModule && 'Users' !== $relModule && \App\Privilege::isPermitted($relModule, 'DetailView', $recordModel->get($field->getName()), $userId)) {
									$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($recordModel->get($field->getName()));
									$entriesPart[] = ' [<a href="' . \App\Config::main('site_URL') . $relatedRecordModel->getDetailViewUrl() . '">' . $value . '</a>] ';
								} else {
									$entriesPart[] = " [{$value}] ";
								}
							} else {
								$recordHtml .= " {$value} ";
							}
						}
						if (!empty($recordHtml)) {
							$entries[$recordModel->getId()] = "{$count}. " . ' <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordHtml . '</a> ' . implode(' ', $entriesPart);
						}
						++$count;

						if ($limit < $count) {
							break 2;
						}
					}
				}
			}
			$html = implode('<br>', $entries);
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
