<?php

/**
 * Email Template Report Task.
 *
 * @package 	App
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
/**
 * VTEmailReport class.
 */
class VTEmailReport extends VTTask
{
	/** @var bool Sending email takes more time, this should be handled via queue all the time. */
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return string[]
	 */
	public function getFieldNames()
	{
		return ['template', 'members'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$users = [];
		foreach ($this->members as $member) {
			$users = array_merge($users, \App\PrivilegeUtil::getUserByMember($member));
		}
		foreach (array_unique($users) as $user) {
			$userRecordModel = \Vtiger_Record_Model::getInstanceById($user, 'Users');
			if ('Active' === $userRecordModel->get('status') && !empty($userEmail = $userRecordModel->get('email1'))) {
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
