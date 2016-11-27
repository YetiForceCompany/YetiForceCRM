<?php

/**
 * Special function displaying oustanding deadlines
 * @package YetiForce.PDF
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Pdf_OverdueDeadlines extends Vtiger_SpecialFunction_Pdf
{

	public $permittedModules = ['all'];
	protected $columnNames = ['subject', 'activitytype', 'date_start', 'link'];

	public function process($moduleName, $id, Vtiger_PDF_Model $pdf)
	{
		$moduleName = 'Calendar';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$fields = $moduleModel->getFields();
		$adminUser = $currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			$adminUser = Users::getActiveAdminUser();
		}
		$queryGenerator = new App\QueryGenerator($moduleName, $adminUser->getId());
		$queryGenerator->setFields(['id']);
		$queryGenerator->addNativeCondition(['vtiger_activity.status' => 'PLL_OVERDUE']);
		$queryGenerator->addNativeCondition(['vtiger_crmentity.smownerid' => $currentUserModel->getId()]);
		$query = $queryGenerator->createQuery();
		$query->limit(500);
		$dataReader = $query->createCommand()->query();
		$html = '<br><style>' .
			'.table {width: 100%; border-collapse: collapse;}' .
			'.table thead th {border-bottom: 1px solid grey;}' .
			'.table tbody tr {border-bottom: 1px solid grey}' .
			'.table tbody tr:nth-child(even) {background-color: #F7F7F7;}' .
			'.center {text-align: center;}' .
			'.summary {border-top: 1px solid grey;}' .
			'</style>';
		$html .= '<table class="table"><thead><tr>';
		foreach ($this->columnNames as $column) {
			$fieldModel = $fields[$column];
			$html .= '<th><span>' . vtranslate($fieldModel->get('label'), $moduleName) . '</span>&nbsp;</th>';
		}
		$html .= '</tr></thead><tbody>';
		while ($row = $dataReader->read()) {
			$html .= '<tr>';
			foreach ($this->columnNames as $column) {
				$recordId = $row['id'];
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
				$class = '';
				if (in_array($column, ['activitytype', 'date_start', 'link'])) {
					$class = 'class="center"';
				}
				$fieldModel = $fields[$column];
				if ($column == 'link') {
					$linkId = $recordModel->get('link');
					if (!empty($linkId) && isRecordExists($linkId)) {
						$processRecordModel = Vtiger_Record_Model::getInstanceById($linkId);
						$value = $processRecordModel->getName();
					} else {
						$value = '';
					}
				} else {
					$value = $recordModel->getDisplayValue($fieldModel->getName(), $recordId, true);
				}

				$html .= '<td ' . $class . '>' . $value . '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}
}
