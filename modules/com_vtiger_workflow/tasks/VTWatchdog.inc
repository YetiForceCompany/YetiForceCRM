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
		return ['type', 'message', 'recipients', 'title', 'skipCurrentUser'];
	}

	public function doTask($entity)
	{
		$moduleName = $entity->getModuleName();
		$wsId = $entity->getId();
		$parts = explode('x', $wsId);
		$recordId = $parts[1];
		$users = [];

		$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordId, $moduleName);
		if ($this->recipients == 'watchdog') {
			$users = $watchdog->getWatchingUsers();
		} else {
			$users = [$this->recipients];
		}
		if (empty($users)) {
			return false;
		}
		if ($this->skipCurrentUser == 1 && ($key = array_search(Users_Privileges_Model::getCurrentUserPrivilegesModel()->getId(), $users)) !== false) {
			unset($users[$key]);
		}
		foreach ($users as $userId) {
			$notification = Home_Notification_Model::getInstance();
			$notification->set('record', $recordId);
			$notification->set('moduleName', $moduleName);
			$notification->set('title', $this->title);
			$notification->set('message', $this->message);
			$notification->set('type', $this->type);
			$notification->set('userid', $userId);
			$notification->save();
		}
	}

	public function getAllTypes()
	{
		$db = PearDatabase::getInstance();
		$result = $db->query('SELECT * FROM a_yf_notification_type');
		$types = [];
		while ($row = $db->getRow($result)) {
			$types[$row['id']] = $row;
		}
		return $types;
	}
}
