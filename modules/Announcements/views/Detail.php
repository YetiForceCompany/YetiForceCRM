<?php

/**
 * Announcements Detail View Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Announcements_Detail_View extends Vtiger_Detail_View
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showUsers');
	}

	public function showUsers(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$users = [];
		foreach ($moduleModel->getUsers() as $userId => $name) {
			$row = $moduleModel->getMarkInfo($recordId, $userId);
			$row['name'] = $name;
			$users[$userId] = $row;
		}

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('USERS', $users);
		$viewer->view('UsersList.tpl', $moduleName);
	}
}
