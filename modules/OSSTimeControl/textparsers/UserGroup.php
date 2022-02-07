<?php

/**
 * Time control user group parser class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$html = '';
		$headerStyle = 'font-size:9px;padding:0px 4px;text-align:center;';
		$bodyStyle = 'font-size:8px;border:1px solid #ddd;padding:0px4px;';
		$html .= '<table class="table" style="border-collapse:collapse;width:100%;"><thead><tr>';
		$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate('User Name', 'Users') . '</th>';
		$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate('Role', 'Users') . '</th>';
		$html .= "<th style=\"{$headerStyle}\">" . \App\Language::translate('OSSTimeControl', 'OSSTimeControl') . '</th>';
		$html .= '</tr></thead><tbody>';
		foreach ($this->getUserList() as $user => $data) {
			$html .= '<tr>';
			$html .= "<td style=\"{$bodyStyle}\">" . $user . '</td>';
			$html .= "<td style=\"{$bodyStyle} text-align:center;\">" . $data['role'] . '</td>';
			$html .= "<td style=\"{$bodyStyle} text-align:center;\">" . \App\Fields\RangeTime::displayElapseTime($data['time']) . '</td>';
			$html .= '</tr>';
		}
		return $html . '</tbody></table>';
	}

	protected function getUserList()
	{
		$users = [];
		$ids = $this->textParser->getParam('pdf')->getVariable('recordsId');
		if (!\is_array($ids)) {
			$ids = [$ids];
		}
		foreach ($ids as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $this->textParser->moduleName);
			if (!$recordModel->isViewable()) {
				continue;
			}
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
