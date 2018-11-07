<?php

/**
 * Calendar right panel view model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Calendar_RightPanel_View extends Vtiger_IndexAjax_View
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getUsersList');
		$this->exposeMethod('getGroupsList');
		$this->exposeMethod('getActivityType');
	}

	/**
	 * Get tpl path file.
	 *
	 * @param string $tplFile
	 *
	 * @return string
	 */
	protected function getTpl(string $tplFile)
	{
		return "Standard/$tplFile";
	}

	public function getUsersList(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$roleInstance = Settings_Roles_Record_Model::getInstanceById($currentUser->get('roleid'));
		$clendarallorecords = $roleInstance->get('clendarallorecords');
		switch ($clendarallorecords) {
			case 3:
				if (AppConfig::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST')) {
					$usersAndGroup = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getUsersAndGroupForModuleList();
					$users = $usersAndGroup['users'];
				} else {
					$users = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers();
				}
				break;
			case 1:
			case 2:
			default:
				$users[$currentUser->getId()] = $currentUser->getName();
				break;
		}
		if (!empty($users) && $favouriteUsers = $currentUser->getFavouritesUsers()) {
			uksort($users,
				function ($a, $b) use ($favouriteUsers) {
					return !isset($favouriteUsers[$a]) && isset($favouriteUsers[$b]);
				});
			$viewer->assign('FAVOURITES_USERS', $favouriteUsers);
		}
		$viewer->assign('HISTORY_USERS', $request->getExploded('user', ',', 'Integer'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ALL_ACTIVEUSER_LIST', $users);
		$viewer->assign('USER_MODEL', $currentUser);
		$viewer->view($this->getTpl('RightPanel.tpl'), $moduleName);
	}

	public function getGroupsList(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$roleInstance = Settings_Roles_Record_Model::getInstanceById($currentUser->get('roleid'));
		$clendarallorecords = $roleInstance->get('clendarallorecords');
		switch ($clendarallorecords) {
			case 1:
				$groups = [];
				break;
			case 2:
				$groups = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups();
				break;
			case 3:
				if (AppConfig::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST')) {
					$usersAndGroup = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getUsersAndGroupForModuleList();
					$groups = $usersAndGroup['group'];
				} else {
					$groups = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups();
				}
				break;
			default:
				break;
		}
		$viewer->assign('HISTORY_USERS', $request->getExploded('user', ',', 'Integer'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ALL_ACTIVEGROUP_LIST', $groups);
		$viewer->view($this->getTpl('RightPanel.tpl'), $moduleName);
	}

	public function getActivityType(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ACTIVITY_TYPE', Calendar_Module_Model::getCalendarTypes());
		$viewer->view($this->getTpl('RightPanel.tpl'), $moduleName);
	}
}
