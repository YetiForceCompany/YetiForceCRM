<?php

/**
 * Notification Action Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Notification_Notification_Action extends \App\Controller\Action
{

	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$id = $request->get('id');
		if ($id) {
			$notice = Notification_NoticeEntries_Model::getInstanceById($id);
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if ($userPrivilegesModel->getId() != $notice->getUserId()) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
		$mode = $request->getMode();
		if ($mode === 'createMessage' && !\App\Privilege::isPermitted('Notification', 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		} elseif ($mode === 'createMail' && (!\App\Privilege::isPermitted('Notification', 'NotificationCreateMail') || !AppConfig::main('isActiveSendingMails') || !\App\Privilege::isPermitted('OSSMail'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		} elseif (in_array($mode, ['setMark', 'saveWatchingModules']) && !\App\Privilege::isPermitted('Notification', 'DetailView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
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

	public function setMark(\App\Request $request)
	{
		$ids = $request->get('ids');
		if (!is_array($ids)) {
			$ids = [$ids];
		}
		foreach ($ids as $id) {
			$recordModel = Vtiger_Record_Model::getInstanceById($id, $request->getModule());
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
				$watchdogModel = Vtiger_Watchdog_Model::getInstance((int) $moduleId);
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
		$content = $request->getForHtml('message');
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
