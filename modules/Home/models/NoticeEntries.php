<?php

/**
 * Notification Entries Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Home_NoticeEntries_Model extends Vtiger_Base_Model
{

	public function getId()
	{
		return $this->get('id');
	}

	public function getUserId()
	{
		return $this->get('userid');
	}

	/**
	 * Function to get the instance
	 * @return <Home_Notification_Model>
	 */
	public static function getInstanceByRow($row, Home_Notification_Model $notificationModel)
	{
		$instance = new self();
		if (isset($row['reletedid']) && $row['reletedmodule'] != 'Users' && isRecordExists($row['reletedid'])) {
			$textParser = Vtiger_TextParser_Helper::getInstanceByModel(Vtiger_Record_Model::getInstanceById($row['reletedid'], $row['reletedmodule']));
		} else {
			$textParser = Vtiger_TextParser_Helper::getCleanInstance();
		}
		$textParser->setContent($row['title']);
		$row['title'] = $textParser->parseTranslations();
		$textParser->setContent($row['message']);
		$row['message'] = $textParser->parseTranslations();
		$instance->setData($row);
		$instance->set('notificationModel', $notificationModel);
		return $instance;
	}

	/**
	 * Function to get the instance by id
	 * @return <Home_Notification_Model>
	 */
	public static function getInstanceById($id)
	{
		$instance = Vtiger_Cache::get('Home_NotifiEntries_Model', $id);
		if ($instance) {
			return $instance;
		}

		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM l_yf_notification WHERE id = ?';
		$result = $db->pquery($sql, [$id]);
		if ($db->getRowCount($result) == 0) {
			throw new \Exception\NoPermitted('LBL_NOT_FOUND_NOTICE');
		}
		$instance = new self();
		$instance->setData($db->getRow($result));
		Vtiger_Cache::set('Home_NotifiEntries_Model', $id, $instance);
		return $instance;
	}

	public function setMarked()
	{
		$db = PearDatabase::getInstance();
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$this->set('mark_user', $userModel->getRealId());
		$this->set('mark_time', date('Y-m-d H:i:s'));
		$notice = $this->getData();
		$db->insert('l_yf_notification_archive', $notice);
		$db->delete('l_yf_notification', 'id = ?', [$this->getId()]);
	}

	public function getIcon()
	{
		$icon = false;
		switch ($this->get('type')) {
			case 0:
				$userModel = Users_Privileges_Model::getInstanceById($this->get('reletedid'));
				$icon = [
					'type' => 'image',
					'title' => $userModel->getName(),
					'src' => $userModel->getImagePath(),
					'class' => 'userImage',
				];
				break;
			default:
				$icon = [
					'type' => 'icon',
					'title' => vtranslate($this->get('reletedmodule'), $this->get('reletedmodule')),
					'class' => 'userIcon-' . $this->get('reletedmodule'),
				];
				break;
		}
		return $icon;
	}

	public function getMassage()
	{
		return $this->get('message');
	}

	public function getTitle()
	{
		return $this->get('title');
	}

	public function getTypeName()
	{
		$types = $this->get('notificationModel')->getTypes();
		return $types[$this->get('type')]['name'];
	}
}
