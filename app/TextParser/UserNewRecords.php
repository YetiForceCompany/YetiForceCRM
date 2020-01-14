<?php

/**
 * User Accounts class.
 *
 * @package 	App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/** @var string Default template */
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
				$query->orderBy(['vtiger_crmentity.createdtime' => \SORT_DESC]);
				$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
				$dataReader = $query->createCommand()->query();
				$columns = [];
				foreach (explode(':', $this->params[2]) as $column) {
					if (!($fieldModel = $moduleModel->getFieldByColumn($column)) || !$fieldModel->isActiveField()) {
						continue;
					}
					$columns[$column] = $fieldModel;
				}
				$count = 1;
				$html = '';
				while ($row = $dataReader->read()) {
					$recordHtml = '';
					$recordModel = \Vtiger_Record_Model::getInstanceById($row['id']);
					foreach ($columns as $column) {
						if (!empty($value = $recordModel->getDisplayValue($column->getName(), false, true))) {
							$recordHtml .= $value . ' ';
						}
					}
					if (!empty($recordHtml)) {
						$html .= $count . '. <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordHtml . '</a><br>';
					}
					++$count;
				}
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
