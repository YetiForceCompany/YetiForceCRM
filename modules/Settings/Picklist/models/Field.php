<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_Picklist_Field_Model extends Settings_Vtiger_Field_Model
{
	/**
	 * Function to check whether the current field is editable.
	 *
	 * @return bool
	 */
	public function isEditable(): bool
	{
		if ($this->get('sourceFieldModel')) {
			$permissions = $this->get('sourceFieldModel')->isEditable();
		} else {
			$nonEditablePickListValues = ['duration_minutes', 'payment_duration', 'recurring_frequency', 'visibility'];
			$permissions = \in_array($this->get('displaytype'), [1, 10]) && \in_array($this->get('presence'), [0, 2]) && !\in_array($this->getName(), $nonEditablePickListValues) && \in_array($this->getFieldDataType(), ['picklist', 'multipicklist']) && 'Users' !== $this->getModuleName();
		}

		return $permissions;
	}

	/**
	 * Get picklist value model.
	 *
	 * @param int|null $itemId
	 *
	 * @return App\Fields\Picklist\Item
	 */
	public function getItemModel(?int $itemId = null): App\Fields\Picklist\Item
	{
		return \App\Fields\Picklist\Item::getInstance($this, $itemId);
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
			if ($intersectionMode && (int) $row['rolecount'] !== \count($roleIdList)) {
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
	 * @param string|int                                              $value  fieldname or fieldid
	 * @param Settings_Picklist_Module_Model|Vtiger_Module_Model|bool $module module instance
	 *
	 * @return self
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

	/** {@inheritdoc} */
	public function getValidator()
	{
		return $this->validator ?? parent::getValidator();
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
		if (preg_match('/[\<\>\"\#]/', $value)) {
			throw new \App\Exceptions\AppException(\App\Language::translateArgs('ERR_SPECIAL_CHARACTERS_NOT_ALLOWED', 'Other.Exceptions', '<>"#'), 512);
		}
		if ($this->getMaxValue() && \strlen($value) > $this->getMaxValue()) {
			throw new \App\Exceptions\AppException(\App\Language::translate('ERR_EXCEEDED_NUMBER_CHARACTERS', 'Other.Exceptions'), 512);
		}
		if ($this->isDuplicateValue($value, $id)) {
			throw new \App\Exceptions\AppException(\App\Language::translateArgs('ERR_DUPLICATES_VALUES_FOUND', 'Other.Exceptions', $value), 513);
		}
	}

	/**
	 * Check if picklist value exists.
	 *
	 * @param string   $value
	 * @param int|null $id
	 *
	 * @return bool
	 */
	public function isDuplicateValue(string $value, ?int $id = null): bool
	{
		$picklistValues = \App\Fields\Picklist::getValuesName($this->getName());
		if ($id) {
			unset($picklistValues[$id]);
		}

		return \in_array(strtolower($value), array_map('strtolower', $picklistValues));
	}
}
