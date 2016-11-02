<?php

/**
 * Notification Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.c
 */
class Notification_Notification_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$id = $request->get('id');
		if (!empty($id)) {
			$notice = Notification_NoticeEntries_Model::getInstanceById($id);
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if ($userPrivilegesModel->getId() != $notice->getUserId()) {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}
		}
		$mode = $request->getMode();
		if ($mode == 'createMessage' && !Users_Privileges_Model::isPermitted('Notification', 'NotificationCreateMessage')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		} elseif ($mode == 'createMail' && (!Users_Privileges_Model::isPermitted('Notification', 'NotificationCreateMail') || !AppConfig::main('isActiveSendingMails') || !Users_Privileges_Model::isPermitted('OSSMail'))) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		} elseif (in_array($mode, ['setMark', 'getNumberOfNotifications', 'saveWatchingModules']) && !Users_Privileges_Model::isPermitted('Notification', 'DetailView')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setMark');
		$this->exposeMethod('getNumberOfNotifications');
		$this->exposeMethod('saveWatchingModules');
		$this->exposeMethod('createMail');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
	}

	public function setMark(Vtiger_Request $request)
	{
		$ids = $request->get('ids');
		if (!is_array($ids)) {
			$ids = [$ids];
		}
		foreach ($ids as $id) {
			$recordModel = Vtiger_Record_Model::getInstanceById($id);
			$recordModel->setMarked();
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	public function getNumberOfNotifications(Vtiger_Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult(Notification_Module_Model::getNumberOfEntries());
		$response->emit();
	}

	public function saveWatchingModules(Vtiger_Request $request)
	{
		$selectedModules = $request->get('selctedModules');
		$watchingModules = Vtiger_Watchdog_Model::getWatchingModules();
		Vtiger_Watchdog_Model::setSchedulerByUser($request->get('sendNotifications'), $request->get('frequency'));
		if (!empty($selectedModules)) {
			foreach ($selectedModules as $moduleName) {
				$watchdogModel = Vtiger_Watchdog_Model::getInstance($moduleName);
				$watchdogModel->changeModuleState(1);
			}
		} else {
			$selectedModules = [];
		}
		foreach ($watchingModules as $moduleId) {
			$moduleName = vtlib\Functions::getModuleName($moduleId);
			if (!in_array($moduleName, $selectedModules)) {
				$watchdogModel = Vtiger_Watchdog_Model::getInstance($moduleName);
				$watchdogModel->changeModuleState(0);
			}
		}
	}

	public function createMail(Vtiger_Request $request)
	{
		$accessibleUsers = \App\Fields\Owner::getInstance()->getAccessibleUsers();
		$content = $request->get('message');
		$subject = $request->get('title');
		$users = $request->get('users');
		if (!is_array($users)) {
			$users = [$users];
		}
		$sendStatus = true;
		if (count($users)) {
			require_once('modules/Emails/mail.php');
			foreach ($users as $user) {
				if (isset($accessibleUsers[$user])) {
					$email = \App\User::getUserModel($user)->getDetail('email1');
					$name = vtlib\Functions::getOwnerRecordLabel($user);
					$status = send_mail('Users', $email, $name, $from_email, $subject, $content);
					if (!$status) {
						$sendStatus = false;
					}
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($sendStatus);
		$response->emit();
	}
}
