<?php

namespace App\TextParser;

/**
 * Report overdue class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class ReportOverdue extends Base
{
	/** @var string Class name */
	public $name = 'LBL_REPORT_OVERDUE';

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
		return $html;
	}
}
