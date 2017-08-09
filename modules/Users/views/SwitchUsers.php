<?php

/**
 * @package YetiForce.Modal
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Users_SwitchUsers_View extends Vtiger_BasicModal_View
{

	public function checkPermission(\App\Request $request)
	{
		if (!Users_Module_Model::getSwitchUsers()) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		echo '<div class="modal fade switchUsersContainer"><div class="modal-dialog modal-sm"><div class="modal-content">';
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$users = Users_Module_Model::getSwitchUsers(true);
		$userId = $request->get('id');
		$baseUserId = $userId;
		if (App\Session::has('baseUserId') && App\Session::get('baseUserId') != '') {
			$baseUserId = App\Session::get('baseUserId');
		}
		unset($users[$baseUserId]);
		unset($users[$userId]);
		$viewer = $this->getViewer($request);
		$viewer->assign('SWITCH_USERS', $users);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('BASE_USER_ID', $baseUserId);
		$this->preProcess($request);
		$viewer->view('SwitchUsers.tpl', $moduleName);
		$this->postProcess($request);
	}
}
