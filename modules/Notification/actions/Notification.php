<?php

/**
 * Notification Action Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.c
 */
class Notification_Notification_Action extends Vtiger_Action_Controller
{

	public function checkPermission(\App\Request $request)
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
		if ($mode == 'createMessage' && !Users_Privileges_Model::isPermitted('Notification', 'CreateView')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		} elseif ($mode == 'createMail' && (!Users_Privileges_Model::isPermitted('Notification', 'NotificationCreateMail') || !AppConfig::main('isActiveSendingMails') || !Users_Privileges_Model::isPermitted('OSSMail'))) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		} elseif (in_array($mode, ['setMark', 'saveWatchingModules']) && !Users_Privileges_Model::isPermitted('Notification', 'DetailView')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setMark');
		$this->exposeMethod('saveWatchingModules');
		$this->exposeMethod('createMail');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
	}

	public function setMark(\App\Request $request)
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

	public function saveWatchingModules(\App\Request $request)
	{
		$selectedModules = $request->get('selctedModules');
		$watchingModules = Vtiger_Watchdog_Model::getWatchingModules();
		Vtiger_Watchdog_Model::setSchedulerByUser($request->get('sendNotifications'), $request->get('frequency'));
		if (!empty($selectedModules)) {
			foreach ($selectedModules as $moduleId) {
				$watchdogModel = Vtiger_Watchdog_Model::getInstance($moduleId);
				$watchdogModel->changeModuleState(1);
			}
		} else {
			$selectedModules = [];
		}
		foreach ($watchingModules as $moduleId) {
			if (!in_array($moduleId, $selectedModules)) {
				$watchdogModel = Vtiger_Watchdog_Model::getInstance($moduleId);
				$watchdogModel->changeModuleState(0);
			}
		}
		Vtiger_Watchdog_Model::reloadCache();
	}

	public function createMail(\App\Request $request)
	{
		$accessibleUsers = \App\Fields\Owner::getInstance()->getAccessibleUsers();
		$content = $request->get('message');
		$subject = $request->get('title');
		$users = $request->get('users');
		if (!is_array($users)) {
			$users = [$users];
		}
		if (count($users)) {
			foreach ($users as $user) {
				if (isset($accessibleUsers[$user])) {
					$email = \App\User::getUserModel($user)->getDetail('email1');
					\App\Mailer::addMail([
						//'smtp_id' => 1,
						'to' => [$email => \App\Fields\Owner::getLabel($user)],
						'owner' => $user,
						'subject' => $subject,
						'content' => $content,
					]);
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
