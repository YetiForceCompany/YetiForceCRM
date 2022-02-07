<?php

/**
 * Save notification.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Notifications_SaveAjax_Action extends Settings_Vtiger_Index_Action
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addOrRemoveMembers');
		$this->exposeMethod('lock');
		$this->exposeMethod('exceptions');
	}

	/**
	 * Function adds/removes members.
	 *
	 * @param \App\Request $request
	 */
	public function addOrRemoveMembers(App\Request $request)
	{
		$module = $request->getInteger('srcModule');
		$members = $request->getArray('members', 'Text');
		$state = $request->getBoolean('isToAdd') ? 1 : 0;
		if (!empty($members)) {
			if (!\is_array($members)) {
				$members = [$members];
			}
			$watchdogModel = Vtiger_Watchdog_Model::getInstance($module);
			foreach ($members as $member) {
				$watchdogModel->changeModuleState($state, $member);
			}
			Vtiger_Watchdog_Model::reloadCache();
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function sets lock status.
	 *
	 * @param \App\Request $request
	 */
	public function lock(App\Request $request)
	{
		$module = $request->getInteger('srcModule');
		$members = $request->getArray('members', 'Text');
		$lock = $request->getBoolean('lock') ? 1 : 0;
		if (!empty($members)) {
			if (!\is_array($members)) {
				$members = [$members];
			}
			$watchdogModel = Vtiger_Watchdog_Model::getInstance($module);
			foreach ($members as $member) {
				$watchdogModel->lock($lock, $member);
			}
			Vtiger_Watchdog_Model::reloadCache();
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function sets exceptions for users.
	 *
	 * @param \App\Request $request
	 */
	public function exceptions(App\Request $request)
	{
		$module = $request->getInteger('srcModule');
		$member = $request->getByType('member', 'Text');
		$exceptions = $request->getArray('exceptions', 'Integer');
		if (!empty($member)) {
			$watchdogModel = Vtiger_Watchdog_Model::getInstance($module);
			$watchdogModel->exceptions($exceptions, $member);
			Vtiger_Watchdog_Model::reloadCache();
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
