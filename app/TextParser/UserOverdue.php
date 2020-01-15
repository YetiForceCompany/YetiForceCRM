<?php

/**
 * User overdue.
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
				$queryGenerator = (new \App\QueryGenerator($moduleName))
					->setFields(['id'])
					->addCondition('shownerid', $userId, 'e', false)
					->addCondition('assigned_user_id', $userId, 'e', false)
					->addCondition('activitystatus', 'PLL_OVERDUE', 'e', true);
				$query = $queryGenerator->createQuery();
				$query->orderBy(['vtiger_crmentity.createdtime' => \SORT_DESC]);
				$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
				$dataReader = $query->createCommand()->query();
				while ($row = $dataReader->read()) {
					$recordModel = \Vtiger_Record_Model::getInstanceById($row['id']);
					$html .= $recordModel->getDisplayValue('date_start', false, true) . ' - <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">' . $recordModel->getName() . '</a><br>';
				}
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
