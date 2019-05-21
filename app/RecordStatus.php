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
		$tableName = \Settings_Picklist_Module_Model::getPicklistTableName($fieldName);
		static::addColumn($tableName, 'record_state');
		static::addColumn($tableName, 'time_counting');
		return $fieldModel ? true : false;
	}

	/**
	 * Add record state column to picklist.
	 *
	 * @param string $tableName
	 * @param string $columnName
	 *
	 * @return bool
	 */
	public static function addColumn(string $tableName, string $columnName)
	{
		$db = Db::getInstance();
		$db->createCommand()->addColumn($tableName, $columnName, $db->getSchema()->createColumnSchemaBuilder(\yii\db\Schema::TYPE_TINYINT, 1))->execute();
	}

	/**
	 * Get time counting value from field model.
	 *
	 * @param \Settings_Picklist_Field_Model $fieldModel
	 * @param int                            $id
	 * @param bool                           $asArray
	 *
	 * @return null|int[]|string
	 */
	public static function getTimeCountingValue(\Settings_Picklist_Field_Model $fieldModel, int $id, bool $asArray = true)
	{
		if (!$fieldModel->isProcessStatusField()) {
			return null;
		}
		$pickListFieldName = $fieldModel->getName();
		$primaryKey = \App\Fields\Picklist::getPickListId($pickListFieldName);
		$tableName = \Settings_Picklist_Module_Model::getPickListTableName($pickListFieldName);
		$value = (new \App\Db\Query())->select('time_counting')->from($tableName)->where([$primaryKey => $id])->scalar();
		if (!$asArray) {
			return $value;
		}
		return static::getTimeCountingArrayValueFromString($value);
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
	 * Get record state value.
	 *
	 * @param \Settings_Picklist_Field_Model $fieldModel
	 * @param int                            $id
	 *
	 * @return null|int
	 */
	public static function getRecordStateValue(\Settings_Picklist_Field_Model $fieldModel, int $id)
	{
		if (!$fieldModel->isProcessStatusField()) {
			return null;
		}
		$pickListFieldName = $fieldModel->getName();
		$primaryKey = \App\Fields\Picklist::getPickListId($pickListFieldName);
		$tableName = \Settings_Picklist_Module_Model::getPickListTableName($pickListFieldName);
		return (new \App\Db\Query())->select('record_state')->from($tableName)->where([$primaryKey => $id])->scalar();
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
	 * Update record state value.
	 *
	 * @param Settings_Picklist_Field_Model $fieldModel
	 * @param int                           $id
	 * @param int                           $recordState
	 *
	 * @return bool
	 */
	public static function updateRecordStateValue(\Settings_Picklist_Field_Model $fieldModel, int $id, int $recordState)
	{
		if (!$fieldModel->isProcessStatusField()) {
			return false;
		}
		$pickListFieldName = $fieldModel->getName();
		$primaryKey = \App\Fields\Picklist::getPickListId($pickListFieldName);
		$tableName = \Settings_Picklist_Module_Model::getPickListTableName($pickListFieldName);
		$oldValue = static::getRecordStateValue($fieldModel, $id);
		if ($recordState === $oldValue) {
			return true;
		}
		if (!$fieldModel->isEditable()) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_NON_EDITABLE_PICKLIST_VALUE', 'Settings:Picklist'), 406);
		}
		$result = Db::getInstance()->createCommand()->update($tableName, ['record_state' => $recordState], [$primaryKey => $id])->execute();
		if ($result) {
			\Settings_Picklist_Module_Model::clearPicklistCache($pickListFieldName, $fieldModel->getModuleName());
			$eventHandler = new EventHandler();
			$eventHandler->setParams([
				'fieldname' => $pickListFieldName,
				'oldvalue' => $oldValue,
				'newvalue' => $recordState,
				'module' => $fieldModel->getModuleName(),
				'id' => $id,
			]);
			$eventHandler->trigger('PicklistAfterRecordStateUpdate');
			return true;
		}
		return false;
	}

	/**
	 * Update close state table.
	 *
	 * @param \Settings_Picklist_Field_Model $fieldModel
	 * @param int                            $valueId
	 * @param string                         $value
	 * @param null|bool                      $closeState
	 *
	 * @throws \yii\db\Exception
	 */
	public static function updateCloseState(\Settings_Picklist_Field_Model $fieldModel, int $valueId, string $value, $closeState = null)
	{
		$dbCommand = Db::getInstance()->createCommand();
		$oldValue = static::getCloseStates($fieldModel->get('tabid'), false)[$valueId] ?? false;
		if ($closeState === null && $oldValue !== $value) {
			$dbCommand->update('u_#__picklist_close_state', ['value' => $value], ['fieldid' => $fieldModel->getId(), 'valueid' => $valueId])->execute();
		} elseif ($closeState === false && $oldValue !== false) {
			$dbCommand->delete('u_#__picklist_close_state', ['fieldid' => $fieldModel->getId(), 'valueid' => $valueId])->execute();
		} elseif ($closeState && $oldValue === false) {
			$dbCommand->insert('u_#__picklist_close_state', ['fieldid' => $fieldModel->getId(), 'valueid' => $valueId, 'value' => $value])->execute();
		}
		\App\Cache::staticDelete('getCloseStatesByName', $fieldModel->get('tabid'));
		\App\Cache::staticDelete('getCloseStates', $fieldModel->get('tabid'));
		return true;
	}

	/**
	 * Update time counting value.
	 *
	 * @param \Settings_Picklist_Field_Model $fieldModel
	 * @param int                            $id
	 * @param int[]                          $timeCounting
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return bool
	 */
	public static function updateTimeCountingValue(\Settings_Picklist_Field_Model $fieldModel, int $id, array $timeCounting): bool
	{
		foreach ($timeCounting as $time) {
			if (!is_int($time)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $time, 406);
			}
		}
		$pickListFieldName = $fieldModel->getName();
		$primaryKey = \App\Fields\Picklist::getPickListId($pickListFieldName);
		$tableName = \Settings_Picklist_Module_Model::getPickListTableName($pickListFieldName);
		$newValue = static::getTimeCountingStringValueFromArray($timeCounting);
		if ($newValue === ',,') {
			$newValue = null;
		}
		$oldValue = static::getTimeCountingValue($fieldModel, $id, false);
		if ($newValue === $oldValue) {
			return true;
		}
		$result = Db::getInstance()->createCommand()->update($tableName, ['time_counting' => $newValue], [$primaryKey => $id])->execute();
		if ($result) {
			\Settings_Picklist_Module_Model::clearPicklistCache($pickListFieldName, $fieldModel->getModuleName());
			$eventHandler = new EventHandler();
			$eventHandler->setParams([
				'fieldname' => $pickListFieldName,
				'oldvalue' => $oldValue,
				'newvalue' => $timeCounting,
				'module' => $fieldModel->getModuleName(),
				'id' => $id,
			]);
			$eventHandler->trigger('PicklistAfterTimeCountingUpdate');
			return true;
		}
		return false;
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
