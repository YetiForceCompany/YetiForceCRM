<?php

/**
 * Watchdog Action Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Announcements_BasicAjax_Action extends \App\Controller\Action
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
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('mark');
	}

	/**
	 * Action to mark announcements.
	 *
	 * @param \App\Request $request
	 */
	public function mark(App\Request $request)
	{
		$moduleName = $request->getModule();
		$announcements = Vtiger_Module_Model::getInstance($moduleName);
		$announcements->setMark($request->getInteger('record'), $request->getInteger('type'));

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
