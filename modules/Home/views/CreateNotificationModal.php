<?php

/**
 * Create Notification View Class
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Home_CreateNotificationModal_View extends Vtiger_BasicModal_View
{

	public function getSize(Vtiger_Request $request)
	{
		return 'modal-lg';
	}

	public function checkPermission(Vtiger_Request $request)
	{
		parent::checkPermission($request);

		$mode = $request->getMode();
		if (!in_array($mode, ['createMessage', 'createMail'])) {
			throw new NoPermittedException('LBL_PERMISSION_DENIED');
		}
		if (!Users_Privileges_Model::isPermitted('Dashboard', 'NotificationCreateMessage') && !Users_Privileges_Model::isPermitted('Dashboard', 'NotificationCreateMail')) {
			throw new NoPermittedException('LBL_PERMISSION_DENIED');
		}
	}

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
