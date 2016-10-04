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
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!Users_Privileges_Model::isPermitted('Dashboard', 'NotificationCreateMessage') && !Users_Privileges_Model::isPermitted('Dashboard', 'NotificationCreateMail')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$private = '';
		if (Users_Privileges_Model::isPermitted('Dashboard', 'NotificationSendToAll')) {
			$private = 'Public';
		}
		$users = \includes\fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers($private);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', $currentUser);
		$viewer->assign('USERS', $users);
		$viewer->view('CreateNotificationModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
