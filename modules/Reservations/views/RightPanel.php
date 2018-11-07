<?php

/**
 * Reservations RightPanel view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Reservations_RightPanel_View extends Vtiger_IndexAjax_View
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getUsersList');
		$this->exposeMethod('getTypesList');
	}

	/**
	 * Get users list.
	 *
	 * @param \App\Request $request
	 */
	public function getUsersList(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$userModel = \App\User::getCurrentUserModel();
		$clendar = Settings_Roles_Record_Model::getInstanceById($userModel->getRole())->get('clendarallorecords');
		$users = $groups = [];
		switch ($clendar) {
			case 1:
				$users[$userModel->getId()] = $userModel->getName();
				break;
			case 2:
				$groups = \App\Fields\Owner::getInstance(false, $userModel)->getAccessibleGroups();
				$users[$userModel->getId()] = $userModel->getName();
				break;
			case 3:
				if (AppConfig::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST')) {
					$usersAndGroup = \App\Fields\Owner::getInstance($moduleName, $userModel)->getUsersAndGroupForModuleList();
					$users = $usersAndGroup['users'];
					$groups = $usersAndGroup['group'];
				} else {
					$users = \App\Fields\Owner::getInstance(false, $userModel)->getAccessibleUsers();
					$groups = \App\Fields\Owner::getInstance(false, $userModel)->getAccessibleGroups();
				}
				break;
			default:
				$users[$userModel->getId()] = $userModel->getName();
				break;
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ALL_ACTIVEUSER_LIST', $users);
		$viewer->assign('ALL_ACTIVEGROUP_LIST', $groups);
		$viewer->view('RightPanel.tpl', $moduleName);
	}

	public function getTypesList(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('ALL_ACTIVETYPES_LIST', Reservations_Calendar_Model::getCalendarTypes());
		$viewer->view('RightPanel.tpl', $request->getModule());
	}
}
