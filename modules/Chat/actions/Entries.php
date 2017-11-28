<?php

/**
 * Chat Entries Action Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Chat_Entries_Action extends Vtiger_Action_Controller
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Constructor with a list of allowed methods 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('add');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		throw new \App\Exceptions\AppException('ERR_NOT_ACCESSIBLE');
	}

	/**
	 * Add entries function
	 * @param \App\Request $request
	 */
	public function add(\App\Request $request)
	{
		Chat_Module_Model::add($request->get('message'));
		$view = new Chat_Entries_View();
		$view->get($request);
	}
}
