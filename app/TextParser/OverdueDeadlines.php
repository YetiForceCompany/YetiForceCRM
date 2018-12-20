<?php

namespace App\TextParser;

/**
 * Oustanding deadlines class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OverdueDeadlines extends Base
{
	/** @var string Class name */
	public $name = 'LBL_OVERDUE_DEADLINES';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/** @var string[] Column names */
	protected $columnNames = ['subject', 'activitytype', 'date_start', 'link'];

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		if (!$this->textParser->recordModel || !$this->textParser->recordModel->getModule()->isInventory()) {
			return '';
		}
		$moduleName = 'Calendar';
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$fields = $moduleModel->getFields();
		$currentUserModel = \App\User::getCurrentUserModel();
		$adminUser = !$currentUserModel->isAdmin() ? \App\User::getActiveAdminId() : $currentUserModel->getId();

		$queryGenerator = new \App\QueryGenerator($moduleName, $adminUser);
		$queryGenerator->setFields(['id']);
		$queryGenerator->addNativeCondition(['vtiger_activity.status' => 'PLL_OVERDUE']);
		$queryGenerator->addNativeCondition(['vtiger_crmentity.smownerid' => $currentUserModel->getId()]);
		$query = $queryGenerator->createQuery();
		$query->limit(500);
		$dataReader = $query->createCommand()->query();
		$html = '<table style="border-collapse:collapse;"><thead><tr>';
		foreach ($this->columnNames as $column) {
			$fieldModel = $fields[$column];
			$html .= '<th><span>' . \App\Language::translate($fieldModel->get('label'), $moduleName) . '</span></th>';
		}
		$html .= '</tr></thead><tbody>';
		while ($row = $dataReader->read()) {
			$html .= '<tr>';
			foreach ($this->columnNames as $column) {
				$recordId = $row['id'];
				$recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
				$style = '';
				if (in_array($column, ['activitytype', 'date_start', 'link'])) {
					$style = 'style="padding:0px 4px;text-align:center;border:1px solid #ddd;"';
				}
				$fieldModel = $fields[$column];
				if ($column == 'link') {
					$linkId = $recordModel->get('link');
					if (!empty($linkId) && \App\Record::isExists($linkId)) {
						$processRecordModel = \Vtiger_Record_Model::getInstanceById($linkId);
						$value = $processRecordModel->getName();
					} else {
						$value = '';
					}
				} else {
					$value = $recordModel->getDisplayValue($fieldModel->getName(), $recordId, true);
				}

				$html .= '<td ' . $style . '>' . $value . '</td>';
			}
			$html .= '</tr>';
		}
		return $html . '</tbody></table>';
	}
}
