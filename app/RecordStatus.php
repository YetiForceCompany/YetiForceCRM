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
	 * Get record state status by module id.
	 *
	 * @param int $tabId
	 *
	 * @return string[]
	 */
	public static function getStatusStatesByModuleId(int $tabId, string $state = 'open')
	{
		if (\App\Cache::has('RecordStatus::getStates', $tabId)) {
			$values = \App\Cache::get('RecordStatus::getStates', $tabId);
		} else {
			$fieldName = static::getField($tabId);
			$values = [];
			foreach (Fields\Picklist::getValues($fieldName) as $value) {
				if ($value['automation']) {
					$values[$value['automation']][$value['ticketstatus_id']] = $value['picklistValue'];
				}
			}
			\App\Cache::save('RecordStatus::getStates', $tabId, $values);
		}
		return $values['open' === $state ? 1 : 2] ?? [];
	}

	/**
	 * Get record status field name.
	 *
	 * @param int $tabId
	 *
	 * @return bool|string
	 */
	public static function getField(int $tabId)
	{
		if (\App\Cache::has('RecordStatus::getField', $tabId)) {
			return \App\Cache::get('RecordStatus::getField', $tabId);
		}
		$fieldName = (new \App\Db\Query())->select(['fieldname'])->from('vtiger_field')
			->where(['tabid' => $tabId, 'presence' => [0, 2], 'fieldparams' => '{"isProcessStatusField":true}'])
			->scalar();
		\App\Cache::save('RecordStatus::getField', $tabId, $fieldName);
		return $fieldName;
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
	 * @param string $fieldName
	 * @param bool   $asMultiArray time counting could have multiple values separated by comma
	 *                             we can return array of strings with commas or as array of arrays
	 *
	 * @return array
	 */
	public static function getTimeCountingValues(string $fieldName, bool $asMultiArray = true)
	{
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
	 * Get record state values grouped by id.
	 *
	 * @param string $fieldName
	 *
	 * @return array
	 */
	public static function getRecordStateValues(string $fieldName)
	{
		$tableName = \App\Fields\Picklist::getPickListTableName($fieldName);
		$primaryKey = \App\Fields\Picklist::getPickListId($fieldName);
		$rows = (new \App\Db\Query())->select([$primaryKey, 'record_state'])->from($tableName)->all();
		if ($rows) {
			return array_column($rows, 'record_state', $primaryKey);
		}
		return [];
	}

	/**
	 * Get closing state for all fields in module.
	 *
	 * @param int $tabId
	 *
	 * @return string[]
	 */
	public static function getCloseStates(int $tabId, bool $byName = true)
	{
		$cacheName = 'getCloseStates' . ($byName ? 'ByName' : '');
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
	public static function getRecordStates(): array
	{
		return [self::RECORD_STATE_NO_CONCERN => 'LBL_RECORD_STATE_NO_CONCERN', self::RECORD_STATE_OPEN => 'LBL_RECORD_STATE_OPEN', self::RECORD_STATE_CLOSED => 'LBL_RECORD_STATE_CLOSED'];
	}

	/**
	 *  Get picklist values by record state value.
	 *
	 * @param string $fieldName
	 * @param int    $recordState
	 *
	 * @return array
	 */
	public static function getPicklistValuesByRecordState(string $fieldName, int $recordState = self::RECORD_STATE_NO_CONCERN): array
	{
		$cacheName = "getPicklistValuesByRecordState$fieldName";
		if (\App\Cache::staticHas($cacheName, $recordState)) {
			return \App\Cache::staticGet($cacheName, $recordState);
		}
		if ((bool) \App\Db::getInstance()->getTableSchema("vtiger_$fieldName", true)->getColumn('record_state')) {
			$values = (new \App\Db\Query())->select([$fieldName])->from("vtiger_$fieldName")->where(['record_state' => $recordState])
				->column();
		} else {
			$values = [];
		}
		\App\Cache::staticSave($cacheName, $recordState, $values);
		return $values;
	}
}
