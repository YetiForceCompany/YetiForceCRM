<?php

/**
 * Speed test view class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_ConfReport_Speed_View extends Vtiger_BasicModal_View
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('TESTS', Settings_ConfReport_Module_Model::testSpeed());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('Speed.tpl', $qualifiedModule);
	}
}
