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
		if ((!in_array($this->get('displaytype'), [1, 10]) && 'salutationtype' !== $this->getName()) || !in_array($this->get('presence'), [0, 2]) || in_array($this->getName(), $nonEditablePickListValues) || ('picklist' !== $this->getFieldDataType() && 'multipicklist' !== $this->getFieldDataType()) || 'Users' === $this->getModuleName()) {
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
		if ('INTERSECTION' == $groupMode) {
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
	 * Update record status.
	 *
	 * @param int   $id
	 * @param int   $recordState
	 * @param int[] $timeCounting
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public function updateRecordStatus(int $id, int $recordState, array $timeCounting): bool
	{
		if (!$this->isProcessStatusField()) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_IS_NOT_A_PROCESS_STATUS_FIELD', 'Settings:Picklist'), 406);
		}
		if (!$this->isEditable()) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_NON_EDITABLE_PICKLIST_VALUE', 'Settings:Picklist'), 406);
		}
		$pickListFieldName = $this->getName();
		$primaryKey = \App\Fields\Picklist::getPickListId($pickListFieldName);
		$tableName = \App\Fields\Picklist::getPickListTableName($pickListFieldName);
		$tabId = $this->get('tabid');
		$moduleName = \App\Module::getModuleName($tabId);
		foreach ($timeCounting as $time) {
			if (!is_int($time)) {
				throw new \App\Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE||' . $time, 406);
			}
		}
		$newTimeCountingValue = \App\RecordStatus::getTimeCountingStringValueFromArray($timeCounting);
		$oldTimeCountingValue = \App\RecordStatus::getTimeCountingValues($moduleName, false)[$id] ?? '';
		$oldStateValue = \App\RecordStatus::getStates($moduleName)[$id];
		if ($recordState === $oldStateValue && $newTimeCountingValue === $oldTimeCountingValue) {
			return true;
		}
		$result = \App\Db::getInstance()->createCommand()->update($tableName, ['record_state' => $recordState, 'time_counting' => $newTimeCountingValue], [$primaryKey => $id])->execute();
		if ($result) {
			\App\Fields\Picklist::clearCache($pickListFieldName, $moduleName);
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
		$oldValue = \App\RecordStatus::getLockStatus($moduleName, false)[$valueId] ?? false;
		if ($closeState === $oldValue) {
			return true;
		}
		if (null === $closeState && $oldValue !== $value) {
			$dbCommand->update('u_#__picklist_close_state', ['value' => $value], ['fieldid' => $this->getId(), 'valueid' => $valueId])->execute();
		} elseif (false === $closeState && false !== $oldValue) {
			$dbCommand->delete('u_#__picklist_close_state', ['fieldid' => $this->getId(), 'valueid' => $valueId])->execute();
		} elseif ($closeState && false === $oldValue) {
			$dbCommand->insert('u_#__picklist_close_state', ['fieldid' => $this->getId(), 'valueid' => $valueId, 'value' => $value])->execute();
		}
		\App\Cache::delete('getLockStatus', $tabId);
		return true;
	}
}
