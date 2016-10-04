<?php

/**
 * Announcements Detail View Class
 * @package YetiForce.View 
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Announcements_Detail_View extends Vtiger_Detail_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showUsers');
	}

	public function showUsers(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
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
