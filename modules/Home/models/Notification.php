<?php

/**
 * Notification Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Home_Notification_Model extends Vtiger_Base_Model
{
	protected $types = [
		0 => 'LBL_MESSAGE1',
		1 => 'LBL_MESSAGE2',
		2 => 'LBL_MESSAGE3',
	];
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
		return $this->types;
	}
	
	public function getEntries()
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$sql = 'SELECT * FROM l_yf_notification WHERE userid = ?';
		$result = $db->pquery($sql, [$currentUser->getId()]);
		$entries = [];
		while ($row = $db->getRow($result)) {
			$entries[] = Home_NoticeEntries_Model::getInstanceByRow($row);
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
			'reletedid' => $this->get('reletedid')
		]);
	}
}
