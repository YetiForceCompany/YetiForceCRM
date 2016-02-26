<?php
/**
 * Watchdog Task Class
 * @package YetiForce.WorkflowTask
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTWatchdog extends VTTask
{

	public $executeImmediately = true;

	public function getFieldNames()
	{
		return array('type', 'message');
	}

	public function doTask($entity)
	{
		$util = new VTWorkflowUtils();
		$admin = $util->adminUser();
		$ws_id = $entity->getId();
		$module = $entity->getModuleName();
		$parts = explode('x', $ws_id);
		$entityId = $parts[1];

		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$notification = Home_Notification_Model::getInstance();
		$notification->set('message', $this->message);
		$notification->set('type', $this->type);
		$notification->set('userid', $userModel->getId());
		$notification->set('reletedid', $entityId);
		$notification->save();
	}
}
