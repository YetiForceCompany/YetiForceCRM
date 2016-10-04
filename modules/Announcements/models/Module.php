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
		if (AppRequest::get('view') == 'Login') {
			return false;
		}
		$this->loadAnnouncements();
		return !empty($this->announcements);
	}

	public function loadAnnouncements()
	{
		$db = PearDatabase::getInstance();
		$userModel = Users_Record_Model::getCurrentUserModel();
		$listView = Vtiger_ListView_Model::getInstance($this->getName());
		$queryGenerator = $listView->get('query_generator');
		$queryGenerator->setFields(['id', 'subject', 'description', 'assigned_user_id', 'createdtime']);
		$query = $queryGenerator->getQuery();
		$query .= ' && announcementstatus = ?';

		$result = $db->pquery($query, ['PLL_PUBLISHED']);
		while ($row = $db->getRow($result)) {
			$query = 'SELECT * FROM u_yf_announcement_mark WHERE announcementid = ? && userid = ?';
			$paramsMark = [$row['announcementid'], $userModel->getId()];
			if (!empty($row['interval'])) {
				$date = date('Y-m-d H:i:s', strtotime('+' . $row['interval'] . ' day', strtotime('now')));
				$paramsMark[] = 0;
				$paramsMark[] = $date;
				$query .= ' && status = ? && date < ?';
			}
			$resultMark = $db->pquery($query, $paramsMark);
			if ($db->getRowCount($resultMark) == 1) {
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
		$db = PearDatabase::getInstance();
		$userModel = Users_Record_Model::getCurrentUserModel();
		$params = [$record, $userModel->getId()];

		$result = $db->pquery('SELECT * FROM u_yf_announcement_mark WHERE announcementid = ? && userid = ?', $params);
		if ($db->getRowCount($result) == 0) {
			$db->insert('u_yf_announcement_mark', [
				'announcementid' => $record,
				'userid' => $userModel->getId(),
				'date' => date('Y-m-d H:i:s'),
				'status' => $state
			]);
		} else {
			$db->update('u_yf_announcement_mark', [
				'date' => date('Y-m-d H:i:s'),
				'status' => $state
				], 'announcementid = ? && userid = ?', $params
			);
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
			$recordModel->set('mode', 'edit');
			$recordModel->set('announcementstatus', 'PLL_ARCHIVES');
			$recordModel->save();
		}
	}

	public function getUsers($showAll = true)
	{
		$userModel = Users_Record_Model::getCurrentUserModel();
		if ($showAll) {
			$users = \includes\fields\Owner::getInstance()->getAccessibleUsers('Public');
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
