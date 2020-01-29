<?php

/**
 * User Accounts.
 *
 * @package 	App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\TextParser;

/**
 * UserNewRecords class.
 */
class UserRecordsList extends Base
{
	/** @var string Class name */
	public $name = 'LBL_REPORT_USER_RECORDS_LIST';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * @var string Default template
	 *
	 * @see \App\Condition::DATE_OPERATORS
	 */
	public $default = '$(custom : UserRecordsList|__MODULE_NAME__|__DATE_OPERATOR__|__FIELDS_LIST__)$';

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
			$queryGenerator = (new \App\QueryGenerator($moduleName));
			$moduleModel = $queryGenerator->getModuleModel();

			$fields = [];
			$fieldsName = !empty($this->params[2]) ? explode(':', $this->params[2]) : $moduleModel->getNameFields();
			foreach ($fieldsName as $fieldName) {
				$fieldModel = \is_object($fieldName) ? $fieldName : $moduleModel->getFieldByName($fieldName);
				if (!$fieldModel || !$fieldModel->isActiveField()) {
					continue;
				}
				$fields[$fieldModel->getName()] = $fieldModel;
			}
			if ($fields) {
				$queryGenerator
					->setFields(array_keys($fields))
					->setField('id')
					->addCondition('assigned_user_id', $userId, 'e', false);
				if (isset(\App\Condition::DATE_OPERATORS[$this->params[1]])) {
					$queryGenerator->addCondition('createdtime', false, $this->params[1]);
				}
				$query = $queryGenerator->createQuery();
				$query->orderBy(['vtiger_crmentity.createdtime' => \SORT_ASC]);
				$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
				$dataReader = $query->createCommand()->query();
				$count = 1;
				$entries = [];
				while ($row = $dataReader->read()) {
					$recordHtml = '';
					$entriesPart = [];
					$recordModel = $moduleModel->getRecordFromArray($row);
					foreach ($fields as $field) {
						if ($recordModel->isEmpty($field->getName())) {
							continue;
						}
						$value = $recordModel->get($field->getName());
						if (
							$field->isReferenceField() &&
							!\in_array('Users', $field->getReferenceList())
							) {
							if (\App\Privilege::isPermitted(\App\Record::getType($value), 'DetailView', $value, $userId)) {
								$relatedRecordModel = \Vtiger_Record_Model::getInstanceById($value);
								$entriesPart[] = '<a href="' . \App\Config::main('site_URL') . $relatedRecordModel->getDetailViewUrl() . '">' . $relatedRecordModel->getName() . '</a>';
							} else {
								$entriesPart[] = \App\Record::getLabel($value);
							}
						} elseif (!empty($value = $recordModel->getDisplayValue($field->getName(), false, true))) {
							$recordHtml .= rtrim($value, '<br>') . ' ';
						}
					}
					if (!empty($recordHtml)) {
						if ('ModComments' === $moduleName) {
							$entries[] = "{$count}. " . ($entriesPart ? implode(', ', $entriesPart) . '<br>  ' : '') . " {$recordHtml} ";
						} else {
							$entries[] = "{$count}. " . implode(' ', $entriesPart) . ' <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordHtml . '</a>';
						}
					}
					++$count;
				}
				$html = implode('<br>', $entries);
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
