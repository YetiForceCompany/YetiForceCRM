<?php

/**
 * Field Class for MappedFields Settings
 * @package YetiForce.Field
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_Field_Model extends Vtiger_Field_Model
{

	public $inventoryField = false;

	/**
	 * Function to get field uitype
	 * @return string uitype
	 */
	public function getUIType()
	{
		if (!$this->get('uitype')) {
			$this->uitype = $this->getWebserviceFieldObject()->getUIType();
		}
		return $this->uitype;
	}

	/**
	 * Function to get field picklist
	 * @return <Array> picklist
	 */
	public function getPicklistDetails()
	{
		return $this->getWebserviceFieldObject()->getPicklistDetails();
	}

	/**
	 * Function to get field data type
	 * @return string data type
	 */
	public function getFieldDataType()
	{
		if (!$this->fieldDataType && $this->get('typeofdata') == 'INVENTORY') {
			$this->fieldDataType = 'inventory';
		} elseif (!$this->fieldDataType) {
			$this->fieldDataType = parent::getFieldDataType();
		}
		if ($this->fieldDataType == 'salutation') {
			$this->fieldDataType = 'string';
		}
		return $this->fieldDataType;
	}

	/**
	 * Function to get the field type
	 * @return string type of the field
	 */
	public function getFieldType()
	{
		if ($this->get('name') === 'id') {
			return 'SELF';
		}
		return parent::getFieldType();
	}

	/**
	 * Function to get clean instance
	 * @return <Settings_MappedFields_Field_Model>
	 */
	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Function to get Field instance from array
	 * @return <Settings_MappedFields_Field_Model>
	 */
	public static function fromArray($row = [])
	{
		$instance = new self();
		foreach ($row as $name => $value) {
			$instance->set($name, $value);
		}
		return $instance;
	}

	/**
	 * Function to get field instance from WebserviceFieldObject
	 * @return <Settings_MappedFields_Field_Model>
	 */
	public static function getInstanceFromWebserviceFieldObject($webserviceField)
	{
		$row = [];
		$row['uitype'] = $webserviceField->getUIType();
		$row['table'] = $webserviceField->getTableName();
		$row['column'] = $webserviceField->getColumnName();
		$row['name'] = $webserviceField->getFieldName();
		$row['label'] = $webserviceField->getFieldLabelKey();
		$row['displaytype'] = $webserviceField->getDisplayType();
		$row['masseditable'] = $webserviceField->getMassEditable();
		$row['typeofdata'] = $webserviceField->getTypeOfData();
		$row['presence'] = $webserviceField->getPresence();
		$row['id'] = $webserviceField->getFieldId();
		$row['defaultvalue'] = $webserviceField->getDefault();
		$row['mandatory'] = $webserviceField->isMandatory();
		$row['fieldparams'] = $webserviceField->getFieldParams();

		$instance = self::fromArray($row);
		$instance->webserviceField == $webserviceField;
		return $instance;
	}

	/**
	 * Function to check if the current field is mandatory or not
	 * @return boolean - true/false
	 */
	public function isMandatory()
	{
		if (!$this->mandatory) {
			$this->mandatory = parent::isMandatory();
		}
		return $this->mandatory;
	}

	/**
	 * Function to get field label
	 * @return string label
	 */
	public function getFieldLabelKey()
	{
		return $this->get('label');
	}

	/**
	 * Function to get field instance from InventoryFieldObject
	 * @return <Settings_MappedFields_Field_Model>
	 */
	public static function getInstanceFromInventoryFieldObject($inventoryField)
	{
		$row = [];
		$row['column'] = $inventoryField->getColumnName();
		$row['name'] = $inventoryField->getColumnName();
		$row['label'] = $inventoryField->get('label');
		$row['id'] = $inventoryField->getColumnName();
		$row['defaultvalue'] = $inventoryField->getDefaultValue();
		$row['mandatory'] = $inventoryField->isMandatory();
		$row['typeofdata'] = 'INVENTORY';

		$instance = self::fromArray($row);
		$instance->inventoryField == $inventoryField;
		return $instance;
	}

	/**
	 * Function to get instance
	 * @param <String/Integer> $value
	 * @param string $module
	 * @param string $type
	 * @return <Settings_MappedFields_Field_Model> field model
	 */
	public static function getInstance($value, $module = false, $type = '')
	{
		switch ($type) {
			case 'SELF':
				$fieldModel = parent::getInstance($value, $module);
				if (!$fieldModel) {
					$fields = Settings_MappedFields_Module_Model::getSpecialFields();
					$fieldModel = $fields[$value];
				}
				break;
			case 'INVENTORY':
				$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($module->getName());
				$inventoryFields = $inventoryFieldModel->getFields();
				return self::getInstanceFromInventoryFieldObject($inventoryFields[$value]);
			default:
				$fieldModel = parent::getInstance($value, $module);
				break;
		}

		if ($fieldModel) {
			$objectProperties = get_object_vars($fieldModel);
			$fieldModel = new self();
			foreach ($objectProperties as $properName => $propertyValue) {
				$fieldModel->$properName = $propertyValue;
			}
		}
		return $fieldModel;
	}
}
