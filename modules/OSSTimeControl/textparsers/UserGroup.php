<?php

/**
 * Time control user group parser class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSTimeControl_UserGroup_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_TIME_CONTROL_USER_GROUP';

	/** @var mixed Parser type */
	public $type = 'pdf';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '<br /><style>' .
			'.table {width: 100%; border-collapse: collapse;}' .
			'.table thead th {border-bottom: 1px solid grey;}' .
			'.table tbody tr {border-bottom: 1px solid grey}' .
			'.table tbody tr:nth-child(even) {background-color: #F7F7F7;}' .
			'.center {text-align: center;}' .
			'.summary {border-top: 1px solid grey;}' .
			'</style>';
		$html .= '<table class="table"><thead><tr>';
		$html .= '<th>' . \App\Language::translate('User Name', 'Users') . '</th>';
		$html .= '<th class="center">' . \App\Language::translate('Role', 'Users') . '</th>';
		$html .= '<th class="center">' . \App\Language::translate('OSSTimeControl', 'OSSTimeControl') . '</th>';
		$html .= '</tr></thead><tbody>';
		foreach ($this->getUserList() as $user => $data) {
			$html .= '<tr>';
			$html .= '<td>' . $user . '</td>';
			$html .= '<td class="center">' . $data['role'] . '</td>';
			$html .= '<td class="center">' . \App\Fields\Time::formatToHourText($data['time'], 'short') . '</td>';
			$html .= '</tr>';
		}
		return $html . '</tbody></table>';
	}

	protected function getUserList()
	{
		$users = [];
		$ids = $this->textParser->getParam('pdf')->getRecordIds();
		if (!is_array($ids)) {
			$ids = [$ids];
		}
		foreach ($ids as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->textParser->moduleName);
			$user = $recordModel->getDisplayValue('assigned_user_id', $recordId, true);
			$time = ($users[$user]['time'] ?? 0) + $recordModel->get('sum_time');
			$users[$user] = [
				'time' => $time,
				'role' => \App\Language::translate($this->getRoleName($recordModel->get('assigned_user_id')), $this->textParser->moduleName),
			];
		}
		return $users;
	}

	public function getRoleName($userId)
	{
		return (new \App\Db\Query())->select(['rolename'])->from('vtiger_role')->innerJoin('vtiger_user2role', 'vtiger_role.roleid = vtiger_user2role.roleid')->where(['vtiger_user2role.userid' => $userId])->scalar();
	}
}
