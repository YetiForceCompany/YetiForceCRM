<?php

namespace App\TextParser;

/**
 * Report calendar class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class ReportCalendar extends Base
{
	/** @var string Class name */
	public $name = 'LBL_REPORT_CALENDAR';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$moduleName = 'Calendar';
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$currentUserModel = \App\User::getCurrentUserModel();
		$adminUser = !$currentUserModel->isAdmin() ? \App\User::getActiveAdminId() : $currentUserModel->getId();
		$queryGenerator = (new \App\QueryGenerator($moduleName, $adminUser))
			->setFields(['id'])
			->addCondition('shownerid', $currentUserModel->getId(), 'e', false)
			->addCondition('assigned_user_id', $currentUserModel->getId(), 'e', false)
			->addNativeCondition(['not', ['vtiger_activity.status' => 'PLL_OVERDUE']]);
		if (isset(\App\Condition::DATE_OPERATORS[$this->params[0]])) {
			$queryGenerator->addCondition('createdtime', false, $this->params[0]);
		}
		$query = $queryGenerator->createQuery();
		$query->orderBy(['vtiger_crmentity.createdtime' => \SORT_DESC]);
		$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
		$dataReader = $query->createCommand()->query();
		$columns = [];
		foreach (['date_start', 'subject', 'smownerid', 'shownerid'] as $column) {
			if (!($fieldModel = $moduleModel->getFieldByColumn($column)) || !$fieldModel->isActiveField()) {
				continue;
			}
			$columns[$column] = $fieldModel;
		}
		$html = '';
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
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
