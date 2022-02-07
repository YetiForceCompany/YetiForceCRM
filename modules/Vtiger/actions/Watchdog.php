<?php

/**
 * Watchdog Action Class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Watchdog_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		if (empty($recordId)) {
			if (!App\Privilege::isPermitted($moduleName, 'WatchingModule')) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			if (!App\Privilege::isPermitted($moduleName, 'DetailView', $recordId) || !App\Privilege::isPermitted($moduleName, 'WatchingRecords')) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
		if ($request->has('user')) {
			$userList = array_keys(\App\Fields\Owner::getInstance()->getAccessibleUsers());
			if (!\in_array($request->getInteger('user'), $userList)) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$state = $request->getInteger('state');
		$user = false;
		if ($request->has('user')) {
			$user = $request->getInteger('user');
		}
		if (empty($record)) {
			$watchdog = Vtiger_Watchdog_Model::getInstance($moduleName, $user);
			$watchdog->changeModuleState($state);
		} else {
			$watchdog = Vtiger_Watchdog_Model::getInstanceById($record, $moduleName, $user);
			$watchdog->changeRecordState($state);
		}
		Vtiger_Watchdog_Model::reloadCache();
		$response = new Vtiger_Response();
		$response->setResult($state);
		$response->emit();
	}
}
