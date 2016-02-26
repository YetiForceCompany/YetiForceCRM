<?php

/**
 * Watchdog Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Watchdog_Action extends Vtiger_Action_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateRecord');
		$this->exposeMethod('updateModule');
	}

	function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		if (!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new NoPermittedToRecordException('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		$mode = $request->getMode();
		if ($mode == 'updateRecord' && !Users_Privileges_Model::isPermitted($moduleName, 'WatchingRecords')) {
			throw new NoPermittedToRecordException('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}

		if ($mode == 'updateModule' && !Users_Privileges_Model::isPermitted($moduleName, 'WatchingModule')) {
			throw new NoPermittedToRecordException('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function updateRecord(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$state = $request->get('state');

		$watchdog = Vtiger_Watchdog_Model::getInstanceById($record, $moduleName);
		$watchdog->changeRecordState($state);

		$response = new Vtiger_Response();
		$response->setResult($state);
		$response->emit();
	}

	public function updateModule(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$state = $request->get('state');

		$watchdog = Vtiger_Watchdog_Model::getInstance($moduleName);
		$watchdog->changeModuleState($state);

		$response = new Vtiger_Response();
		$response->setResult($state);
		$response->emit();
	}
}
