<?php
/**
 * Cron task to review changes in records
 * @package YetiForce.CRON
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
$db = PearDatabase::getInstance();
$reviewed = new Cron_Reviewed();
$result = $db->query('SELECT * FROM u_yf_reviewed_queue');
while ($row = $db->getRow($result)) {
	$reviewed->clearData();
	$reviewed->init($row);
	$reviewed->reviewChanges();
	if ($reviewed->isEnd()) {
		break;
	}
}

class Cron_Reviewed
{

	const MAX_RECORDS = 200;

	private $limit;
	private $displayed;
	private $counter = 0;
	private $done = [];
	private $valueMap = [];
	private $recordList = [];
	private $end = false;

	public function __construct()
	{
		$this->limit = AppConfig::module('ModTracker', 'REVIEWED_SCHEDULE_LIMIT');
		$this->displayed = ModTracker_Record_Model::DISPLAYED;
	}

	/**
	 * Initiation of data
	 * @param <array> $row
	 */
	public function init($row)
	{
		if (!is_array($row)) {
			$row = [$row];
		}
		foreach ($row as $key => $value) {
			if ($key === 'data') {
				$value = \includes\utils\Json::decode($row['data']);
				$this->init($value);
			}
			$this->valueMap[$key] = $value;
		}
	}

	/**
	 * Clear data
	 */
	public function clearData()
	{
		$this->done = [];
		$this->valueMap = [];
		$this->recordList = [];
	}

	/**
	 * Get key value
	 * @param <string> $key
	 * @return key value
	 */
	private function get($key)
	{
		return $this->valueMap[$key];
	}

	/**
	 * Function to get records id
	 * @return <array> - List of records
	 */
	private function getRecords()
	{
		$data = $this->get('data');
		if ('all' === $this->get('selected_ids')) {
			$data['module'] = \vtlib\Functions::getModuleName($this->get('tabid'));
			$request = new Vtiger_Request($data, $data);
			$this->recordList = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		} else {
			$this->recordList = $this->get('selected_ids');
		}
		return $this->recordList;
	}

	/**
	 * Function marks forwarded records as reviewed
	 */
	public function reviewChanges()
	{
		$db = PearDatabase::getInstance();
		$recordsList = $this->getRecords();
		if (!empty($recordsList)) {
			foreach ($recordsList as $crmId) {
				if ($this->counter === $this->limit) {
					$this->end = true;
					break;
				}
				$listQuery = 'SELECT `last_reviewed_users` as u, `id`, `changedon` FROM vtiger_modtracker_basic WHERE crmid = ? && status <> ? ORDER BY changedon DESC, id DESC;';
				$result = $db->pquery($listQuery, [$crmId, $this->displayed]);
				while ($row = $db->getRow($result)) {
					$userId = $this->get('userid');
					if (strpos($row['u'], "#$userId#") !== false) {
						break;
					} elseif (strtotime($row['time']) >= strtotime($this->get('changedon'))) {
						$changed = $this->setReviewed($row['id'], $row['u']);
						if ($changed) {
							ModTracker_Record_Model::unsetReviewed($crmId, $userId, $row['id']);
						}
						break;
					}
				}
				$this->counter++;
				$this->done[] = $crmId;
			}
			$this->finish();
		}
	}

	/**
	 * Function marks forwarded records as reviewed
	 */
	private function setReviewed($id, $users)
	{
		$db = PearDatabase::getInstance();
		$lastReviewedUsers = explode('#', $users);
		$lastReviewedUsers[] = $this->get('userid');
		return $db->update('vtiger_modtracker_basic', ['last_reviewed_users' => '#' . implode('#', array_filter($lastReviewedUsers)) . '#'], ' `id` = ?', [$id]);
	}

	/**
	 * Function to clean data in database
	 */
	private function finish()
	{
		$db = PearDatabase::getInstance();
		$db->delete('u_yf_reviewed_queue', '`id` = ?', [$this->get('id')]);
		if (count($this->done) < count($this->recordList)) {
			$records = array_diff($this->recordList, $this->done);
			$this->addPartToDBRecursive($records);
		}
	}

	/**
	 * Function adds records to task queue that updates reviewing changes in records
	 */
	private function addPartToDBRecursive($records)
	{
		$db = PearDatabase::getInstance();
		$list = array_splice($records, 0, self::MAX_RECORDS);
		$data = \includes\utils\Json::encode(['selected_ids' => $list]);
		$id = $db->getUniqueID('u_yf_reviewed_queue');
		$db->insert('u_yf_reviewed_queue', [
			'id' => $id,
			'userid' => $this->get('userid'),
			'tabid' => $this->get('tabid'),
			'data' => $data,
			'time' => $this->get('time')
		]);
		if (!empty($records)) {
			$this->addPartToDBRecursive($records);
		}
	}

	/**
	 * Function to check the status cron
	 * @return <boolean>
	 */
	public function isEnd()
	{
		return $this->end;
	}
}
