<?php

/**
 * Watchdog Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Announcements_Save_Action extends Vtiger_Action_Controller
{

	function __construct()
	{
		$this->exposeMethod('mark');
	}

	function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();

		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	function checkPermission(Vtiger_Request $request)
	{
		return;
	}

	public function mark(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$state = $request->get('type');

		$announcements = Vtiger_Module_Model::getInstance($moduleName);
		$announcements->setMark($record, $state);

		$response = new Vtiger_Response();
		$response->setResult($state);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
