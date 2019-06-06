<?php
/**
 * Record status service file.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */

namespace App;

/**
 * Record status service class.
 */
class RecordStatus
{
	/**
	 * Variable using in picklist record state.
	 *
	 * @var int
	 */
	const RECORD_STATE_NO_CONCERN = 0;
	/**
	 * Variable using in picklist record state.
	 *
	 * @var int
	 */
	const RECORD_STATE_OPEN = 1;
	/**
	 * Variable using in picklist record state.
	 *
	 * @var int
	 */
	const RECORD_STATE_CLOSED = 2;
	/**
	 * Variable used to count times in specified categories.
	 *
	 * @var int
	 */
	const TIME_COUNTING_REACTION = 1;
	/**
	 * Variable used to count times in specified categories.
	 *
	 * @var int
	 */
	const TIME_COUNTING_RESOLVE = 2;
	/**
	 * Variable used to count times in specified categories.
	 *
	 * @var int
	 */
	const TIME_COUNTING_IDLE = 3;
	/**
	 * Fields key by state time.
	 *
	 * @var string[]
	 */
	private static $fieldsByStateTime = [
		self::TIME_COUNTING_REACTION => 'response',
		self::TIME_COUNTING_RESOLVE => 'solution',
		self::TIME_COUNTING_IDLE => 'idle',
	];

	/**
	 * State time fields.
	 *
	 * @var array
	 */
	private static $stateTimeFields = [
		'RangeTime' => [
			'response_range_time' => 'FL_RESPONSE_RANGE_TIME',
			'solution_range_time' => 'FL_SOLUTION_RANGE_TIME',
			'idle_range_time' => 'FL_IDLE_RANGE_TIME',
			'closing_range_time' => 'FL_CLOSING_RANGE_TIME',
		],
		'DateTime' => [
			'response_datatime' => 'FL_RESPONSE_DATE_TIME',
			'solution_datatime' => 'FL_SOLUTION_DATE_TIME',
			'idle_datatime' => 'FL_IDLE_DATE_TIME',
			'closing_datatime' => 'FL_CLOSING_DATE_TIME',
			'response_expected' => 'FL_RESPONSE_EXPECTED',
			'solution_expected' => 'FL_SOLUTION_EXPECTED',
			'idle_expected' => 'FL_IDLE_DATE_EXPECTED',
		]
	];

	/**
	 * Get all record states.
	 *
	 * @return string[] [id=>label]
	 */
	public static function getLabels(): array
	{
		return [
			self::RECORD_STATE_NO_CONCERN => 'LBL_RECORD_STATE_NO_CONCERN',
			self::RECORD_STATE_OPEN => 'LBL_RECORD_STATE_OPEN',
			self::RECORD_STATE_CLOSED => 'LBL_RECORD_STATE_CLOSED'
		];
	}

	/**
	 * Get record state statuses by module name.
	 *
	 * @param string   $moduleName
	 * @param int|null $state
	 *
	 * @return array if state is specified values are labels, if not values are record_states, key is always primary key
	 */
	public static function getStates(string $moduleName, int $state = null)
	{
		$fieldName = static::getFieldName($moduleName);
		$primaryKey = Fields\Picklist::getPickListId($fieldName);
		$values = [];
		foreach (Fields\Picklist::getValues($fieldName) as $value) {
			if (isset($value['record_state']) && $state === $value['record_state']) {
				$values[$value[$primaryKey]] = $value['picklistValue'];
			} elseif (null === $state) {
				$values[$value[$primaryKey]] = $value['record_state'];
			}
		}
		return $values;
	}

	/**
	 * Activate of the record status mechanism.
	 *
	 * @param string $moduleName
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public static function activate(string $moduleName, string $fieldName): bool
	{
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		if (!($fieldModel = $moduleModel->getFieldByName($fieldName))) {
			return false;
		}
		$db = Db::getInstance();
		$schema = $db->getSchema();
		$dbCommand = $db->createCommand();
		$params = $fieldModel->getFieldParams();
		$params['isProcessStatusField'] = true;
		$fieldModel->set('fieldparams', Json::encode($params));
		$fieldModel->save();
		$tableStatusHistory = $moduleModel->get('basetable') . '_state_history';
		if (!$db->getTableSchema($tableStatusHistory)) {
			$db->createTable($tableStatusHistory, [
				'id' => \yii\db\Schema::TYPE_UPK,
				'crmid' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_INTEGER, 11),
				'before' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1)->notNull()->defaultValue(0),
				'after' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1)->notNull()->defaultValue(0),
				'date' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TIMESTAMP)->null(),
			]);
			$dbCommand->createIndex($tableStatusHistory . '_crmid_idx', $tableStatusHistory, 'crmid')->execute();
			$dbCommand->addForeignKey('fk_1_' . $tableStatusHistory, $tableStatusHistory, 'crmid', 'vtiger_crmentity', 'crmid', 'CASCADE', 'RESTRICT')->execute();
		}
		$tableName = Fields\Picklist::getPicklistTableName($fieldName);
		$tableSchema = $db->getTableSchema($tableName);
		if (!isset($tableSchema->columns['record_state'])) {
			$dbCommand->addColumn($tableName, 'record_state', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1)->notNull()->defaultValue(0))->execute();
		}
		if (!isset($tableSchema->columns['time_counting'])) {
			$dbCommand->addColumn($tableName, 'time_counting', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1)->notNull()->defaultValue(0))->execute();
		}
		foreach (EventHandler::getAll(false) as $handler) {
			if ('Vtiger_RecordStatusHistory_Handler' === $handler['handler_class']) {
				$modules = $handler['include_modules'] ? \explode(',', $handler['include_modules']) : [];
				if (!\in_array($moduleName, $modules)) {
					$modules[] = $moduleName;
				}
				EventHandler::update([
					'is_active' => 1,
					'include_modules' => \implode(',', $modules)
				], $handler['eventhandler_id']);
			}
		}
		static::addFieldsAndBlock($moduleName);
		return (bool) $fieldModel;
	}

	/**
	 * Add block and fields.
	 *
	 * @param string $moduleName
	 *
	 * @return void
	 */
	public static function addFieldsAndBlock(string $moduleName)
	{
		$moduleModel = \Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);
		$blockId = (new \App\Db\Query())->select(['blockid'])->from('vtiger_blocks')->where(['blocklabel' => 'BL_RECORD_STATUS_TIMES', 'tabid' => $moduleModel->getId()])->scalar();
		if (!$blockId) {
			$blockInstance = new \Settings_LayoutEditor_Block_Model();
			$blockInstance->set('label', 'BL_RECORD_STATUS_TIMES');
			$blockId = $blockInstance->save($moduleModel);
		}
		$allFields = $moduleModel->getFields();
		foreach (static::$stateTimeFields as $type => $fields) {
			foreach ($fields as $name => $label) {
				if (!isset($allFields[$name])) {
					$moduleModel->addField($type, $blockId, [
						'fieldLabel' => $label,
						'fieldName' => $name,
						'fieldTypeList' => 0,
						'generatedtype' => 1,
						'displayType' => 9,
					]);
				}
			}
		}
	}

	/**
	 * Deactivate of the record status mechanism.
	 *
	 * @param string $moduleName
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public static function deactivate(string $moduleName, string $fieldName): bool
	{
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		if (!($fieldModel = $moduleModel->getFieldByName($fieldName))) {
			return false;
		}
		$db = Db::getInstance();
		$dbCommand = $db->createCommand();
		$params = $fieldModel->getFieldParams();
		unset($params['isProcessStatusField']);
		$fieldModel->set('fieldparams', Json::encode($params));
		$fieldModel->save();
		$dbCommand->dropTable($moduleModel->get('basetable') . '_state_history')->execute();
		$tableName = Fields\Picklist::getPicklistTableName($fieldName);
		$tableSchema = $db->getTableSchema($tableName);
		if (isset($tableSchema->columns['record_state'])) {
			$dbCommand->dropColumn($tableName, 'record_state')->execute();
		}
		if (isset($tableSchema->columns['time_counting'])) {
			$dbCommand->dropColumn($tableName, 'time_counting')->execute();
		}
		foreach (EventHandler::getAll() as $handler) {
			if ('Vtiger_RecordStatusHistory_Handler' === $handler['handler_class']) {
				$modules = $handler['include_modules'] ? \explode(',', $handler['include_modules']) : [];
				if (\in_array($moduleName, $modules)) {
					unset($modules[array_search($moduleName, $modules)]);
				}
				EventHandler::update([
					'is_active' => $modules ? 1 : 0,
					'include_modules' => \implode(',', $modules)
				], $handler['eventhandler_id']);
			}
		}
		return (bool) $fieldModel;
	}

	/**
	 * Add date history status to table.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param string               $fieldName
	 */
	public static function addHistory(\Vtiger_Record_Model $recordModel, string $fieldName)
	{
		$timeCountingValues = self::getTimeCountingValues($fieldName);
		$before = $timeCountingValues[$recordModel->getPreviousValue($fieldName)] ?? 0;
		$after = $timeCountingValues[$recordModel->get($fieldName)] ?? 0;
		if ($before !== $after) {
			\App\Db::getInstance()
				->createCommand()
				->insert($recordModel->getModule()->get('basetable') . '_state_history', [
					'crmid' => $recordModel->getId(),
					'before' => $before,
					'after' => $after,
					'date' => date('Y-m-d H:i:s')
				])->execute();
			Cache::save("RecordStatus::StateDates::{$recordModel->getId()}", $after, date('Y-m-d H:i:s'));
		}
	}

	/**
	 * Update status times.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param string               $fieldName
	 */
	public static function update(\Vtiger_Record_Model $recordModel, string $fieldName)
	{
		$timeCountingValues = self::getTimeCountingValues($fieldName);
		$previous = $recordModel->getPreviousValue($fieldName);
		$current = $recordModel->get($fieldName);
		if ($previous && isset($timeCountingValues[$previous]) && ($timeCountingValues[$current] ?? '') !== $timeCountingValues[$previous]
		&& ($date = self::getStateDate($recordModel, $timeCountingValues[$previous])) && ($key = self::$fieldsByStateTime[$timeCountingValues[$previous]] ?? '')) {
			$recordModel->set($key . '_range_time', self::getDiff($date,'',$recordModel));
			$recordModel->set($key . '_datatime', date('Y-m-d H:i:s'));
		}
	}

	/**
	 * Get date date from the status change history by status.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param int                  $value
	 * @param int                  $state
	 *
	 * @return string
	 */
	public static function getStateDate(\Vtiger_Record_Model $recordModel, int $state): string
	{
		$cacheName = "RecordStatus::StateDates::{$recordModel->getId()}";
		if (Cache::has($cacheName, $state)) {
			return Cache::get($cacheName, $state);
		}
		$date = (new Db\Query())->select(['date'])
			->from($recordModel->getModule()->get('basetable') . '_state_history')
			->where(['crmid' => $recordModel->getId(), 'after' => $state])->orderBy(['date' => SORT_DESC])
			->limit(1)->scalar();
		Cache::save($cacheName, $state, $date);
		return $date;
	}

	/**
	 * Function returning difference in format between date times.
	 *
	 * @param string               $start
	 * @param string               $end
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public static function getDiff(string $start, string $end = '', \Vtiger_Record_Model $recordModel): int
	{
		if (!$end) {
			$end = date('Y-m-d H:i:s');
		}
		if ($field = Field::getRelatedFieldForModule($recordModel->getModuleName(), 'ServiceContracts')) {
			return self::getDiffFromServiceContracts($start, $end, $recordModel->get($field['fieldname']));
		}
		$diff = self::getDiffFromDefaultBusinessHours($start, $end);
		if (!$diff) {
			$diff = round(Fields\DateTime::getDiff($start, $end, 'minutes'));
		}
		return  $diff;
	}

	/**
	 * Get time counting values grouped by id from field name.
	 *
	 * @param string $fieldName
	 *
	 * @return array
	 */
	public static function getTimeCountingValues(string $fieldName)
	{
		$values = [];
		foreach (Fields\Picklist::getValues($fieldName) as $row) {
			if (isset($row['time_counting'])) {
				$values[$row[$fieldName]] = (int) $row['time_counting'];
			}
		}
		return $values;
	}

	/**
	 * Get time counting values grouped by id from field name.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getTimeCountingIds(string $moduleName)
	{
		$fieldName = static::getFieldName($moduleName);
		if (!$fieldName) {
			return [];
		}
		$primaryKey = Fields\Picklist::getPickListId($fieldName);
		$values = [];
		foreach (Fields\Picklist::getValues($fieldName) as $row) {
			if (isset($row['time_counting'])) {
				$values[$row[$primaryKey]] = (int) $row['time_counting'];
			}
		}
		return $values;
	}

	/**
	 * Get closing states for all fields in module.
	 *
	 * @param string $moduleName
	 * @param bool   $byName
	 *
	 * @return string[]
	 */
	public static function getLockStatus(string $moduleName, bool $byName = true)
	{
		$tabId = Module::getModuleId($moduleName);
		$cacheName = "RecordStatus::getLockStatus::$moduleName";
		if (Cache::has($cacheName, $byName)) {
			return Cache::get($cacheName, $byName);
		}
		$field = $byName ? ['vtiger_field.fieldname', 'value'] : ['valueid', 'value'];
		$values = (new Db\Query())->select($field)
			->from('u_#__picklist_close_state')
			->innerJoin('vtiger_field', 'u_#__picklist_close_state.fieldid = vtiger_field.fieldid')
			->where(['tabid' => $tabId, 'presence' => [0, 2]])
			->createCommand()->queryAllByGroup($byName ? 2 : 0);
		Cache::save($cacheName, $byName, $values);
		return $values;
	}

	/**
	 * Get process status field names.
	 *
	 * @param string $moduleName optional if we need only one field name for specified module
	 *
	 * @return string|string[]
	 */
	public static function getFieldName(string $moduleName = '')
	{
		if (Cache::has('RecordStatus::getFieldName', $moduleName)) {
			return Cache::get('RecordStatus::getFieldName', $moduleName);
		}
		$query = (new Db\Query())
			->select(['vtiger_field.fieldname', 'vtiger_field.tabid'])
			->from('vtiger_field')
			->where(['LIKE', 'fieldparams', '"isProcessStatusField":true'])
			->andWhere(['presence' => [0, 2]]);
		if ($moduleName) {
			$result = $query->andWhere(['vtiger_field.tabid' => Module::getModuleId($moduleName)])->scalar();
		} else {
			$result = array_column($query->all(), 'fieldname', 'tabid');
		}
		Cache::save('RecordStatus::getFieldName', $moduleName, $result);
		return $result;
	}

	/**
	 * Update expected times.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public static function updateExpectedTimes(\Vtiger_Record_Model $recordModel)
	{
		if ($field = Field::getRelatedFieldForModule($recordModel->getModuleName(), 'ServiceContracts')) {
			foreach (self::getExpectedTimes($recordModel->get($field['fieldname'])) as $key => $time) {
				$recordModel->set($key . '_datatime', $time);
			}
		}
	}

	/**
	 * Get expected times from ServiceContracts.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	private static function getExpectedTimes(int $id): array
	{
		// if (Cache::has('RecordStatus::getFieldName', $moduleName)) {
		// 	return Cache::get('RecordStatus::getFieldName', $moduleName);
		// }
		// TODO   complete function
		// Cache::save('RecordStatus::getFieldName', $moduleName, $result);
		return [
			'response' => '2019-01-01 11:11:11',
			'solution' => '2019-05-05 22:22:22',
			'idle' => '2019-11-11 00:00:00',
		];
	}

	/**
	 * Get the amount of business time between the two dates in minutes based on the service contracts.
	 *
	 * @param string $start
	 * @param string $end
	 * @param int    $id Service contracts id
	 *
	 * @return int
	 */
	public static function getDiffFromServiceContracts(string $start, string $end, int $id): int
	{
		return self::getDiffFromDefaultBusinessHours($start, $end);
	}

	/**
	 * Get the amount of default business time between two dates in minutes.
	 *
	 * @param string $start
	 * @param string $end
	 *
	 * @return int
	 */
	public static function getDiffFromDefaultBusinessHours(string $start, string $end): int
	{
		$businessHours = self::getDefaultBusinessHours();
		if (!$businessHours) {
			return false;
		}
		$time = 0;
		foreach ($businessHours as $row) {
			$days = explode(',', $row['working_days']);
			$time += ($days ? self::businessTime($start, $end, $row['working_hours_from'], $row['working_hours_to'], $days, (bool) $row['holidays']) : 0);
		}
		return $time;
	}

	/**
	 * Get the amount of business time between two dates in minutes.
	 *
	 * @param string $start
	 * @param string $end
	 * @param array  $days
	 * @param string $startHour
	 * @param string $endHour
	 * @param bool   $holidays
	 *
	 * @return int
	 */
	public static function businessTime(string $start, string $end, string $startHour, string $endHour, array $days, bool $holidays): int
	{
		$start = new \DateTime($start);
		$end = new \DateTime($end);
		$holidaysDates = $dates = [];
		$date = clone $start;
		$days = array_flip($days);
		if ($holidays) {
			$holidaysDates = array_flip(array_keys(Fields\Date::getHolidays($start->format('Y-m-d'), $end->format('Y-m-d'))));
		}
		while ($date <= $end) {
			$datesEnd = (clone $date)->setTime(23, 59, 59);
			if (isset($days[$date->format('N')]) || ($holidays && isset($holidaysDates[$date->format('Y-m-d')]))) {
				$dates[] = [
					'start' => clone $date,
					'end' => clone ($end < $datesEnd ? $end : $datesEnd),
				];
			}
			$date->modify('+1 day')->setTime(0, 0, 0);
		}
		[$sh,$sm,$ss] = explode(':', $startHour);
		[$eh,$em,$es] = explode(':', $endHour);
		return array_reduce($dates, function ($carry, $item) use ($sh, $sm, $ss, $eh, $em, $es) {
			$businessStart = (clone $item['start'])->setTime($sh, $sm, $ss);
			$businessEnd = (clone $item['end'])->setTime($eh, $em, $es);
			$start = ($item['start'] < $businessStart) ? $businessStart : $item['start'];
			$end = ($item['end'] > $businessEnd) ? $businessEnd : $item['end'];
			return $carry += max(0, $end->getTimestamp() - $start->getTimestamp());
		}, 0) / 60;
	}

	/**
	 * Get default business hours.
	 *
	 * @return array
	 */
	private static function getDefaultBusinessHours(): array
	{
		if (Cache::has('RecordStatus::getDefaultBusinessHours', '')) {
			return Cache::get('RecordStatus::getDefaultBusinessHours', '');
		}
		$row = (new Db\Query())->from('s_#__business_hours')->where(['default' => 1])->all();
		Cache::save('RecordStatus::getDefaultBusinessHours', '', $row);
		return $row;
	}
}
