<?php
/**
 * Announcements Module Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Class Announcements_Module_Model.
 */
class Announcements_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Announcements.
	 *
	 * @var array
	 */
	protected $announcements = [];

	/**
	 * Check active.
	 *
	 * @return bool
	 */
	public function checkActive()
	{
		if (\App\Request::_get('view') == 'Login' || !$this->isActive()) {
			return false;
		}
		$this->loadAnnouncements();

		return !empty($this->announcements);
	}

	/**
	 * Load announcements.
	 */
	public function loadAnnouncements()
	{
		$queryGenerator = new \App\QueryGenerator($this->getName());
		$queryGenerator->setFields(['id', 'subject', 'description', 'assigned_user_id', 'createdtime', 'is_mandatory']);
		$query = $queryGenerator->createQuery();
		$query->andWhere(['announcementstatus' => 'PLL_PUBLISHED']);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$query = (new \App\Db\Query())
				->from('u_#__announcement_mark')
				->where(['announcementid' => $row['id'], 'userid' => \App\User::getCurrentUserId()]);
			if (!empty($row['interval'])) {
				$date = date('Y-m-d H:i:s', strtotime('+' . $row['interval'] . ' day', strtotime('now')));
				$query->andWhere(['status' => 0]);
				$query->andWhere(['<', 'date', $date]);
			}
			if ($query->count() !== 0) {
				continue;
			}
			$recordModel = $this->getRecordFromArray($row);
			$recordModel->setId($row['id']);
			$this->announcements[] = $recordModel;
		}
		$dataReader->close();
	}

	/**
	 * Get announcements.
	 *
	 * @return array
	 */
	public function getAnnouncements()
	{
		if (empty($this->announcements)) {
			$this->loadAnnouncements();
		}
		return $this->announcements;
	}

	/**
	 * Set mark.
	 *
	 * @param int $record
	 * @param int $state
	 */
	public function setMark($record, $state)
	{
		$db = \App\Db::getInstance();
		$query = (new \App\Db\Query())
			->from('u_#__announcement_mark')
			->where(['announcementid' => $record, 'userid' => \App\User::getCurrentUserId()])->limit(1);
		if ($query->scalar() === false) {
			$db->createCommand()
				->insert('u_#__announcement_mark', [
					'announcementid' => $record,
					'userid' => \App\User::getCurrentUserId(),
					'date' => date('Y-m-d H:i:s'),
					'status' => $state,
				])->execute();
		} else {
			$db->createCommand()
				->update('u_#__announcement_mark', [
					'date' => date('Y-m-d H:i:s'),
					'status' => $state,
					], ['announcementid' => $record, 'userid' => \App\User::getCurrentUserId()])
					->execute();
		}
		$this->checkStatus($record);
	}

	/**
	 * Check status.
	 *
	 * @param int $record
	 */
	public function checkStatus($record)
	{
		$archive = true;
		$users = $this->getUsers(true);
		foreach ($users as $userId => $name) {
			$result = (new App\Db\Query())->from('u_#__announcement_mark')->where(['announcementid' => $record, 'userid' => $userId, 'status' => 1])->count();
			if (!$result) {
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

	/**
	 * Get mark info.
	 *
	 * @param int $record
	 * @param int $userId
	 *
	 * @return array
	 */
	public function getMarkInfo($record, $userId)
	{
		return (new App\Db\Query())->from('u_#__announcement_mark')->where(['announcementid' => $record, 'userid' => $userId])->one();
	}
}
