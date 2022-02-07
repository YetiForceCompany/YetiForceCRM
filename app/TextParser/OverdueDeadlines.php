<?php

namespace App\TextParser;

/**
 * Oustanding deadlines class.
 *
 * @package TextParser
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OverdueDeadlines extends Base
{
	/** @var string Class name */
	public $name = 'LBL_OVERDUE_DEADLINES';

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
		$queryGenerator = new \App\QueryGenerator($moduleName, $adminUser);
		$queryGenerator->setFields(['id']);
		$queryGenerator->addNativeCondition(['vtiger_activity.status' => 'PLL_OVERDUE']);
		$queryGenerator->addNativeCondition(['vtiger_crmentity.smownerid' => $currentUserModel->getId()]);
		$query = $queryGenerator->createQuery();
		$query->limit(500);
		$dataReader = $query->createCommand()->query();
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px 4px;';
		$html = '<table border="1" class="products-table" style="border-collapse:collapse;width:100%;"><thead><tr>';
		$columns = [];
		foreach (['subject', 'activitytype', 'date_start', 'link'] as $column) {
			if (!($fieldModel = $moduleModel->getFieldByColumn($column)) || !$fieldModel->isActiveField()) {
				continue;
			}
			$columns[$column] = $fieldModel;
			$html .= "<th style=\"{$headerStyle}\"><span>" . \App\Language::translate($fieldModel->get('label'), $moduleName) . '</span></th>';
		}
		$html .= '</tr></thead><tbody>';
		$counter = 0;
		while ($row = $dataReader->read()) {
			++$counter;
			$html .= '<tr class="row-' . $counter . '">';
			foreach ($columns as $column) {
				$columnName = $column->getName();
				$recordId = $row['id'];
				$recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
				$style = $bodyStyle;
				if (\in_array($columnName, ['activitytype', 'date_start'])) {
					$style = $bodyStyle . 'text-align:center;';
				}
				$html .= "<td style=\"{$style}\">" . $recordModel->getDisplayValue($columnName, false, true) . '</td>';
			}
			$html .= '</tr>';
		}
		return $html . '</tbody></table>';
	}
}
