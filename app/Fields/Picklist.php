<?php
/**
 * Tools file for picklist.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Fields;

/**
 * Tools class for picklist.
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
	 * Check if picklist exist.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public static function isPicklistExist(string $fieldName): bool
	{
		return \App\Db::getInstance()->isTableExists("vtiger_{$fieldName}");
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
			->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')->where(['uitype' => [15, 16, 33, 115], 'vtiger_field.presence' => [0, 2], 'vtiger_tab.presence' => 0])
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
		if (\App\Cache::has('Picklist::getPicklistModulesByName', $moduleName)) {
			return \App\Cache::get('Picklist::getPicklistModulesByName', $moduleName);
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
		\App\Cache::save('Picklist::getPicklistModulesByName', $moduleName, $result);
		return $result;
	}

	/**
	 * Function to get picklist dependency data source.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getDependencyForModule(string $moduleName)
	{
		if (\App\Cache::has('Picklist::getDependencyForModule', $moduleName)) {
			return \App\Cache::get('Picklist::getDependencyForModule', $moduleName);
		}
		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$fields = $moduleModel->getFieldsById();

		$conditionsByFields = $listenFields = [];
		$dataReader = (new \App\Db\Query())->from(['s_#__picklist_dependency'])->where(['tabid' => $moduleModel->getId()])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$sourceFieldName = $fields[$row['source_field']]->getName();
			$picklistTable = "vtiger_$sourceFieldName";
			$primaryKey = static::getPickListId($sourceFieldName);
			$dataReaderPDValue = (new \App\Db\Query())
				->select(['value' => "{$picklistTable}.{$sourceFieldName}", 's_#__picklist_dependency_data.conditions'])
				->from([$picklistTable])
				->leftJoin('s_#__picklist_dependency_data', "$picklistTable.$primaryKey = s_#__picklist_dependency_data.source_id AND s_#__picklist_dependency_data.id = :pdd", [':pdd' => $row['id']])
				->createCommand()->query();
			while ($plRow = $dataReaderPDValue->read()) {
				$conditions = [];
				if ($plRow['conditions']) {
					$conditions = \App\Json::decode($plRow['conditions']);
					$listenFields = array_merge($listenFields, \App\Condition::getFieldsFromConditions($conditions)['baseModule']);
				}
				$conditionsByFields[$sourceFieldName][$plRow['value']] = $conditions;
			}
		}
		$result = ['listener' => array_unique($listenFields), 'conditions' => $conditionsByFields];
		\App\Cache::save('Picklist::getDependencyForModule', $moduleName, $result);
		return $result;
	}

	/**
	 * Check if field is dependent.
	 *
	 * @param string $moduleName
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public static function isDependentField(string $moduleName, string $fieldName): bool
	{
		['listener' => $listener, 'conditions' => $conditions] = self::getDependencyForModule($moduleName);
		return \in_array($fieldName, $listener) || isset($conditions[$fieldName]);
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
	 * Get icon data for picklist value.
	 *
	 * @param string $fieldName
	 * @param string $value
	 *
	 * @return array
	 */
	public static function getIcon(string $fieldName, string $value): array
	{
		$icon = [];
		if (self::isPicklistExist($fieldName) && ($iconValue = array_column(self::getValues($fieldName), 'icon', $fieldName)[$value] ?? [])) {
			$icon = \App\Json::isJson($iconValue) ? \App\Json::decode($iconValue) : ['type' => 'icon', 'name' => $iconValue];
		}

		return $icon;
	}

	/**
	 * Get colors for all fields or generate it if not exists.
	 *
	 * @param mixed $fieldName
	 * @param bool  $numericKey
	 *
	 * @return array [$id=>'#FF00FF']
	 */
	public static function getColors($fieldName, bool $numericKey = true)
	{
		$colors = [];
		foreach (static::getValues($fieldName) as $id => &$value) {
			$value['color'] = trim($value['color'] ?? '', " #\\s\t\n\r");
			if (empty($value['color'])) {
				$color = \App\Colors::getRandomColor($id);
			} else {
				$color = '#' . $value['color'];
			}
			$colors[$numericKey ? $id : $value[$fieldName]] = $color;
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
	 * Check if the prefix exists in given picklist name.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public static function prefixExist(string $fieldName): bool
	{
		return !empty(array_filter(array_column(static::getValues($fieldName), 'prefix')));
	}

	/**
	 * Get picklist ID number.
	 *
	 * @param string $fieldName
	 *
	 * @return int
	 */
	public static function getPicklistIdNr(string $fieldName): int
	{
		return (int) (new \App\Db\Query())->select(['picklistid'])
			->from('vtiger_picklist')
			->where(['name' => $fieldName])
			->scalar();
	}

	/**
	 * Remove dependency condition field.
	 *
	 * @param string $moduleName
	 * @param string $fieldName
	 *
	 * @return void
	 */
	public static function removeDependencyConditionField(string $moduleName, string $fieldName): void
	{
		$tabId = \App\Module::getModuleId($moduleName);
		$fullFieldName = "{$fieldName}:{$moduleName}";
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = (new \App\Db\Query())->select(['s_#__picklist_dependency_data.*'])->from('s_#__picklist_dependency')->innerJoin('s_#__picklist_dependency_data', 's_#__picklist_dependency_data.id = s_#__picklist_dependency.id')
			->where(['tabid' => $tabId])->andWhere(['like', 'conditions', $fullFieldName])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$conditions = \App\Json::decode($row['conditions']);
			$conditions = \App\Condition::removeFieldFromCondition($moduleName, $conditions, $moduleName, $fieldName);
			if ($conditions) {
				$dbCommand->update('s_#__picklist_dependency_data', ['conditions' => \App\Json::encode($conditions)], ['id' => $row['id'], 'source_id' => $row['source_id']])->execute();
			} else {
				$dbCommand->delete('s_#__picklist_dependency_data', ['id' => $row['id'], 'source_id' => $row['source_id']])->execute();
			}
		}
		$dataReader->close();
	}

	/**
	 * Clear cache.
	 *
	 * @param string $fieldName
	 * @param string $moduleName
	 */
	public static function clearCache(string $fieldName, string $moduleName)
	{
		\App\Cache::delete('Picklist::getDependencyForModule', $moduleName);
		\App\Cache::delete('Picklist::getValuesName', $fieldName);
		\App\Cache::delete('Picklist::getNonEditableValues', $fieldName);
		\App\Cache::delete('Picklist::getRoleBasedValues', $fieldName);
		\App\Cache::delete('Picklist::getValues', $fieldName);
		\App\Cache::delete("RecordStatus::getLockStatus::$moduleName", true);
		\App\Cache::delete("RecordStatus::getLockStatus::$moduleName", false);
	}
}
