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
	 * Get record state statuses by module name.
	 *
	 * @param string   $moduleName
	 * @param null|int $state
	 *
	 * @return array if state is specified values are labels, if not values are record_states, key is always primary key
	 */
	public static function getStates(string $moduleName, int $state = null)
	{
		$cacheKey = "RecordStatus::getStates::$moduleName";
		$cacheValue = $state ?? 'empty_state';
		if (Cache::has($cacheKey, $cacheValue)) {
			$values = Cache::get($cacheKey, $cacheValue);
		} else {
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
			Cache::save($cacheKey, $cacheValue, $values);
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
		$tableStatusHistory = $moduleModel->get('basetable') . '_status_history';
		if (!$db->getTableSchema($tableStatusHistory)) {
			$db->createTable($tableStatusHistory, [
				'id' => \yii\db\Schema::TYPE_UPK,
				'crmid' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_INTEGER, 11),
				'after' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_STRING, 255),
				'before' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_STRING, 255),
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
			$dbCommand->addColumn($tableName, 'time_counting', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_STRING, 7))->execute();
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
		static::addFieldsAndBlok($moduleName);
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
		$blockInstance = new \Settings_LayoutEditor_Block_Model();
		$blockInstance->set('label', 'BL_RECORD_STATUS_TIMES');
		$blockInstance->set('iscustom', 1);
		$blockId = $blockInstance->save($moduleModel);
		foreach (static::$stateTimeFields as $type => $fields) {
			foreach ($fields as $name => $label) {
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
		$dbCommand->dropTable($moduleModel->get('basetable') . '_status_history')->execute();
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
				if (in_array($moduleName, $modules)) {
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
	 */
	public static function addHistory(\Vtiger_Record_Model $recordModel)
	{
		$db = \App\Db::getInstance();
		$fieldStatusActive = self::getFieldName($recordModel->getModuleName());
		$nameTableStatusHistory = $recordModel->getModule()->get('basetable') . '_status_history';
		if ($fieldStatusActive && $recordModel->getPreviousValue($fieldStatusActive)) {
			$db->createCommand()->insert($nameTableStatusHistory, [
				'crmid' => $recordModel->getId(),
				'after' => $recordModel->get($fieldStatusActive),
				'before' => $recordModel->getPreviousValue($fieldStatusActive),
				'date' => date('Y-m-d H:i:s')
			])->execute();
		}
	}

	/**
	 * Get time counting values grouped by id from field name.
	 *
	 * @param string $moduleName
	 * @param bool   $asMultiArray time counting could have multiple values separated by comma
	 *                             we can return array of strings with commas or as array of arrays with ints
	 *
	 * @return array
	 */
	public static function getTimeCountingValues(string $moduleName, bool $asMultiArray = true)
	{
		$fieldName = static::getFieldName($moduleName);
		if (!$fieldName) {
			return [];
		}
		$primaryKey = Fields\Picklist::getPickListId($fieldName);
		$values = [];
		foreach (Fields\Picklist::getValues($fieldName) as $row) {
			if (isset($row['time_counting'])) {
				if ($asMultiArray) {
					$values[$row[$primaryKey]] = static::getTimeCountingArrayValueFromString($row['time_counting']);
				} else {
					$values[$row[$primaryKey]] = $row['time_counting'];
				}
			}
		}
		return $values;
	}

	/**
	 * Get time counting value from string.
	 *
	 * @param string $timeCountingStr
	 *
	 * @throws Exceptions\IllegalValue
	 *
	 * @return int[]
	 */
	public static function getTimeCountingArrayValueFromString(string $timeCountingStr): array
	{
		if ('' === $timeCountingStr || ',,' === $timeCountingStr) {
			return [];
		}
		if (-1 === strpos($timeCountingStr, ',')) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $timeCountingStr, 406);
		}
		$values = explode(',', trim($timeCountingStr, ','));
		if (!$values) {
			return [];
		}
		$result = [];
		foreach ($values as $value) {
			if (!is_numeric($value)) {
				throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $value, 406);
			}
			$result[] = (int) $value;
		}
		return $result;
	}

	/**
	 * Get time counting value from array.
	 *
	 * @param int[] $timeCountingArr
	 *
	 * @throws Exceptions\IllegalValue
	 *
	 * @return null|string
	 */
	public static function getTimeCountingStringValueFromArray(array $timeCountingArr)
	{
		foreach ($timeCountingArr as $time) {
			if (!is_int($time)) {
				throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $time, 406);
			}
		}
		return empty($timeCountingArr) ? null : ',' . implode(',', $timeCountingArr) . ',';
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
	 * Get all record states.
	 *
	 * @return string[] [id=>label]
	 */
	public static function getLabels(): array
	{
		return [self::RECORD_STATE_NO_CONCERN => 'LBL_RECORD_STATE_NO_CONCERN', self::RECORD_STATE_OPEN => 'LBL_RECORD_STATE_OPEN', self::RECORD_STATE_CLOSED => 'LBL_RECORD_STATE_CLOSED'];
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
}
