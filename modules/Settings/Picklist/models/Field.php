<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce Sp. z o.o.
 * ********************************************************************************** */

class Settings_Picklist_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function to check whether the current field is editable.
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		$nonEditablePickListValues = ['duration_minutes', 'payment_duration', 'recurring_frequency', 'visibility'];
		if ((!in_array($this->get('displaytype'), [1, 10]) && $this->getName() !== 'salutationtype') || !in_array($this->get('presence'), [0, 2]) || in_array($this->getName(), $nonEditablePickListValues) || ($this->getFieldDataType() !== 'picklist' && $this->getFieldDataType() !== 'multipicklist') || $this->getModuleName() === 'Users') {
			return false;
		}
		return true;
	}

	/**
	 * Function which will give the picklistvalues for given roleids.
	 *
	 * @param type $roleIdList -- array of role ids
	 * @param type $groupMode  -- Intersection/Conjuction , intersection will give only picklist values that exist for all roles
	 *
	 * @return type -- array
	 */
	public function getPicklistValuesForRole($roleIdList, $groupMode = 'INTERSECTION')
	{
		if (!$this->isRoleBased()) {
			$fieldModel = new Vtiger_Field_Model();

			return $fieldModel->getPicklistValues();
		}
		$intersectionMode = false;
		if ($groupMode == 'INTERSECTION') {
			$intersectionMode = true;
		}
		$fieldName = $this->getName();
		$tableName = 'vtiger_' . $fieldName;
		$query = (new App\Db\Query())->select(["{$tableName}.{$fieldName}"]);
		if ($intersectionMode) {
			$query->addSelect(['rolecount' => new yii\db\Expression('COUNT(roleid)')]);
		}
		$query->from('vtiger_role2picklist')
			->innerJoin($tableName, "vtiger_role2picklist.picklistvalueid = {$tableName}.picklist_valueid")
			->where(['vtiger_role2picklist.roleid' => $roleIdList])->orderBy(['vtiger_role2picklist.sortid' => SORT_ASC]);
		if ($intersectionMode) {
			$query->groupBy(['picklistvalueid']);
		}
		$dataReader = $query->createCommand()->query();
		$pickListValues = [];
		while ($row = $dataReader->read()) {
			//second not equal if specify that the picklistvalue is not present for all the roles
			if ($intersectionMode && (int) $row['rolecount'] !== count($roleIdList)) {
				continue;
			}
			//Need to decode the picklist values twice which are saved from old ui
			$pickListValues[] = \App\Purifier::decodeHtml(\App\Purifier::decodeHtml($row[$fieldName]));
		}
		$dataReader->close();

		return $pickListValues;
	}

	/**
	 * Function to get instance.
	 *
	 * @param string $value  - fieldname or fieldid
	 * @param <type> $module - optional - module instance
	 *
	 * @return <Vtiger_Field_Model>
	 */
	public static function getInstance($value, $module = false)
	{
		$fieldObject = parent::getInstance($value, $module);
		if ($fieldObject) {
			return self::getInstanceFromFieldObject($fieldObject);
		}
		return false;
	}

	/**
	 * Static Function to get the instance fo Vtiger Field Model from a given vtlib\Field object.
	 *
	 * @param vtlib\Field $fieldObj - vtlib field object
	 *
	 * @return Vtiger_Field_Model instance
	 */
	public static function getInstanceFromFieldObject(vtlib\Field $fieldObj)
	{
		$objectProperties = get_object_vars($fieldObj);
		$fieldModel = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$fieldModel->{$properName} = $propertyValue;
		}
		return $fieldModel;
	}

	/**
	 * Verification of data.
	 *
	 * @param string $value
	 * @param int    $id
	 *
	 * @throws Exception
	 */
	public function validate($value, $id = false)
	{
		if (preg_match('/[\<\>\"\#\,]/', $value)) {
			throw new \App\Exceptions\AppException(\App\Language::translateArgs('ERR_SPECIAL_CHARACTERS_NOT_ALLOWED', 'Other.Exceptions', '<>"#,'), 512);
		}
		if ($this->get('maximumlength') && strlen($value) > $this->get('maximumlength')) {
			throw new \App\Exceptions\AppException(\App\Language::translate('ERR_EXCEEDED_NUMBER_CHARACTERS', 'Other.Exceptions'), 512);
		}
		$picklistValues = \App\Fields\Picklist::getValuesName($this->getName());
		if ($id) {
			unset($picklistValues[$id]);
		}
		if (in_array(strtolower($value), array_map('strtolower', $picklistValues))) {
			throw new \App\Exceptions\AppException(\App\Language::translateArgs('ERR_DUPLICATES_VALUES_FOUND', 'Other.Exceptions', $value), 513);
		}
	}

	/**
	 * Is process status field.
	 *
	 * @return bool
	 */
	public function isProcessStatusField(): bool
	{
		return $this->getFieldParams()['isProcessStatusField'] ?? false;
	}

	/**
	 * Update record state value.
	 *
	 * @param int $id
	 * @param int $recordState
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public function updateRecordStateValue(int $id, int $recordState): bool
	{
		if (!$this->isProcessStatusField()) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_IS_NOT_A_PROCESS_STATUS_FIELD', 'Settings:Picklist'), 406);
		}
		$pickListFieldName = $this->getName();
		$primaryKey = \App\Fields\Picklist::getPickListId($pickListFieldName);
		$tableName = \App\Fields\Picklist::getPickListTableName($pickListFieldName);
		$oldValue = \App\RecordStatus::getRecordStateValues($this->getModuleName())[$id];
		if ($recordState === $oldValue) {
			return true;
		}
		if (!$this->isEditable()) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_NON_EDITABLE_PICKLIST_VALUE', 'Settings:Picklist'), 406);
		}
		$result = \App\Db::getInstance()->createCommand()->update($tableName, ['record_state' => $recordState], [$primaryKey => $id])->execute();
		if ($result) {
			\Settings_Picklist_Module_Model::clearPicklistCache($pickListFieldName, $this->getModuleName());
			$eventHandler = new \App\EventHandler();
			$eventHandler->setParams([
				'fieldname' => $pickListFieldName,
				'oldvalue' => $oldValue,
				'newvalue' => $recordState,
				'module' => $this->getModuleName(),
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
	 * @param int       $valueId
	 * @param string    $value
	 * @param null|bool $closeState
	 *
	 * @throws \yii\db\Exception
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public function updateCloseState(int $valueId, string $value, $closeState = null): bool
	{
		if (!$this->isProcessStatusField()) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_IS_NOT_A_PROCESS_STATUS_FIELD', 'Settings:Picklist'), 406);
		}
		$dbCommand = \App\Db::getInstance()->createCommand();
		$tabId = $this->get('tabid');
		$moduleName = \App\Module::getModuleName($tabId);
		$oldValue = \App\RecordStatus::getClosingStates($moduleName, false)[$valueId] ?? false;
		if ($closeState === $oldValue) {
			return true;
		}
		if ($closeState === null && $oldValue !== $value) {
			$dbCommand->update('u_#__picklist_close_state', ['value' => $value], ['fieldid' => $this->getId(), 'valueid' => $valueId])->execute();
		} elseif ($closeState === false && $oldValue !== false) {
			$dbCommand->delete('u_#__picklist_close_state', ['fieldid' => $this->getId(), 'valueid' => $valueId])->execute();
		} elseif ($closeState && $oldValue === false) {
			$dbCommand->insert('u_#__picklist_close_state', ['fieldid' => $this->getId(), 'valueid' => $valueId, 'value' => $value])->execute();
		}
		\App\Cache::staticDelete('getCloseStatesByName', $tabId);
		\App\Cache::staticDelete('getCloseStates', $tabId);
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
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public function updateTimeCountingValue(int $id, array $timeCounting): bool
	{
		if (!$this->isProcessStatusField()) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_IS_NOT_A_PROCESS_STATUS_FIELD', 'Settings:Picklist'), 406);
		}
		foreach ($timeCounting as $time) {
			if (!is_int($time)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $time, 406);
			}
		}
		$pickListFieldName = $this->getName();
		$moduleName = $this->getModuleName();
		$primaryKey = \App\Fields\Picklist::getPickListId($pickListFieldName);
		$tableName = \App\Fields\Picklist::getPickListTableName($pickListFieldName);
		$newValue = \App\RecordStatus::getTimeCountingStringValueFromArray($timeCounting);
		if ($newValue === ',,') {
			$newValue = null;
		}
		$oldValue = \App\RecordStatus::getTimeCountingValues($moduleName, false)[$id];
		if ($newValue === $oldValue) {
			return true;
		}
		$result = \App\Db::getInstance()->createCommand()->update($tableName, ['time_counting' => $newValue], [$primaryKey => $id])->execute();
		if ($result) {
			\Settings_Picklist_Module_Model::clearPicklistCache($pickListFieldName, $moduleName);
			$eventHandler = new \App\EventHandler();
			$eventHandler->setParams([
				'fieldname' => $pickListFieldName,
				'oldvalue' => $oldValue,
				'newvalue' => $newValue,
				'module' => $moduleName,
				'id' => $id,
			]);
			$eventHandler->trigger('PicklistAfterTimeCountingUpdate');
			return true;
		}
		return false;
	}
}
