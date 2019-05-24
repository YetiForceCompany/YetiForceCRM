<?php
/**
 * Record status service file.
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
		if (\App\Cache::has($cacheKey, $state)) {
			$values = \App\Cache::get($cacheKey, $state);
		} else {
			$fieldName = static::getFieldName($moduleName);
			$primaryKey = \App\Fields\Picklist::getPickListId($fieldName);
			$values = [];
			foreach (Fields\Picklist::getValues($fieldName) as $value) {
				if (isset($value['record_state']) && $state === $value['record_state']) {
					$values[$value[$primaryKey]] = $value['picklistValue'];
				} elseif ($state === null) {
					$values[$value[$primaryKey]] = $value['record_state'];
				}
			}
			\App\Cache::save($cacheKey, $state, $values);
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
		$field = (new \App\Db\Query())
			->from('vtiger_field')
			->where(['tabid' => Module::getModuleId($moduleName), 'fieldname' => $fieldName])
			->one();
		if (!$field) {
			return false;
		}
		if ($fieldModel = \Vtiger_Field_Model::getInstance($fieldName, \Vtiger_Module_Model::getInstance($moduleName))) {
			$fieldModel->set('fieldparams', \App\Json::encode(['isProcessStatusField' => true]));
			$fieldModel->save();
		}
		$tableName = \App\Fields\Picklist::getPicklistTableName($fieldName);
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$db->createCommand()->addColumn($tableName, 'record_state', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1));
		$db->createCommand()->addColumn($tableName, 'time_counting', $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_STRING, 7));
		return $fieldModel ? true : false;
	}

	/**
	 * Get time counting values grouped by id from field name.
	 *
	 * @param string $moduleName
	 * @param bool   $asMultiArray time counting could have multiple values separated by comma
	 *                             we can return array of strings with commas or as array of arrays
	 *
	 * @return array
	 */
	public static function getTimeCountingValues(string $moduleName, bool $asMultiArray = true)
	{
		$fieldName = static::getFieldName($moduleName);
		if (!$fieldName) {
			return [];
		}
		$primaryKey = \App\Fields\Picklist::getPickListId($fieldName);
		$tableName = \App\Fields\Picklist::getPickListTableName($fieldName);
		$rows = (new \App\Db\Query())->select([$primaryKey, 'time_counting'])->from($tableName)->all();
		$values = [];
		foreach ($rows as $row) {
			if ($asMultiArray) {
				$values[$row[$primaryKey]] = static::getTimeCountingArrayValueFromString($row['time_counting']);
			} else {
				$values[$row[$primaryKey]] = $row['time_counting'];
			}
		}
		return $values;
	}

	/**
	 * Get time counting value from string.
	 *
	 * @param string $timeCountingStr
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return int[]
	 */
	public static function getTimeCountingArrayValueFromString(string $timeCountingStr): array
	{
		if ($timeCountingStr === '' || $timeCountingStr === ',,') {
			return [];
		}
		if (strpos($timeCountingStr, ',') === -1) {
			throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $timeCountingStr, 406);
		}
		$values = explode(',', trim($timeCountingStr, ','));
		if (!$values) {
			return [];
		}
		$result = [];
		foreach ($values as $value) {
			if (!is_numeric($value)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $value, 406);
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
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return string
	 */
	public static function getTimeCountingStringValueFromArray(array $timeCountingArr): string
	{
		foreach ($timeCountingArr as $time) {
			if (!is_int($time)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $time, 406);
			}
		}
		return ',' . implode(',', $timeCountingArr) . ',';
	}

	/**
	 * Get closing states for all fields in module.
	 *
	 * @param string $moduleName
	 * @param bool   $byName
	 *
	 * @return string[]
	 */
	public static function getCloseStates(string $moduleName, bool $byName = true)
	{
		$tabId = \App\Module::getModuleId($moduleName);
		$cacheName = 'RecordStatus::getCloseStates' . ($byName ? 'ByName' : '');
		if (\App\Cache::staticHas($cacheName, $tabId)) {
			return \App\Cache::staticGet($cacheName, $tabId);
		}
		$field = $byName ? ['vtiger_field.fieldname', 'value'] : ['valueid', 'value'];
		$values = (new \App\Db\Query())->select($field)
			->from('u_#__picklist_close_state')
			->innerJoin('vtiger_field', 'u_#__picklist_close_state.fieldid = vtiger_field.fieldid')
			->where(['tabid' => $tabId, 'presence' => [0, 2]])
			->createCommand()->queryAllByGroup($byName ? 2 : 0);
		\App\Cache::staticSave($cacheName, $tabId, $values);
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
		$cacheKey = 'RecordStatus::getFieldName';
		if (\App\Cache::has($cacheKey, $moduleName)) {
			return \App\Cache::get($cacheKey, $moduleName);
		}
		$query = (new \App\Db\Query())
			->select(['vtiger_field.fieldname', 'vtiger_field.tabid'])
			->from('vtiger_field')
			->where(['fieldparams' => '{"isProcessStatusField":true}', 'presence' => [0, 2]]);
		if ($moduleName) {
			$result = $query->andWhere(['vtiger_field.tabid' => \App\Module::getModuleId($moduleName)])->scalar();
		} else {
			$result = array_column($query->all(), 'fieldname', 'tabid');
		}
		\App\Cache::save($cacheKey, $moduleName, $result);
		return $result;
	}
}
