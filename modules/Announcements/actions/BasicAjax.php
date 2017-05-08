<?php

/**
 * Watchdog Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Announcements_BasicAjax_Action extends Vtiger_BasicAjax_Action
{

	public function __construct()
	{
		$this->exposeMethod('mark');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();

		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	public function mark(\App\Request $request)
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

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
