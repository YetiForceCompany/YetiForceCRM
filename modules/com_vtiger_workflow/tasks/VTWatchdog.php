<?php
/**
 * Watchdog Task Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTWatchdog extends VTTask
{
	public $executeImmediately = true;
	public $srcWatchdogModule = 'Notification';

	public function getFieldNames()
	{
		return ['type', 'message', 'recipients', 'title', 'skipCurrentUser'];
	}

	/**
	 * Get request method name for field name.
	 *
	 * @return array
	 */
	public function getFieldsNamesRequestMethod(): array
	{
		return ['message' => 'getForHtml'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$moduleName = $recordModel->getModuleName();
		$recordId = $recordModel->getId();
		switch ($this->recipients) {
			case 'watchdog':
				$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordId, $moduleName);
				$users = $watchdog->getWatchingUsers();
				break;
			case 'owner':
				$users = [$recordModel->get('assigned_user_id')];
				break;
			case 'owner_and_showner':
				$users = array_merge([$recordModel->get('assigned_user_id')], explode(',', $recordModel->get('shownerid')));
				break;
			case 'showner':
				$users = explode(',', $recordModel->get('shownerid'));
				break;
			default:
				$users = \App\PrivilegeUtil::getUserByMember($this->recipients);
				break;
		}
		$users = array_filter($users);
		if (!empty($this->skipCurrentUser) && false !== ($key = array_search(\App\User::getCurrentUserId(), $users))) {
			unset($users[$key]);
		}
		if (empty($users)) {
			return false;
		}
		$textParser = \App\TextParser::getInstanceByModel($recordModel);
		$relatedField = \App\ModuleHierarchy::getMappingRelatedField($moduleName);
		$notification = Vtiger_Record_Model::getCleanInstance('Notification');
		$notification->set('shownerid', implode(',', $users));
		$notification->set($relatedField, $recordId);
		$notification->set('title', $textParser->setContent($this->title)->parse()->getContent(), 'Text');
		$notification->set('description', $textParser->setContent($this->message)->parse()->getContent(), 'Text');
		$notification->set('notification_type', $this->type);
		$notification->set('notification_status', 'PLL_UNREAD');
		$notification->setHandlerExceptions(['disableHandlers' => true]);
		$notification->save();
	}
}
