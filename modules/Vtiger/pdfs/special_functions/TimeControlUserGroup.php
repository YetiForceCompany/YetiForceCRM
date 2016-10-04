<?php

/**
 * Special function displaying time control table
 * @package YetiForce.PDF
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Pdf_TimeControlUserGroup extends Vtiger_SpecialFunction_Pdf
{

	public $permittedModules = ['OSSTimeControl'];
	protected $columnNames = ['name', 'accountid', 'time_start', 'time_end', 'sum_time'];

	public function process($moduleName, $id, Vtiger_PDF_Model $pdf)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$fields = $moduleModel->getFields();

		$html = '<br><style>' .
			'.table {width: 100%; border-collapse: collapse;}' .
			'.table thead th {border-bottom: 1px solid grey;}' .
			'.table tbody tr {border-bottom: 1px solid grey}' .
			'.table tbody tr:nth-child(even) {background-color: #F7F7F7;}' .
			'.center {text-align: center;}' .
			'.summary {border-top: 1px solid grey;}' .
			'</style>';

		$html .= '<table class="table"><thead><tr>';
		$html .= '<th>Nazwa użytkownika</th>';
		$html .= '<th class="center">Dział</th>';
		$html .= '<th class="center">Czas pracy</th>';
		$html .= '</tr></thead><tbody>';

		$summary = [];
		foreach ($this->getUserList($pdf, $moduleName) as $user => $data) {
			$html .= '<tr>';
			$html .= '<td>' . $user . '</td>';
			$html .= '<td class="center">' . $data['role'] . '</td>';
			$time = vtlib\Functions::decimalTimeFormat($data['time']);
			$html .= '<td class="center">' . $time['short'] . '</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}

	protected function getUserList(Vtiger_PDF_Model $pdf, $moduleName)
	{
		$users = [];
		$db = PearDatabase::getInstance();
		$ids = $pdf->getRecordIds();
		if (!is_array($ids)) {
			$ids = [$ids];
		}
		foreach ($ids as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$user = $recordModel->getDisplayValue('assigned_user_id', $recordId, true);
			$time = (isset($users[$user]['time']) ? $users[$user]['time'] : 0) + $recordModel->get('sum_time');
			$users[$user] = [
				'time' => $time,
				'role' => vtranslate($this->getRoleName($recordModel->get('assigned_user_id')), $moduleName),
			];
		}
		return $users;
	}

	public function getRoleName($userId)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT rolename FROM vtiger_role INNER JOIN vtiger_user2role ON vtiger_user2role.roleid = vtiger_role.roleid WHERE vtiger_user2role.userid = ?', [$userId]);
		return $db->getSingleValue($result);
	}
}
