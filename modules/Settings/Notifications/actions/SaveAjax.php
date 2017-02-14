<?php

/**
 * Save notification
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Notifications_SaveAjax_Action extends Settings_Vtiger_Index_Action
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addOrRemoveMembers');
		$this->exposeMethod('lock');
		$this->exposeMethod('exceptions');
	}

	/**
	 * Function adds/removes members
	 * @param Vtiger_Request $request
	 */
	public function addOrRemoveMembers(Vtiger_Request $request)
	{
		$module = $request->get('srcModule');
		$members = $request->get('members');
		$state = $request->get('isToAdd') ? 1 : 0;
		if (!empty($members)) {
			if (!is_array($members)) {
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
	 * Function sets lock status
	 * @param Vtiger_Request $request
	 */
	public function lock(Vtiger_Request $request)
	{
		$module = $request->get('srcModule');
		$members = $request->get('members');
		$lock = $request->get('lock');
		if (!empty($members)) {
			if (!is_array($members)) {
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
	 * Function sets exceptions for users
	 * @param Vtiger_Request $request
	 */
	public function exceptions(Vtiger_Request $request)
	{
		$module = $request->get('srcModule');
		$member = $request->get('member');
		$exceptions = $request->get('exceptions');
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
