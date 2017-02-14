<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

abstract class EntityMeta
{

	public static $RETRIEVE = "DetailView";
	public static $CREATE = "Save";
	public static $UPDATE = "EditView";
	public static $DELETE = "Delete";
	protected $webserviceObject;
	protected $objectName;
	protected $objectId;
	protected $user;
	protected $baseTable;
	protected $tableList;
	protected $tableIndexList;
	protected $defaultTableList;
	protected $idColumn;
	protected $userAccessibleColumns;
	protected $columnTableMapping;
	protected $fieldColumnMapping;
	protected $mandatoryFields;
	protected $referenceFieldDetails;
	protected $emailFields;
	protected $ownerFields;
	protected $moduleFields = null;

	protected function __construct($webserviceObject, $user)
	{
		$this->webserviceObject = $webserviceObject;
		$this->objectName = $this->webserviceObject->getEntityName();
		$this->objectId = $this->webserviceObject->getEntityId();

		$this->user = $user;
	}

	public function getEmailFields()
	{
		if ($this->emailFields === null) {
			$this->emailFields = [];
			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				if (strcasecmp($webserviceField->getFieldType(), 'e') === 0) {
					array_push($this->emailFields, $fieldName);
				}
			}
		}

		return $this->emailFields;
	}

	public function getFieldColumnMapping()
	{
		if ($this->fieldColumnMapping === null) {
			$this->fieldColumnMapping = [];

			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				$this->fieldColumnMapping[$fieldName] = $webserviceField->getColumnName();
			}
			$this->fieldColumnMapping['id'] = $this->idColumn;
		}
		return $this->fieldColumnMapping;
	}

	public function getMandatoryFields()
	{
		if ($this->mandatoryFields === null) {
			$this->mandatoryFields = [];

			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				if ($webserviceField->isMandatory() === true) {
					array_push($this->mandatoryFields, $fieldName);
				}
			}
		}
		return $this->mandatoryFields;
	}

	public function getReferenceFieldDetails()
	{
		if ($this->referenceFieldDetails === null) {
			$this->referenceFieldDetails = [];

			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				foreach (Vtiger_Field_Model::$referenceTypes as $type) {
					if (strcasecmp($webserviceField->getFieldDataType(), $type) === 0) {
						$this->referenceFieldDetails[$fieldName] = $webserviceField->getReferenceList();
					}
				}
			}
		}
		return $this->referenceFieldDetails;
	}

	public function getOwnerFields()
	{
		if ($this->ownerFields === null) {
			$this->ownerFields = [];

			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				if (strcasecmp($webserviceField->getFieldDataType(), 'owner') === 0) {
					array_push($this->ownerFields, $fieldName);
				}
			}
			sort($this->ownerFields);
		}
		return $this->ownerFields;
	}

	public function getSharedOwnerFields()
	{
		if ($this->sharedOwnerFields === null) {
			$this->sharedOwnerFields = [];

			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				if (strcasecmp($webserviceField->getFieldDataType(), 'sharedOwner') === 0) {
					array_push($this->sharedOwnerFields, $fieldName);
				}
			}
			sort($this->sharedOwnerFields);
		}
		return $this->sharedOwnerFields;
	}

	public function getObectIndexColumn()
	{
		return $this->idColumn;
	}

	public function getUserAccessibleColumns()
	{
		if ($this->userAccessibleColumns === null) {
			$this->userAccessibleColumns = [];

			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				array_push($this->userAccessibleColumns, $webserviceField->getColumnName());
			}
			array_push($this->userAccessibleColumns, $this->idColumn);
		}
		return $this->userAccessibleColumns;
	}

	public function getFieldByColumnName($column)
	{
		$fields = $this->getModuleFields();
		foreach ($fields as $fieldName => $webserviceField) {
			if ($column == $webserviceField->getColumnName()) {
				return $webserviceField;
			}
		}
		return null;
	}

	public function getColumnTableMapping()
	{
		if ($this->columnTableMapping === null) {
			$this->columnTableMapping = [];

			$moduleFields = $this->getModuleFields();
			foreach ($moduleFields as $fieldName => $webserviceField) {
				$this->columnTableMapping[$webserviceField->getColumnName()] = $webserviceField->getTableName();
			}
			$this->columnTableMapping[$this->idColumn] = $this->baseTable;
		}
		return $this->columnTableMapping;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function hasMandatoryFields($row)
	{

		$mandatoryFields = $this->getMandatoryFields();
		$hasMandatory = true;
		foreach ($mandatoryFields as $ind => $field) {
			// dont use empty API as '0'(zero) is a valid value.
			if (!isset($row[$field]) || $row[$field] === "" || $row[$field] === null) {
				//throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING,
				//		"$field does not have a value");
			}
		}
		return $hasMandatory;
	}

	public function isUpdateMandatoryFields($element)
	{
		if (!is_array($element)) {
			throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING, "Mandatory field does not have a value");
		}
		$mandatoryFields = $this->getMandatoryFields();
		$updateFields = array_keys($element);
		$hasMandatory = true;
		$updateMandatoryFields = array_intersect($updateFields, $mandatoryFields);
		if (!empty($updateMandatoryFields)) {
			foreach ($updateMandatoryFields as $ind => $field) {
				// dont use empty API as '0'(zero) is a valid value.
				if (!isset($element[$field]) || $element[$field] === "" || $element[$field] === null) {
					throw new WebServiceException(WebServiceErrorCode::$MANDFIELDSMISSING, "$field does not have a value");
				}
			}
		}
		return $hasMandatory;
	}

	public function getModuleFields()
	{
		return $this->moduleFields;
	}

	public function getFieldNameListByType($type)
	{
		$type = strtolower($type);
		$typeList = [];
		$moduleFields = $this->getModuleFields();
		foreach ($moduleFields as $fieldName => $webserviceField) {
			if (strcmp($webserviceField->getFieldDataType(), $type) === 0) {
				array_push($typeList, $fieldName);
			}
		}
		return $typeList;
	}

	public function getFieldListByType($type)
	{
		$type = strtolower($type);
		$typeList = [];
		$moduleFields = $this->getModuleFields();
		foreach ($moduleFields as $fieldName => $webserviceField) {
			if (strcmp($webserviceField->getFieldDataType(), $type) === 0) {
				array_push($typeList, $webserviceField);
			}
		}
		return $typeList;
	}

	public function getIdColumn()
	{
		return $this->idColumn;
	}

	public function getEntityBaseTable()
	{
		return $this->baseTable;
	}

	public function getEntityTableIndexList()
	{
		return $this->tableIndexList;
	}

	public function getEntityDefaultTableList()
	{
		return $this->defaultTableList;
	}

	public function getEntityTableList()
	{
		return $this->tableList;
	}

	public function getEntityAccessControlQuery()
	{
		$accessControlQuery = '';
		return $accessControlQuery;
	}

	public function getEntityDeletedQuery()
	{
		if ($this->getEntityName() == 'Leads') {
			return "vtiger_crmentity.deleted=0 and vtiger_leaddetails.converted=0";
		}
		if ($this->getEntityName() != "Users") {
			return "vtiger_crmentity.deleted=0";
		}
		// not sure whether inactive users should be considered deleted or not.
		return "vtiger_users.status='Active'";
	}

	abstract function hasPermission($operation, $webserviceId);

	abstract function hasAssignPrivilege($ownerWebserviceId);

	abstract function hasDeleteAccess();

	abstract function hasAccess();

	abstract function hasReadAccess();

	abstract function hasWriteAccess();

	abstract function getEntityName();

	abstract function getEntityId();

	abstract function exists($recordId);

	abstract function getObjectEntityName($webserviceId);

	abstract public function getNameFields();

	abstract public function getName($webserviceId);

	abstract public function isModuleEntity();
}
