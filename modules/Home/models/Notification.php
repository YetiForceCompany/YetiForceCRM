<?php

/**
 * Notification Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.c
 */
class Home_Notification_Model extends Vtiger_Base_Model
{

	/**
	 * Function to get the instance
	 * @return <Home_Notification_Model>
	 */
	public static function getInstance()
	{
		return new self();
	}

	/**
	 * Function to get types of notification for library jstree
	 * @return <Array>
	 */
	public function getTypesForTree()
	{
		$typesNotification = $this->getTypes();
		$tree = [];
		foreach ($typesNotification as $id => $type) {
			$tree[] = [
				'id' => $id,
				'record_id' => $id,
				'type' => 'folder',
				'text' => vtranslate($type['name'], 'Home')
			];
		}
		return $tree;
	}

	public function getTypes()
	{
		$instance = Vtiger_Cache::get('Home_Notification_Model', 'Types');
		if ($instance) {
			return $instance;
		}

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$role = str_replace('H', '', $currentUser->get('roleid'));
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM a_yf_notification_type WHERE role = ? || role = ?', [0, $role]);
		$types = [];
		while ($row = $db->getRow($result)) {
			$types[$row['id']] = $row;
		}
		Vtiger_Cache::set('NotificationTypes', 'Types', $types);
		return $types;
	}

	public function getEntries($limit = false, $conditions = false, $userId = false, $groupBy = true)
	{
		$db = PearDatabase::getInstance();
		if (empty($userId)) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->getId();
		}
		$sql = 'SELECT * FROM l_yf_notification WHERE userid = ? ';
		if ($conditions) {
			$sql .= $conditions;
		}
		if ($limit) {
			$sql .= sprintf(' LIMIT %s', $limit);
		}
		$result = $db->pquery($sql, [$userId]);
		$entries = [];
		while ($row = $db->getRow($result)) {
			if ($groupBy) {
				$entries[$row['type']][] = Home_NoticeEntries_Model::getInstanceByRow($row, $this);
			} else {
				$entries[] = Home_NoticeEntries_Model::getInstanceByRow($row, $this);
			}
		}
		return $entries;
	}

	public function getNumberOfEntries()
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$result = $db->pquery('SELECT count(*) FROM l_yf_notification WHERE userid = ?', [$currentUser->getId()]);
		$count = $db->getSingleValue($result);
		$max = AppConfig::module('Home', 'MAX_NUMBER_NOTIFICATIONS');
		return $count > $max ? $max : $count;
	}

	public function save($parseContent = true)
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '| ');

		$currentUser = vglobal('current_user');
		$user = CRMEntity::getInstance('Users');
		$user->retrieveCurrentUserInfoFromFile($this->get('userid'));
		vglobal('current_user', $user);

		if (!Users_Privileges_Model::isPermitted('Dashboard', 'NotificationPreview')) {
			$log->warn('User ' . vtlib\Functions::getOwnerRecordLabel($this->get('userid')) . ' has no active notifications');
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' - return true');
			return false;
		}
		if ($this->get('moduleName') != 'Users' && !Users_Privileges_Model::isPermitted($this->get('moduleName'), 'DetailView', $this->get('record'))) {
			$log->error('User ' . vtlib\Functions::getOwnerRecordLabel($this->get('userid')) .
				' does not have permission for this record ' . $this->get('record'));
			$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' - return true');
			return false;
		}
		if ($parseContent) {
			$this->parseContent();
		}
		vglobal('current_user', $currentUser);

		if ($this->get('moduleName') != 'Users' && $this->get('type') == 0) {
			$this->set('record', Users_Privileges_Model::getCurrentUserPrivilegesModel()->getId());
			$this->set('moduleName', 'Users');
		}
		if (!$this->has('time')) {
			$this->set('time', date('Y-m-d H:i:s'));
		}
		$db = PearDatabase::getInstance();
		$db->insert('l_yf_notification', [
			'userid' => $this->get('userid'),
			'type' => $this->get('type'),
			'title' => $this->get('title'),
			'message' => $this->get('message'),
			'reletedid' => $this->get('record'),
			'reletedmodule' => $this->get('moduleName'),
			'time' => $this->get('time')
		]);

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' - return true');
		return true;
	}

	public function parseContent()
	{
		$message = Vtiger_Cache::get('NotificationParseContentM', $this->get('message') . $this->get('record'));
		if ($message === false) {
			$message = $this->get('message');

			$textParser = Vtiger_TextParser_Helper::getInstanceById($this->get('record'), $this->get('moduleName'));
			$textParser->set('withoutTranslations', true);
			$textParser->setContent($message);
			$message = $textParser->parse();

			$this->set('message', $message);
			Vtiger_Cache::set('NotificationParseContentM', $this->get('message') . $this->get('record'), $message);
		} else {
			$this->set('message', $message);
		}

		$title = Vtiger_Cache::get('NotificationParseContentT', $this->get('title') . $this->get('record'));
		if ($title === false) {
			$title = $this->get('title');

			$textParser = Vtiger_TextParser_Helper::getInstanceById($this->get('record'), $this->get('moduleName'));
			$textParser->set('withoutTranslations', true);
			$textParser->setContent($title);
			$title = $textParser->parse();

			$this->set('title', $title);
			Vtiger_Cache::set('NotificationParseContentT', $this->get('title') . $this->get('record'), $title);
		} else {
			$this->set('title', $title);
		}
	}
}
