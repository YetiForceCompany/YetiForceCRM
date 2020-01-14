<?php

/**
 * User overdue class.
 *
 * @package 	App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\TextParser;

/**
 * UserOverdue class.
 */
class UserOverdue extends Base
{
	/** @var string Class name */
	public $name = 'LBL_REPORT_OVERDUE';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string Default template */
	public $default = '$(custom : UserOverdue)$';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process(): string
	{
		$html = '';
		$moduleName = 'Calendar';
		if (!empty($textParserParams = $this->textParser->getParam('textParserParams')) && isset($textParserParams['userId'])) {
			$userId = $textParserParams['userId'];
			if (!empty($userId) && \App\User::isExists($userId) && \App\Module::isModuleActive($moduleName)) {
				$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
				$queryGenerator = (new \App\QueryGenerator($moduleName))
					->setFields(['id'])
					->addCondition('shownerid', $userId, 'e', false)
					->addCondition('assigned_user_id', $userId, 'e', false)
					->addNativeCondition(['vtiger_activity.status' => 'PLL_OVERDUE']);
				$query = $queryGenerator->createQuery();
				$query->orderBy(['vtiger_crmentity.createdtime' => \SORT_DESC]);
				$query->limit(10);
				$dataReader = $query->createCommand()->query();
				$columns = [];
				foreach (['date_start', 'subject'] as $column) {
					if (!($fieldModel = $moduleModel->getFieldByColumn($column)) || !$fieldModel->isActiveField()) {
						continue;
					}
					$columns[$column] = $fieldModel;
				}
				while ($row = $dataReader->read()) {
					$recordHtml = '';
					foreach ($columns as $column) {
						$nextColumn = next($columns);
						$recordModel = \Vtiger_Record_Model::getInstanceById($row['id']);
						if (!empty($value = $recordModel->getDisplayValue($column->getName(), false, true))) {
							$recordHtml .= '' . $value;
							$recordHtml .= $nextColumn && !empty($recordModel->getDisplayValue($nextColumn->getName(), false, true)) ? ' - ' : '';
						}
					}
					if (!empty($recordHtml)) {
						$html .= '<a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordHtml . '</a><br>';
					}
				}
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
