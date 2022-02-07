<?php

/**
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Users_SwitchUsers_View extends Vtiger_BasicModal_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Module_Model::getSwitchUsers()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		echo '<div class="modal fade switchUsersContainer"><div class="modal-dialog"><div class="modal-content">';
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$users = Users_Module_Model::getSwitchUsers(true);
		$userId = $request->getInteger('id');
		$baseUserId = $userId;
		if (App\Session::has('baseUserId') && '' !== App\Session::get('baseUserId')) {
			$baseUserId = App\Session::get('baseUserId');
		}
		unset($users[$baseUserId], $users[$userId]);

		$viewer = $this->getViewer($request);
		$viewer->assign('SWITCH_USERS', $users);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('BASE_USER_ID', $baseUserId);
		$this->preProcess($request);
		$viewer->view('SwitchUsers.tpl', $moduleName);
		$this->postProcess($request);
	}
}
