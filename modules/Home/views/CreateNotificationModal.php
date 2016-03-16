<?php

/**
 * Create Notification View Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Home_CreateNotificationModal_View extends Vtiger_BasicModal_View
{

	public function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('CreateNotificationModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
