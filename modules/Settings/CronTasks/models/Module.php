<?php

/**
 * Settings CronTasks Module Model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_CronTasks_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 'vtiger_cron_task';
	public $baseIndex = 'id';
	public $listFields = [
		'sequence' => 'Sequence',
		'name' => 'Cron Job',
		'frequency' => 'Frequency(H:M)',
		'status' => 'Status',
		'laststart' => 'Last Start',
		'last_update' => 'Last update',
		'lastend' => 'Last End',
		'duration' => 'LBL_DURATION',
	];
	public $nameFields = [''];
	public $name = 'CronTasks';

	/**
	 * Save last cron time start between views (timestamp).
	 *
	 * @var int
	 */
	private $lastCronStart = 0;

	/**
	 * Function to get editable fields from this module.
	 *
	 * @return array List of fieldNames
	 */
	public function getEditableFieldsList()
	{
		return ['frequency', 'status'];
	}

	/**
	 * Function to update sequence of several records.
	 *
	 * @param array $sequencesList
	 */
	public function updateSequence($sequencesList)
	{
		$db = App\Db::getInstance();
		$caseSequence = 'CASE';
		foreach ($sequencesList as $sequence => $recordId) {
			$caseSequence .= ' WHEN ' . $db->quoteColumnName('id') . ' = ' . $db->quoteValue($recordId) . ' THEN ' . $db->quoteValue($sequence);
		}
		$caseSequence .= ' END';
		$db->createCommand()->update('vtiger_cron_task', ['sequence' => new yii\db\Expression($caseSequence)])->execute();
	}

	public function hasCreatePermissions()
	{
		return false;
	}

	public function isPagingSupported()
	{
		return false;
	}

	/**
	 * Returns last iteration start time.
	 *
	 * @return int timestamp
	 */
	public function getLastCronStart()
	{
		if ($this->lastCronStart) {
			return $this->lastCronStart;
		}
		$cronConfigFileName = ROOT_DIRECTORY . '/app_data/cron.php';
		if (file_exists($cronConfigFileName)) {
			$cronConfig = include $cronConfigFileName;
			if ($cronConfig && isset($cronConfig['last_start'])) {
				return $this->lastCronStart = (int) $cronConfig['last_start'];
			}
		}
		return 0;
	}

	/**
	 * Get last executed Cron info formated by user settings.
	 *
	 * @return array ['duration'=>'0g 0m 0s','laststart'=>'3 hours ago','tasks'=>2, 'finished_tasks'=>1]
	 */
	public function getLastCronIteration()
	{
		$result = [];
		$totalDiff = $finalLastStart = $finalLastEnd = $finishedTasks = 0;
		$timedout = false;
		$lastStart = $this->getLastCronStart();
		$tasks = (new \App\Db\Query())
			->from('vtiger_cron_task')
			->where(['status', [
				Settings_CronTasks_Record_Model::$STATUS_ENABLED,
				Settings_CronTasks_Record_Model::$STATUS_RUNNING,
				Settings_CronTasks_Record_Model::$STATUS_COMPLETED,
			],
			])
			->where(['>=', 'laststart', $lastStart])
			->createCommand()
			->query()
			->readAll();

		foreach ($tasks as $task) {
			$record = new Settings_CronTasks_Record_Model($task);
			$lastStart = (int) $record->get('laststart');
			$lastEnd = (int) $record->get('lastend');
			if (!$record->isRunning() && !$record->hadTimedout()) {
				++$finishedTasks;
				$totalDiff += (int) $record->getTimeDiff();
				if ($lastEnd > $finalLastEnd) {
					$finalLastEnd = $lastEnd;
				}
				if ($lastStart > $finalLastStart) {
					$finalLastStart = $lastStart;
				}
			} elseif ($record->hadTimedout()) {
				$timedout = $record;
			}
		}
		if ($timedout) {
			$result['duration'] = $timedout->getDuration();
		} else {
			$result['duration'] = \App\Fields\RangeTime::displayElapseTime($totalDiff, 's');
		}
		$result['laststart'] = empty($lastStart) ? ' - ' : \App\Fields\DateTime::formatToViewDate(date('Y-m-d H:i:s', $lastStart));
		$result['finished_tasks'] = $finishedTasks;
		$result['tasks'] = \count($tasks);
		return $result;
	}
}
