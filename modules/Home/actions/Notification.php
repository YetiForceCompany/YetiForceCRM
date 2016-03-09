<?php

/**
 * Notification Action Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Home_Notification_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$id = $request->get('id');
		$notice = Home_NoticeEntries_Model::getInstanceById($id);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->getId() != $notice->getUserId()) {
			throw new NoPermittedException('LBL_PERMISSION_DENIED');
		}
	}

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setMark');
		$this->exposeMethod('getNumberOfNotifications');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		throw new NoPermittedException('LBL_PERMISSION_DENIED');
	}

	public function setMark(Vtiger_Request $request)
	{
		$notice = Home_NoticeEntries_Model::getInstanceById($request->get('id'));
		$response = new Vtiger_Response();
		$response->setResult($notice->setMarked());
		$response->emit();
	}
	
	public function getNumberOfNotifications(Vtiger_Request $request)
	{
		$notice = Home_Notification_Model::getInstance();
		$response = new Vtiger_Response();
		$response->setResult($notice->getNumberOfEntries());
		$response->emit();
	}
}
