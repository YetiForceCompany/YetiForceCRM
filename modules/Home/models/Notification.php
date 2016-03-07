<?php

/**
 * Notification Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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

	public function getTypes()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$role = str_replace('H', '', $currentUser->get('roleid'));
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM a_yf_notification_type WHERE role = ? OR role = ?', [0, $role]);
		$types = [];
		while ($row = $db->getRow($result)) {
			$types[$row['id']] = $row;
		}
		return $types;
	}

	public function getEntries()
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$sql = 'SELECT * FROM l_yf_notification WHERE userid = ?';
		$result = $db->pquery($sql, [$currentUser->getId()]);
		$entries = [];
		while ($row = $db->getRow($result)) {
			$entries[$row['type']][] = Home_NoticeEntries_Model::getInstanceByRow($row);
		}
		return $entries;
	}

	public function save()
	{
		$db = PearDatabase::getInstance();
		$db->insert('l_yf_notification', [
			'userid' => $this->get('userid'),
			'type' => $this->get('type'),
			'message' => $this->get('message'),
			'reletedid' => $this->get('record')
		]);
	}

	public function parseContent()
	{
		$message = Vtiger_Cache::get('NotificationParseContent', $this->get('message') . $this->get('record'));
		if ($message !== false) {
			$this->set('message', $message);
			return $message;
		}
		$message = $this->get('message');
		if (preg_match_all('/\$[a-zA-Z_]+\$/', $message, $matches)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($this->get('record'), $this->get('moduleName'));
			$matches = $matches[0];
			foreach ($matches as $matche) {
				$name = substr($matche, 1, -1);
				if ($recordModel->has($name)) {
					$value = $recordModel->getDisplayValue($name, $this->get('record'), true);
				} else {
					$value = $this->getSpecialFunction($name, $recordModel);
				}
				$message = str_replace($matche, $value, $message);
			}
		}
		$this->set('message', $message);
		Vtiger_Cache::set('NotificationParseContent', $this->get('message') . $this->get('record'), $message);
	}

	public function getSpecialFunction($name, Vtiger_Record_Model $recordModel)
	{
		$value = '';
		switch ($name) {
			case '_RecordLabel_':
				$value = $recordModel->getName();
				break;
		}
		return $value;
	}
}
