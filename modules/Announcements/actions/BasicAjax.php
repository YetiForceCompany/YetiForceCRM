<?php

/**
 * Watchdog Action Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Announcements_BasicAjax_Action extends Vtiger_Action_Controller
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->exposeMethod('mark');
	}

	/**
	 * Main process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Action to mark announcements
	 * @param \App\Request $request
	 */
	public function mark(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$state = $request->get('type');
		$announcements = Vtiger_Module_Model::getInstance($moduleName);
		$announcements->setMark($request->getInteger('record'), $request->get('type'));

		$response = new Vtiger_Response();
		$response->setResult($state);
		$response->emit();
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
