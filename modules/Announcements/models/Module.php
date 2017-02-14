<?php

/**
 * Announcements Module Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Announcements_Module_Model extends Vtiger_Module_Model
{

	protected $announcements = [];

	public function checkActive()
	{
		if (AppRequest::get('view') == 'Login' || !$this->isActive()) {
			return false;
		}
		$this->loadAnnouncements();
		return !empty($this->announcements);
	}

	public function loadAnnouncements()
	{
		$queryGenerator = new \App\QueryGenerator($this->getName());
		$queryGenerator->setFields(['id', 'subject', 'description', 'assigned_user_id', 'createdtime']);
		$query = $queryGenerator->createQuery();
		$query->andWhere(['announcementstatus' => 'PLL_PUBLISHED']);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$query = (new \App\Db\Query())
				->from('u_#__announcement_mark')
				->where(['announcementid' => $row['announcementid'], 'userid' => \App\User::getCurrentUserId()]);
			if (!empty($row['interval'])) {
				$date = date('Y-m-d H:i:s', strtotime('+' . $row['interval'] . ' day', strtotime('now')));
				$query->andWhere(['status' => 0]);
				$query->andWhere(['<', 'date', $date]);
			}
			if ($query->count() !== 0) {
				continue;
			}
			$recordModel = $this->getRecordFromArray($row);
			$recordModel->set('id', $row['announcementid']);
			$this->announcements[] = $recordModel;
		}
	}

	public function getAnnouncements()
	{
		if (empty($this->announcements)) {
			$this->loadAnnouncements();
		}
		return $this->announcements;
	}

	public function setMark($record, $state)
	{
		$db = \App\Db::getInstance();

		$query = (new \App\Db\Query())
				->from('u_#__announcement_mark')
				->where(['announcementid' => $record, 'userid' => \App\User::getCurrentUserId()])->limit(1);
		if ($query->scalar() !== false) {
			$db->createCommand()
				->insert('u_#__announcement_mark', [
					'announcementid' => $record,
					'userid' => \App\User::getCurrentUserId(),
					'date' => date('Y-m-d H:i:s'),
					'status' => $state
				])->execute();
		} else {
			$db->createCommand()
				->update('u_#__announcement_mark', [
					'date' => date('Y-m-d H:i:s'),
					'status' => $state
					], ['announcementid' => $record, 'userid' => \App\User::getCurrentUserId()])
				->execute();
		}
		$this->checkStatus($record);
	}

	public function checkStatus($record)
	{
		$archive = true;
		$db = PearDatabase::getInstance();
		$users = $this->getUsers(true);
		foreach ($users as $userId => $name) {
			$result = $db->pquery('SELECT count(*) FROM u_yf_announcement_mark WHERE announcementid = ? && userid = ? && status = ?', [$record, $userId, 1]);
			if ($db->getSingleValue($result) == 0) {
				$archive = false;
			}
		}
		if ($archive) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $this->getName());
			$recordModel->set('announcementstatus', 'PLL_ARCHIVES');
			$recordModel->save();
		}
	}

	public function getUsers($showAll = true)
	{
		$userModel = Users_Record_Model::getCurrentUserModel();
		if ($showAll) {
			$users = \App\Fields\Owner::getInstance()->getAccessibleUsers('Public');
		} else {
			$users = $userModel->getRoleBasedSubordinateUsers();
		}
		return $users;
	}

	public function getMarkInfo($record, $userId)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM u_yf_announcement_mark WHERE announcementid = ? && userid = ?', [$record, $userId]);
		while ($row = $db->getRow($result)) {
			return $row;
		}
		return [];
	}
}
