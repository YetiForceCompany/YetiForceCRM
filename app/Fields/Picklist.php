<?php

namespace App\Fields;

/**
 * Picklist class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Picklist
{
	/**
	 * Function to get role based picklist values.
	 *
	 * @param string $fieldName
	 * @param string $roleId
	 *
	 * @return array list of role based picklist values
	 */
	public static function getRoleBasedValues(string $fieldName, string $roleId)
	{
		if (\App\Cache::has('Picklist::getRoleBasedValues', $fieldName)) {
			$allValues = \App\Cache::get('Picklist::getRoleBasedValues', $fieldName);
		} else {
			$allValues = (new \App\Db\Query())->select([$fieldName, 'roleid'])
				->from("vtiger_$fieldName")
				->innerJoin('vtiger_role2picklist', "vtiger_role2picklist.picklistvalueid = vtiger_$fieldName.picklist_valueid")
				->innerJoin('vtiger_picklist', 'vtiger_picklist.picklistid = vtiger_role2picklist.picklistid')
				->orderBy("vtiger_{$fieldName}.sortorderid")
				->all();
			\App\Cache::save('Picklist::getRoleBasedValues', $fieldName, $allValues);
		}
		$fldVal = [];
		foreach ($allValues as $row) {
			if ($row['roleid'] === $roleId) {
				$fldVal[] = \App\Purifier::decodeHtml($row[$fieldName]);
			}
		}

		return $fldVal;
	}

	/**
	 * Function which will give the picklist values for a field.
	 *
	 * @param string $fieldName -- string
	 *
	 * @return array -- array of values
	 */
	public static function getValuesName($fieldName)
	{
		if (\App\Cache::has('Picklist::getValuesName', $fieldName)) {
			return \App\Cache::get('Picklist::getValuesName', $fieldName);
		}
		$primaryKey = static::getPickListId($fieldName);
		$dataReader = (new \App\Db\Query())->select([$primaryKey, $fieldName])
			->from("vtiger_$fieldName")
			->orderBy('sortorderid')
			->createCommand()->query();
		$values = [];
		while ($row = $dataReader->read()) {
			$values[$row[$primaryKey]] = \App\Purifier::decodeHtml(\App\Purifier::decodeHtml($row[$fieldName]));
		}
		\App\Cache::save('Picklist::getValuesName', $fieldName, $values);

		return $values;
	}

	/**
	 * Check if the value exists in the picklist.
	 *
	 * @param string $fieldName
	 * @param string $value
	 *
	 * @return bool
	 */
	public static function isExists(string $fieldName, string $value): bool
	{
		return \in_array($value, static::getValuesName($fieldName));
	}

	/**
	 * Function which will give the editable picklist values for a field.
	 *
	 * @param string $fieldName -- string
	 *
	 * @return array -- array of values
	 */
	public static function getEditableValues($fieldName)
	{
		$values = static::getValuesName($fieldName);
		$nonEditableValues = static::getNonEditableValues($fieldName);
		foreach ($values as $key => &$value) {
			if ('--None--' === $value || isset($nonEditableValues[$key])) {
				unset($values[$key]);
			}
		}
		return $values;
	}

	/**
	 * Function which will give the non editable picklist values for a field.
	 *
	 * @param string $fieldName -- string
	 *
	 * @return array -- array of values
	 */
	public static function getNonEditableValues($fieldName)
	{
		if (\App\Cache::has('Picklist::getNonEditableValues', $fieldName)) {
			return \App\Cache::get('Picklist::getNonEditableValues', $fieldName);
		}
		$primaryKey = static::getPickListId($fieldName);
		$dataReader = (new \App\Db\Query())->select([$primaryKey, $fieldName])
			->from("vtiger_$fieldName")
			->where(['presence' => 0])
			->createCommand()->query();
		$values = [];
		while ($row = $dataReader->read()) {
			$values[$row[$primaryKey]] = \App\Purifier::decodeHtml(\App\Purifier::decodeHtml($row[$fieldName]));
		}
		\App\Cache::save('Picklist::getNonEditableValues', $fieldName, $values);

		return $values;
	}

	/**
	 * Function to get picklist key for a picklist.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public static function getPickListId($fieldName)
	{
		$pickListIds = [
			'opportunity_type' => 'opptypeid',
			'sales_stage' => 'sales_stage_id',
			'rating' => 'rating_id',
			'ticketpriorities' => 'ticketpriorities_id',
			'ticketseverities' => 'ticketseverities_id',
			'ticketstatus' => 'ticketstatus_id',
			'salutationtype' => 'salutationtypeid',
			'faqstatus' => 'faqstatus_id',
			'recurring_frequency' => 'recurring_frequency_id',
			'payment_duration' => 'payment_duration_id',
			'language' => 'id',
			'duration_minutes' => 'minutesid',
		];
		if (isset($pickListIds[$fieldName])) {
			return $pickListIds[$fieldName];
		}
		return $fieldName . 'id';
	}

	/**
	 * Function to get modules which has picklist values.
	 *
	 * @return array
	 */
	public static function getModules()
	{
		return (new \App\Db\Query())->select(['vtiger_tab.tabid', 'vtiger_tab.tablabel', 'tabname' => 'vtiger_tab.name'])->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')->where(['uitype' => [15, 16, 33, 115]])
			->distinct('vtiger_tab.tabid')->orderBy(['vtiger_tab.tabid' => SORT_ASC])->createCommand()->queryAllByGroup(1);
	}

	/**
	 * Get modules with field names which are picklists.
	 *
	 * @param string $moduleName if you want only one module picklist fieldnames
	 *
	 * @return array associative array [$moduleName=>[...$fieldNames]]
	 */
	public static function getModulesByName(string $moduleName = '')
	{
		if (\App\Cache::has('getPicklistModulesByName', $moduleName)) {
			return \App\Cache::get('getPicklistModulesByName', $moduleName);
		}
		$query = (new \App\Db\Query())->select(['vtiger_tab.name', 'vtiger_field.fieldname'])
			->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
			->where(['uitype' => [15, 16, 33, 115]]);
		if ($moduleName) {
			$modules = $query->andWhere(['vtiger_tab.name' => $moduleName])->createCommand()->queryAllByGroup(2);
			$result = $modules[$moduleName] ?? [];
		} else {
			$result = $query->orderBy(['vtiger_tab.tabid' => SORT_ASC])->createCommand()->queryAllByGroup(2);
		}
		\App\Cache::save('getPicklistModulesByName', $moduleName, $result);
		return $result;
	}

	/**
	 * this function returns all the assigned picklist values for the given tablename for the given roleid.
	 *
	 * @param string $tableName - the picklist tablename
	 * @param int    $roleId    - the roleid of the role for which you want data
	 *
	 * @return array $val - the assigned picklist values in array format
	 */
	public static function getAssignedPicklistValues($tableName, $roleId)
	{
		if (\App\Cache::has('getAssignedPicklistValues', $tableName . $roleId)) {
			return \App\Cache::get('getAssignedPicklistValues', $tableName . $roleId);
		}
		$values = [];
		$exists = (new \App\Db\Query())->select(['picklistid'])->from('vtiger_picklist')->where(['name' => $tableName])->exists();
		if ($exists) {
			$roleIds = [$roleId];
			foreach (\App\PrivilegeUtil::getRoleSubordinates($roleId) as $role) {
				$roleIds[] = $role;
			}
			$dataReader = (new \App\Db\Query())->select([$tableName, 'sortid'])->from("vtiger_$tableName")
				->innerJoin('vtiger_role2picklist', "$tableName.picklist_valueid = vtiger_role2picklist.picklistvalueid")
				->where(['roleid' => $roleIds])->orderBy('sortid')->distinct($tableName)->createCommand()->query();
			while ($row = $dataReader->read()) {
				/** Earlier we used to save picklist values by encoding it. Now, we are directly saving those(getRaw()).
				 *  If value in DB is like "test1 &amp; test2" then $abd->fetch_[] is giving it as
				 *  "test1 &amp;$amp; test2" which we should decode two time to get result.
				 */
				$pickVal = \App\Purifier::decodeHtml(\App\Purifier::decodeHtml($row[$tableName]));
				$values[$pickVal] = $pickVal;
			}
			// END
			\App\Cache::save('getAssignedPicklistValues', $tableName . $roleId, $values);

			return $values;
		}
	}

	/**
	 * Picklist dependency fields.
	 *
	 * @var array
	 */
	public static $picklistDependencyFields = [];

	/**
	 * Function to get picklist dependency data source.
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public static function getPicklistDependencyDatasource($module)
	{
		if (\App\Cache::has('getPicklistDependencyDatasource', $module)) {
			static::$picklistDependencyFields[$module] = \App\Cache::get('picklistDependencyFields', $module);
			return \App\Cache::get('getPicklistDependencyDatasource', $module);
		}
		$query = (new \App\Db\Query())->from('vtiger_picklist_dependency')->where(['tabid' => \App\Module::getModuleId($module)]);
		$dataReader = $query->createCommand()->query();
		$picklistDependencyDatasource = [];
		static::$picklistDependencyFields[$module] = [];
		$isEmptyDefaultValue = \App\Config::performance('PICKLIST_DEPENDENCY_DEFAULT_EMPTY');
		while ($row = $dataReader->read()) {
			$pickArray = [];
			$sourceField = $row['sourcefield'];
			$targetField = $row['targetfield'];
			static::$picklistDependencyFields[$module][$sourceField] = true;
			static::$picklistDependencyFields[$module][$targetField] = true;
			$sourceValue = \App\Purifier::decodeHtml($row['sourcevalue']);
			$targetValues = \App\Purifier::decodeHtml($row['targetvalues']);
			$unserializedTargetValues = \App\Json::decode(html_entity_decode($targetValues));
			$criteria = \App\Purifier::decodeHtml($row['criteria']);
			$unserializedCriteria = \App\Json::decode(html_entity_decode($criteria));

			if (!empty($unserializedCriteria) && null !== $unserializedCriteria['fieldname']) {
				$picklistDependencyDatasource[$sourceField][$sourceValue][$targetField][] = [
					'condition' => [$unserializedCriteria['fieldname'] => $unserializedCriteria['fieldvalues']],
					'values' => $unserializedTargetValues,
				];
			} else {
				$picklistDependencyDatasource[$sourceField][$sourceValue][$targetField] = $unserializedTargetValues;
			}
			if (!isset($picklistDependencyDatasource[$sourceField]['__DEFAULT__'][$targetField])) {
				if (!$isEmptyDefaultValue) {
					foreach (self::getValuesName($targetField) as $picklistValue) {
						$pickArray[] = \App\Purifier::decodeHtml($picklistValue);
					}
					$picklistDependencyDatasource[$sourceField]['__DEFAULT__'][$targetField] = $pickArray;
				} else {
					$picklistDependencyDatasource[$sourceField]['__DEFAULT__'][$targetField] = [];
				}
			}
		}
		\App\Cache::save('picklistDependencyFields', $module, static::$picklistDependencyFields[$module]);
		\App\Cache::save('getPicklistDependencyDatasource', $module, $picklistDependencyDatasource);
		return $picklistDependencyDatasource;
	}

	/**
	 * Function which will give the picklist values rows for a field.
	 *
	 * @param string $fieldName -- string
	 *
	 * @return array -- array of values
	 */
	public static function getValues($fieldName)
	{
		if (\App\Cache::has('Picklist::getValues', $fieldName)) {
			return \App\Cache::get('Picklist::getValues', $fieldName);
		}
		$primaryKey = static::getPickListId($fieldName);
		$dataReader = (new \App\Db\Query())
			->from("vtiger_$fieldName")
			->orderBy('sortorderid')
			->createCommand()->query();
		$values = [];
		while ($row = $dataReader->read()) {
			$row['picklistValue'] = \App\Purifier::decodeHtml(\App\Purifier::decodeHtml($row[$fieldName]));
			$row['picklistValueId'] = $row[static::getPickListId($fieldName)];
			$values[$row[$primaryKey]] = $row;
		}
		\App\Cache::save('Picklist::getValues', $fieldName, $values);
		return $values;
	}

	/**
	 * Get colors for all fields or generate it if not exists.
	 *
	 * @param mixed $fieldName
	 *
	 * @return array [$id=>'#FF00FF']
	 */
	public static function getColors($fieldName)
	{
		$colors = [];
		foreach (static::getValues($fieldName) as $id => &$value) {
			$value['color'] = trim($value['color'] ?? '', " #\\s\t\n\r");
			if (empty($value['color'])) {
				$color = \App\Colors::getRandomColor($id);
			} else {
				$color = '#' . $value['color'];
			}
			$colors[$id] = $color;
		}
		return $colors;
	}

	/**
	 * Get picklist table name.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public static function getPickListTableName(string $fieldName)
	{
		if (empty($fieldName) || !preg_match('/^[_a-zA-Z0-9]+$/', $fieldName)) {
			throw new \App\Exceptions\AppException('Incorrect picklist name');
		}
		return 'vtiger_' . $fieldName;
	}

	/**
	 * Clear cache.
	 *
	 * @param string $fieldName
	 * @param string $moduleName
	 */
	public static function clearCache(string $fieldName, string $moduleName)
	{
		\App\Cache::delete('Picklist::getValuesName', $fieldName);
		\App\Cache::delete('Picklist::getNonEditableValues', $fieldName);
		\App\Cache::delete('Picklist::getRoleBasedValues', $fieldName);
		\App\Cache::delete('Picklist::getValues', $fieldName);
		\App\Cache::delete("RecordStatus::getLockStatus::$moduleName", true);
		\App\Cache::delete("RecordStatus::getLockStatus::$moduleName", false);
		$cacheKey = "RecordStatus::getStates::$moduleName";
		\App\Cache::delete($cacheKey, 'empty_state');
		foreach (array_keys(\App\RecordStatus::getLabels()) as $state) {
			\App\Cache::delete($cacheKey, $state);
		}
	}
}
