<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

/**
 * Class VTSendNotificationTask.
 */
class VTSendNotificationTask extends VTTask
{
	/**
	 * Sending email takes more time, this should be handled via queue all the time.
	 *
	 * @var bool
	 */
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return array
	 */
	public function getFieldNames()
	{
		return ['template'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		if (is_numeric($this->template) && $this->template) {
			$entityId = $recordModel->getId();
			$resultInvitees = (new \App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => $entityId])->createCommand()->query();
			while ($recordinfo = $resultInvitees->read()) {
				$userModel = App\User::getUserModel($recordinfo['inviteeid']);
				if ('Active' === $userModel->getDetail('status')) {
					\App\Mailer::sendFromTemplate([
						'template' => $this->template,
						'moduleName' => $recordModel->getModuleName(),
						'recordId' => $entityId,
						'to' => $userModel->getDetail('email1'),
						'cc' => $this->copy_email,
						'language' => $userModel->getDetail('language'),
						'to_email_mod' => 'Users',
					]);
				}
			}
		}
	}
}
