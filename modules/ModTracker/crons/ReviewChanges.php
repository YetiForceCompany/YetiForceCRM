<?php
/**
 * Cron task to review changes in records.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * ModTracker_ReviewChanges_Cron class.
 */
class ModTracker_ReviewChanges_Cron extends \App\CronHandler
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
		$this->limit = App\Config::module('ModTracker', 'REVIEWED_SCHEDULE_LIMIT');
		$this->displayed = ModTracker_Record_Model::DISPLAYED;
	}

	/** {@inheritdoc} */
	public function process()
	{
		$db = \App\Db::getInstance();
		$query = (new \App\Db\Query())->from('u_#__reviewed_queue');
		$dataReader = $query->createCommand($db)->query();
		while ($row = $dataReader->read()) {
			$this->clearData();
			$this->init($row);
			$this->reviewChanges();
			if ($this->isEnd()) {
				break;
			}
		}
		$dataReader->close();
	}

	/**
	 * Initiation of data.
	 *
	 * @param array $row
	 */
	public function init($row)
	{
		if (!\is_array($row)) {
			$row = [$row];
		}
		foreach ($row as $key => $value) {
			if ('data' === $key) {
				$value = \App\Json::decode($row['data']);
				$this->init($value);
			}
			$this->valueMap[$key] = $value;
		}
	}

	/**
	 * Clear data.
	 */
	public function clearData()
	{
		$this->done = [];
		$this->valueMap = [];
		$this->recordList = [];
	}

	/**
	 * Get key value.
	 *
	 * @param string $key
	 *
	 * @return key value
	 */
	private function get($key)
	{
		return $this->valueMap[$key];
	}

	/**
	 * Function to get records id.
	 *
	 * @return array - List of records
	 */
	private function getRecords()
	{
		$data = $this->get('data');
		if ('all' === ($this->get('selected_ids')[0] ?? '')) {
			$data['module'] = \App\Module::getModuleName($this->get('tabid'));
			$request = new \App\Request($data, false);
			$this->recordList = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		} else {
			$this->recordList = $this->get('selected_ids');
		}
		return $this->recordList;
	}

	/**
	 * Function marks forwarded records as reviewed.
	 */
	public function reviewChanges()
	{
		$db = \App\Db::getInstance();
		$recordsList = $this->getRecords();
		if (!empty($recordsList)) {
			foreach ($recordsList as $crmId) {
				if ($this->counter === $this->limit) {
					$this->end = true;
					break;
				}
				$query = (new \App\Db\Query())
					->select(['u' => 'last_reviewed_users', 'id', 'changedon'])
					->from('vtiger_modtracker_basic')
					->where(['crmid' => $crmId])
					->andWhere(['<>', 'status', $this->displayed])
					->orderBy(['changedon' => SORT_DESC, 'id' => SORT_DESC]);
				$dataReader = $query->createCommand($db)->query();
				while ($row = $dataReader->read()) {
					$userId = $this->get('userid');
					if (false !== strpos($row['u'], "#$userId#")) {
						break;
					}
					if (strtotime($row['time']) >= strtotime($this->get('changedon'))) {
						$changed = $this->setReviewed($row['id'], $row['u']);
						if ($changed) {
							ModTracker_Record_Model::unsetReviewed($crmId, $userId, $row['id']);
						}
						break;
					}
				}
				$dataReader->close();
				++$this->counter;
				$this->done[] = $crmId;
			}
			$this->finish();
		}
	}

	/**
	 * Function marks forwarded records as reviewed.
	 *
	 * @param mixed $id
	 * @param mixed $users
	 */
	private function setReviewed($id, $users)
	{
		$db = \App\Db::getInstance();
		$lastReviewedUsers = explode('#', $users);
		$lastReviewedUsers[] = $this->get('userid');

		return $db->createCommand()->update(
			'vtiger_modtracker_basic', ['last_reviewed_users' => '#' . implode('#', array_filter($lastReviewedUsers)) . '#'], ['id' => $id]
		)->execute();
	}

	/**
	 * Function to clean data in database.
	 */
	private function finish()
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('u_#__reviewed_queue', ['=', 'id', $this->get('id')])->execute();
		if (\count($this->done) < \count($this->recordList)) {
			$records = array_diff($this->recordList, $this->done);
			$this->addPartToDBRecursive($records);
		}
	}

	/**
	 * Function adds records to task queue that updates reviewing changes in records.
	 *
	 * @param mixed $records
	 */
	private function addPartToDBRecursive($records)
	{
		$db = \App\Db::getInstance();
		$list = array_splice($records, 0, self::MAX_RECORDS);
		$data = \App\Json::encode(['selected_ids' => $list]);
		$id = (new \App\Db\Query())
			->from('u_#__reviewed_queue')
			->max('id') + 1;
		$db->createCommand()->insert('u_#__reviewed_queue', [
			'id' => $id,
			'userid' => $this->get('userid'),
			'tabid' => $this->get('tabid'),
			'data' => $data,
			'time' => $this->get('time'),
		])->execute();
		if (!empty($records)) {
			$this->addPartToDBRecursive($records);
		}
	}

	/**
	 * Function to check the status cron.
	 *
	 * @return bool
	 */
	public function isEnd()
	{
		return $this->end;
	}
}
