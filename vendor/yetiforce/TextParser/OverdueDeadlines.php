<?php
namespace App\TextParser;

/**
 * Oustanding deadlines class
 * @package YetiForce.TextParser
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	 * Process
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
		$adminUser = !$currentUserModel->isAdmin() ? \App\User::getActiveAdminId() : $currentUserModel->getUserId();

		$queryGenerator = new \App\QueryGenerator($moduleName, $adminUser);
		$queryGenerator->setFields(['id']);
		$queryGenerator->addNativeCondition(['vtiger_activity.status' => 'PLL_OVERDUE']);
		$queryGenerator->addNativeCondition(['vtiger_crmentity.smownerid' => $currentUserModel->getUserId()]);
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
			$html .= '<th><span>' . \App\Language::translate($fieldModel->get('label'), $moduleName) . '</span>&nbsp;</th>';
		}
		$html .= '</tr></thead><tbody>';
		while ($row = $dataReader->read()) {
			$html .= '<tr>';
			foreach ($this->columnNames as $column) {
				$recordId = $row['id'];
				$recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
				$class = '';
				if (in_array($column, ['activitytype', 'date_start', 'link'])) {
					$class = 'class="center"';
				}
				$fieldModel = $fields[$column];
				if ($column == 'link') {
					$linkId = $recordModel->get('link');
					if (!empty($linkId) && isRecordExists($linkId)) {
						$processRecordModel = \Vtiger_Record_Model::getInstanceById($linkId);
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
