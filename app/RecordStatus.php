<?php
/**
 * Record status service file.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Record status service class.
 */
class RecordStatus
{
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
		return $values['open' === $state ? 1 : 2];
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
		$db = \App\Db::getInstance();
		$schema = $db->getSchema();
		$field = (new \App\Db\Query())
			->from('vtiger_field')
			->where(['tabid' => Module::getModuleId($moduleName), 'fieldname' => $fieldName])
			->one();
		if (!$field) {
			return false;
		}
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		if ($fieldModel = \Vtiger_Field_Model::getInstance($fieldName, $moduleModel)) {
			$fieldModel->set('fieldparams', \App\Json::encode(['isProcessStatusField' => true]));
			$fieldModel->save();
			$nameTableStatusHistory = $moduleModel->get('basetable') . '_record_status_history';
			if (!$db->getTableSchema($nameTableStatusHistory)) {
				$db->createTable($nameTableStatusHistory, [
					'id' => \yii\db\Schema::TYPE_UPK,
					'crmid' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_INTEGER, 11),
					'after' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_STRING, 255),
					'before' => $schema->createColumnSchemaBuilder(\yii\db\Schema::TYPE_STRING, 255),
					'data' => \yii\db\Schema::TYPE_TIMESTAMP
				]);
			}
		}
		return $fieldModel ? true : false;
	}

	/**
	 * Add date history status to table.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function addHistory(\Vtiger_Record_Model $recordModel)
	{
		$db = \App\Db::getInstance();
		$fieldStatusActive = self::getField(\App\Module::getModuleId($recordModel->getModuleName()));
		$nameTableStatusHistory = $recordModel->getModule()->get('basetable') . '_record_status_history';
		if ($db->getTableSchema($nameTableStatusHistory) && $fieldStatusActive) {
			$db->createCommand()->insert($nameTableStatusHistory, [
				'crmid' => $recordModel->getId(),
				'after' => $recordModel->get($fieldStatusActive),
				'before' => $recordModel->getPreviousValue($fieldStatusActive),
				'data' => date('Y-m-d H:i:s')
			])->execute();
		}
	}
}
