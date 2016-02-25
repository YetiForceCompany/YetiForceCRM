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
}
