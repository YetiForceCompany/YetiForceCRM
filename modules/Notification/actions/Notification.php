<?php

/**
 * Notification Action Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Notification_Notification_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!$request->isEmpty('id')) {
			$notice = Notification_NoticeEntries_Model::getInstanceById($request->getInteger('id'));
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if ($userPrivilegesModel->getId() != $notice->getUserId()) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
		$mode = $request->getMode();
		if ($mode === 'createMessage' && !\App\Privilege::isPermitted('Notification', 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		} elseif (in_array($mode, ['setMark', 'saveWatchingModules']) && !\App\Privilege::isPermitted('Notification', 'DetailView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setMark');
		$this->exposeMethod('saveWatchingModules');
	}

	/**
	 * Marks notification as read.
	 *
	 * @param \App\Request $request
	 */
	public function setMark(\App\Request $request)
	{
		foreach ($request->getArray('ids', 'Integer') as $id) {
			$recordModel = Vtiger_Record_Model::getInstanceById($id, $request->getModule());
			$recordModel->setMarked();
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	public function saveWatchingModules(\App\Request $request)
	{
		$selectedModules = $request->getArray('selctedModules', 2);
		$watchingModules = Vtiger_Watchdog_Model::getWatchingModules();
		Vtiger_Watchdog_Model::setSchedulerByUser($request->getArray('sendNotifications', 'Integer'), $request->getInteger('frequency'));
		foreach ($selectedModules as $moduleId) {
			$watchdogModel = Vtiger_Watchdog_Model::getInstance((int) $moduleId);
			$watchdogModel->changeModuleState(1);
		}
		foreach ($watchingModules as $moduleId) {
			if (!in_array($moduleId, $selectedModules)) {
				$watchdogModel = Vtiger_Watchdog_Model::getInstance($moduleId);
				$watchdogModel->changeModuleState(0);
			}
		}
		Vtiger_Watchdog_Model::reloadCache();
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
