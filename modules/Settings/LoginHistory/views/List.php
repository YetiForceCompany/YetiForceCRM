<?php

/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_LoginHistory_List_View extends Settings_Vtiger_List_View
{
	public function preProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$loginHistoryRecordModel = new Settings_LoginHistory_Record_Model();
		$usersList = $loginHistoryRecordModel->getAccessibleUsers();
		$viewer->assign('USERSLIST', $usersList);
		$viewer->assign('SELECTED_USER', $request->getByType('user_name', 'Text'));
		parent::preProcess($request, false);
	}
}
