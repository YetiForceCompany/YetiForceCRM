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
		return $fieldModel ? true : false;
	}
}
