<?php
/**
 * Announcements Module Model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		if ('Login' == \App\Request::_get('view') || !$this->isActive() || !\App\Privilege::isPermitted($this->getName())) {
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
		$queryGenerator->setFields(['id', 'subject', 'description', 'assigned_user_id', 'createdtime', 'is_mandatory', 'interval']);
		$query = $queryGenerator->createQuery();
		$query->andWhere(['announcementstatus' => 'PLL_PUBLISHED']);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$query = (new \App\Db\Query())
				->from('u_#__announcement_mark')
				->where(['announcementid' => $row['id'], 'userid' => \App\User::getCurrentUserId()]);
			if (!empty($row['interval'])) {
				$query->andWhere(['or',
					['status' => 1],
					['and',
						['status' => 0],
						['>', 'date', date('Y-m-d H:i:s')]
					]
				]);
			}
			if (0 !== $query->count()) {
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
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $this);
		$date = date('Y-m-d H:i:s', strtotime('+' . (int) $recordModel->get('interval') . ' day'));
		if (false === $query->scalar()) {
			$db->createCommand()
				->insert('u_#__announcement_mark', [
					'announcementid' => $record,
					'userid' => \App\User::getCurrentUserId(),
					'date' => $date,
					'status' => $state,
				])->execute();
		} else {
			$db->createCommand()
				->update('u_#__announcement_mark', [
					'date' => $date,
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
