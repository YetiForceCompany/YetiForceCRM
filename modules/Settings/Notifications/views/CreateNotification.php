<?php

/**
 * List notifications
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_CreateNotification_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new NoPermittedForAdminException('LBL_PERMISSION_DENIED');
		}
	}

	function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$listSize = [3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
		$recordModel = false;
		if ($request->has('id')) {
			$recordModel = Settings_Notifications_Record_Model::getInstanceById($request->get('id'));
		} else {
			$recordModel = new Settings_Notifications_Record_Model();
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('LIST_SIZE', $listSize);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->view('CreateNotification.tpl', $qualifiedModuleName);
	}
}
