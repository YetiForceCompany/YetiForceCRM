<?php

/**
 * Recurring Events Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Calendar_RecuringEvents_Model extends \App\Base
{
	public $recordModel;
	public $typeSaving;
	public $isNew;
	public $changes;
	public $templateRecordId;

	const UPDATE_ALL_EVENTS = 1;
	const UPDATE_THIS_EVENT = 2;
	const UPDATE_FUTURE_EVENTS = 3;

	/**
	 * Function to get empty instance.
	 *
	 * @return Calendar_RecuringEvents_Model
	 */
	public static function getInstance()
	{
		return new self();
	}

	/**
	 * Function to create new records in never ending events. Function uses only by cron.
	 *
	 * @param int $recordId
	 */
	public function updateNeverEndingEvents($recordId)
	{
		$record = Vtiger_Record_Model::getInstanceById($recordId);
		$cleanInstance = Vtiger_Record_Model::getCleanInstance($record->getModuleName());
		$cleanInstance->setData($record->getData());
		$this->recordModel = $cleanInstance;
		$records = $this->getLastRecord($recordId);
		$dates = $this->getDates($records['date_start'] . ' ' . $records['time_start'], $records['due_date'] . ' ' . $records['time_end']);
		unset($dates[0]);
		$endingDate = date('Y-m-d', strtotime(date('Y-m-d') . ' +1 year'));
		foreach ($dates as $date) {
			if ($endingDate > $date['startDate']) {
				$this->createRecords([$date]);
			}
		}
	}

	/**
	 * Function to get instance of class.
	 *
	 * @param \App\Request $request
	 *
	 * @return Calendar_RecuringEvents_Model
	 */
	public static function getInstanceFromRequest(App\Request $request)
	{
		$instance = new self();
		$moduleName = $request->getModule();
		$instance->recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$instance->isNew = $request->isEmpty('record');
		if (!$instance->isNew) {
			$instance->templateRecordId = $request->getInteger('record');
			$instance->typeSaving = $request->getInteger('typeSaving');
			if (empty($instance->typeSaving)) {
				$instance->typeSaving = self::UPDATE_THIS_EVENT;
			}
		}
		return $instance;
	}

	/**
	 * Function to set data to record model.
	 *
	 * @param array $values
	 */
	public function setData($values)
	{
		$this->recordModel->setData($values);
	}

	/**
	 * Function to create new records.
	 *
	 * @param array $dates
	 */
	public function createRecords($dates)
	{
		foreach ($dates as $date) {
			$record = clone $this->recordModel;
			$record->set('date_start', $date['startDate']);
			$record->set('time_start', $date['startTime']);
			$record->set('due_date', $date['endDate']);
			$record->set('time_end', $date['endTime']);
			$record->save();
		}
	}

	/**
	 * Function to set changes which user modified.
	 *
	 * @param type $values
	 */
	public function setChanges($values)
	{
		$this->changes = $values;
	}

	/**
	 * Update ommited records, change recuring rule for each records.
	 *
	 * @param type $records
	 * @param type $dateStart
	 */
	public function updateOmmitedRecords($records, $dateStart)
	{
		foreach ($records as $recordId) {
			$record = Vtiger_Record_Model::getInstanceById($recordId);
			$rule = new \Recurr\Rule($record->get('recurrence'));
			$rule->setUntil(new \DateTime($dateStart));
			$record->set('recurrence', $rule->getString());
			$record->save();
		}
	}

	/**
	 * Function to edit record.
	 *
	 * @param int   $recordId
	 * @param array $dates
	 */
	public function updateRecord($recordId, $dates)
	{
		$record = Vtiger_Record_Model::getInstanceById($recordId);
		foreach ($this->changes as $fieldName => $value) {
			$record->set($fieldName, $this->recordModel->get($fieldName));
		}
		$record->set('date_start', $dates['startDate']);
		$record->set('time_start', $dates['startTime']);
		$record->set('due_date', $dates['endDate']);
		$record->set('time_end', $dates['endTime']);
		$record->save();
	}

	/**
	 * Save records.
	 */
	public function save()
	{
		if (!$this->isNew) {
			switch ($this->typeSaving) {
				case self::UPDATE_ALL_EVENTS:
					$dates = [];
					$recordsIds = $this->getRecords($this->recordModel->get('followup'));
					$itemNumber = 0;
					foreach ($recordsIds as $recordId => $data) {
						if (0 === $itemNumber) {
							$dates = $this->getDates($data['date_start'] . ' ' . $data['time_start'], $data['due_date'] . ' ' . $data['time_end']);
						}
						if ($recordId === $this->templateRecordId) {
							unset($dates[$itemNumber]);
							++$itemNumber;
							continue;
						}
						if (isset($dates[$itemNumber])) {
							$this->updateRecord($recordId, $dates[$itemNumber]);
							unset($dates[$itemNumber]);
						} else {
							Vtiger_Record_Model::getInstanceById($recordId)->delete();
						}
						++$itemNumber;
					}
					if ($dates) {
						$this->createRecords($dates);
					}
					break;
				case self::UPDATE_FUTURE_EVENTS:
					$recordsIds = $this->getRecords($this->recordModel->get('followup'));
					$itemNumber = 0;
					$skip = true;
					$omittedRecords = [];
					$dates = reset($recordsIds);
					$dates = $this->getDates($dates['date_start'] . ' ' . $dates['time_start'], $dates['due_date'] . ' ' . $dates['time_end'], $dates['recurrence']);
					foreach ($recordsIds as $recordId => $data) {
						if ($skip && $data['date_start'] >= $this->recordModel->get('date_start')) {
							$skip = false;
							if (!($this->recordModel->getPreviousValue('recurrence') === $this->recordModel->get('recurrence'))) {
								$this->updateOmmitedRecords($omittedRecords, $data['date_start']);
								$this->changes['followup'] = $recordId;
								$this->recordModel->set('followup', $recordId);
								$dates = $this->getDates($data['date_start'] . ' ' . $data['time_start'], $data['due_date'] . ' ' . $data['time_end']);
								$itemNumber = 0;
							}
						}
						if ($skip) {
							$omittedRecords[] = $recordId;
							unset($dates[$itemNumber]);
							++$itemNumber;
							continue;
						}
						if (isset($dates[$itemNumber])) {
							$this->updateRecord($recordId, $dates[$itemNumber]);
							unset($dates[$itemNumber]);
						} else {
							Vtiger_Record_Model::getInstanceById($recordId)->delete();
						}
						++$itemNumber;
					}
					if ($dates) {
						$this->createRecords($dates);
					}
					break;
				default:
					break;
			}
		} else {
			$dates = $this->getDates($this->recordModel->get('date_start') . ' ' . $this->recordModel->get('time_start'), $this->recordModel->get('due_date') . ' ' . $this->recordModel->get('time_end'));
			unset($dates[0]);
			if ($dates) {
				$this->createRecords($dates);
			}
		}
	}

	/**
	 * Function to remove records.
	 */
	public function delete()
	{
		switch ($this->typeSaving) {
			case self::UPDATE_ALL_EVENTS:
				$records = $this->getRecords($this->recordModel->get('followup'));
				foreach ($records as $recordId => $data) {
					if ($recordId !== $this->templateRecordId) {
						Vtiger_Record_Model::getInstanceById($recordId)->changeState('Trash');
					}
				}
				break;
			case self::UPDATE_FUTURE_EVENTS:
				$recordsIds = $this->getRecords($this->recordModel->get('followup'));
				$skip = true;
				$omittedRecords = [];
				foreach ($recordsIds as $recordId => $data) {
					if ($skip && $data['date_start'] >= $this->recordModel->get('date_start')) {
						$this->updateOmmitedRecords($omittedRecords, $data['date_start']);
						$skip = false;
					}
					if ($skip) {
						$omittedRecords[] = $recordId;
						continue;
					}
					Vtiger_Record_Model::getInstanceById($recordId)->changeState('Trash');
				}
				break;
			case self::UPDATE_THIS_EVENT:
				if ($this->templateRecordId === $this->recordModel->get('followup')) {
					$recordsIds = $this->getRecords($this->recordModel->get('followup'));
					$skip = true;
					foreach ($recordsIds as $recordId => $data) {
						if ($data['date_start'] >= $this->recordModel->get('date_start')) {
							App\Db::getInstance()->createCommand()->update('vtiger_activity', ['followup' => $recordId, 'reapeat' => 1], ['followup' => $this->templateRecordId])->execute();
							break;
						}
					}
				}
				break;
			default:
				break;
		}
	}

	/**
	 * Check if recurrence rule is never ending.
	 *
	 * @param type $recurrenceRule
	 *
	 * @return type
	 */
	public function isNeverEndingRule($recurrenceRule)
	{
		return false === strpos($recurrenceRule, 'COUNT') && false === strpos($recurrenceRule, 'UNTIL');
	}

	/**
	 * Function to get dates.
	 *
	 * @param string $startDateTime
	 * @param string $endDateTime
	 * @param string $recurrenceRule
	 *
	 * @return array
	 */
	public function getDates($startDateTime, $endDateTime, $recurrenceRule = false)
	{
		if (!$recurrenceRule) {
			$recurrenceRule = $this->recordModel->get('recurrence');
		}
		$isNeverEnding = $this->isNeverEndingRule($recurrenceRule);
		if ($isNeverEnding) {
			$endingDate = date('Y-m-d', strtotime(date('Y-m-d', strtotime($startDateTime)) . ' +1 year'));
		}
		$rule = new \Recurr\Rule($recurrenceRule, new \DateTime($startDateTime), new \DateTime($endDateTime));
		$data = (new \Recurr\Transformer\ArrayTransformer())->transform($rule);
		$dates = [];
		foreach ($data as $date) {
			if ($isNeverEnding && $date->getStart()->format('Y-m-d') > $endingDate) {
				break;
			}
			$dates[] = [
				'startDate' => $date->getStart()->format('Y-m-d'),
				'startTime' => $date->getStart()->format('H:i:s'),
				'endDate' => $date->getEnd()->format('Y-m-d'),
				'endTime' => $date->getEnd()->format('H:i:s'),
			];
		}
		return $dates;
	}

	/**
	 * Function to get related records.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function getRecords($id)
	{
		return (new App\Db\Query())->from('vtiger_activity')
			->where(['followup' => $id, 'deleted' => 0, 'reapeat' => 1])
			->orderBy(['date_start' => SORT_ASC])
			->indexBy('activityid')
			->all();
	}

	/**
	 * Function to get the last record in series.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function getLastRecord($id)
	{
		return (new App\Db\Query())->from('vtiger_activity')
			->where(['followup' => $id, 'deleted' => 0, 'reapeat' => 1])
			->orderBy(['date_start' => SORT_DESC])
			->limit(1)
			->indexBy('activityid')
			->one();
	}
}
