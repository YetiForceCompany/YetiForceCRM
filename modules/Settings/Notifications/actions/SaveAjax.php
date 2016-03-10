<?php

/**
 * Save notification
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_SaveAjax_Action extends Settings_Vtiger_Index_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveType');
		$this->exposeMethod('saveConfig');
	}

	function saveConfig(Vtiger_Request $request)
	{
		$moduleName = $request->get('srcModule');
		$shareOwners = $request->get('owners');
		$watchdogModel = Vtiger_Watchdog_Model::getInstance($moduleName);
		$listWatchingUsers = $watchdogModel->getWatchingUsers();
		if (empty(!$shareOwners)) {
			foreach ($shareOwners as $ownerId) {
				if (!in_array($ownerId, $listWatchingUsers)) {
					$watchdogModel->changeModuleState(1, $ownerId);
				}
			}
		} else {
			$shareOwners = [];
		}
		foreach ($listWatchingUsers as $ownerId) {
			if (!in_array($ownerId, $shareOwners)) {
				$watchdogModel->changeModuleState(0, $ownerId);
			}
		}
	}

	function saveType(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$insertParams = [
			'name' => $request->get('name'),
			'role' => $request->get('roleId'),
			'width' => $request->get('width'),
			'height' => $request->get('height'),
		];
		if (($id = $request->get('id')) == 0) {
			$db->insert('a_yf_notification_type', $insertParams);
		} else {
			$db->update('a_yf_notification_type', $insertParams, 'id = ?', [$id]);
		}
	}
}
