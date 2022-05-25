<?php

/**
 * Notification Action Class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function checkPermission(App\Request $request)
	{
		if (!$request->isEmpty('record') && !Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule())->isEditable()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_PERMISSION_DENIED', 406);
		}
		$mode = $request->getMode();
		if ('createMessage' === $mode && !\App\Privilege::isPermitted('Notification', 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (\in_array($mode, ['setMark', 'saveWatchingModules']) && !\App\Privilege::isPermitted('Notification', 'DetailView')) {
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
		$this->exposeMethod('tracking');
	}

	/**
	 * Marks notification as read.
	 *
	 * @param \App\Request $request
	 */
	public function setMark(App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$recordModel->setMarked();

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	public function saveWatchingModules(App\Request $request)
	{
		$selectedModules = $request->getArray('selectedModules', \App\Purifier::INTEGER);
		$watchingModules = Vtiger_Watchdog_Model::getWatchingModules();
		Vtiger_Watchdog_Model::setSchedulerByUser($request->getArray('sendNotifications', 'Integer'), $request->getInteger('frequency'));
		foreach ($selectedModules as $moduleId) {
			$watchdogModel = Vtiger_Watchdog_Model::getInstance((int) $moduleId);
			$watchdogModel->changeModuleState(1);
		}
		foreach ($watchingModules as $moduleId) {
			if (!\in_array($moduleId, $selectedModules)) {
				$watchdogModel = Vtiger_Watchdog_Model::getInstance($moduleId);
				$watchdogModel->changeModuleState(0);
			}
		}
		Vtiger_Watchdog_Model::reloadCache();
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/** {@inheritdoc} */
	public function isSessionExtend(App\Request $request)
	{
		return 'tracking' !== $request->getMode();
	}

	/**
	 * Get number of notifications.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function tracking(App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult(Vtiger_Module_Model::getInstance($request->getModule())->getEntriesCount());
		$response->emit();
	}
}
