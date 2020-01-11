<?php

namespace App\TextParser;

/**
 * Report Accounts class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class ReportNewRecords extends Base
{
	/** @var string Class name */
	public $name = 'LBL_REPORT_NEW_RECORDS';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		$moduleName = $this->params[0];
		if (\App\Module::isModuleActive($moduleName)) {
			$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
			$currentUserModel = \App\User::getCurrentUserModel();
			$adminUser = !$currentUserModel->isAdmin() ? \App\User::getActiveAdminId() : $currentUserModel->getId();
			$queryGenerator = (new \App\QueryGenerator($moduleName, $adminUser))
				->setFields(['id'])
				->addCondition('shownerid', $currentUserModel->getId(), 'e', false)
				->addCondition('assigned_user_id', $currentUserModel->getId(), 'e', false);
			if (isset(\App\Condition::DATE_OPERATORS[$this->params[1]])) {
				$queryGenerator->addCondition('createdtime', false, $this->params[1]);
			}
			$query = $queryGenerator->createQuery();
			$query->orderBy(['vtiger_crmentity.createdtime' => \SORT_DESC]);
			$query->limit(\App\Config::performance('REPORT_RECORD_NUMBERS'));
			$dataReader = $query->createCommand()->query();
			$columns = [];
			foreach (explode(':',$this->params[2]) as $column) {
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
					$html .= $count . '. <a href="' . \App\Config::main('site_URL') . $recordModel->getDetailViewUrl() . '">'.$recordHtml.'</a><br>';
				}
				++$count;
			}
		}
		return !empty($html) ? $html : \App\Language::translate('LBL_NO_RECORDS', 'Other.Reports');
	}
}
