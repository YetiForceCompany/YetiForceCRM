<?php

/**
 * User Accounts.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\TextParser;

/**
 * UserNewRecords class.
 */
class UserNewRecords extends Base
{
	/** @var string Class name */
	public $name = 'LBL_REPORT_NEW_RECORDS';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * @var string Default template
	 *
	 * @see \App\Condition::DATE_OPERATORS
	 */
	public $default = '$(custom : UserNewRecords|__MODULE_NAME__|__DATE_OPERATOR__|__FIELDS_LIST__)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$html = '';
		$moduleName = $this->params[0];
		if (!empty($textParserParams = $this->textParser->getParam('textParserParams')) && isset($textParserParams['userId'])) {
			$userId = $textParserParams['userId'];
			if (!empty($userId) && \App\User::isExists($userId) && \App\Module::isModuleActive($moduleName)) {
				$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
				$queryGenerator = (new \App\QueryGenerator($moduleName))
					->setFields(['id'])
					->addCondition('shownerid', $userId, 'e', false)
					->addCondition('assigned_user_id', $userId, 'e', false);
				if (isset(\App\Condition::DATE_OPERATORS[$this->params[1]])) {
					$queryGenerator->addCondition('createdtime', false, $this->params[1]);
				}
				$query = $queryGenerator->createQuery();
				$query->orderBy(['vtiger_crmentity.createdtime' => SORT_DESC]);
				$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
				$dataReader = $query->createCommand()->query();
				$fields = [];
				foreach (explode(':', $this->params[2]) as $fieldName) {
					if (!($fieldModel = $moduleModel->getFieldByName($fieldName)) || !$fieldModel->isActiveField()) {
						continue;
					}
					$fields[$fieldName] = $fieldModel;
				}
				$count = 1;
				$entries = [];
				while ($row = $dataReader->read()) {
					$recordHtml = '';
					$recordModel = \Vtiger_Record_Model::getInstanceById($row['id']);
					foreach ($fields as $field) {
						if (!empty($value = $recordModel->getDisplayValue($field->getName(), false, true))) {
							$recordHtml .= $value . ' ';
						}
					}
					if (!empty($recordHtml)) {
						$entries[] = $count . '. <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordHtml . '</a>';
					}
					++$count;
				}
				$html = implode('<br>', $entries);
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
