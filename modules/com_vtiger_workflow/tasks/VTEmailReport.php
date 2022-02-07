<?php

/**
 * Email Template Report Task.
 *
 * @package 	App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * VTEmailReport class.
 */
class VTEmailReport extends VTTask
{
	/** {@inheritdoc} */
	public $recordEventState = self::RECORD_EVENT_INACTIVE;

	/**
	 * Get field names.
	 *
	 * @return string[]
	 */
	public function getFieldNames()
	{
		return ['template', 'members', 'emailoptout'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel = null)
	{
		$users = [];
		foreach ($this->members as $member) {
			$users = array_merge($users, \App\PrivilegeUtil::getUserByMember($member));
		}
		foreach (array_unique($users) as $user) {
			$userRecordModel = \Vtiger_Record_Model::getInstanceById($user, 'Users');
			$checkApproval = (bool) ($this->emailoptout ?? null);
			if ('Active' === $userRecordModel->get('status') && !empty($userEmail = $userRecordModel->get('email1')) && (!$checkApproval || $userRecordModel->get('emailoptout'))) {
				(new \App\BatchMethod(['method' => '\App\Mailer::sendFromTemplate', 'params' => ['params' => [
					'template' => $this->template,
					'to' => $userEmail,
					'textParserParams' => [
						'userId' => $user
					]
				]]]))->save();
			}
		}
	}
}
