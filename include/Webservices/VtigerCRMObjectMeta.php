<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class VtigerCRMObjectMeta extends EntityMeta
{

	private $tabId;
	private $meta;
	private $assign;
	private $hasAccess;
	private $hasReadAccess;
	private $hasWriteAccess;
	private $hasDeleteAccess;
	private $assignUsers;

	public function __construct($webserviceObject, $user)
	{

		parent::__construct($webserviceObject, $user);

		$this->columnTableMapping = null;
		$this->fieldColumnMapping = null;
		$this->userAccessibleColumns = null;
		$this->mandatoryFields = null;
		$this->emailFields = null;
		$this->referenceFieldDetails = null;
		$this->ownerFields = null;
		$this->moduleFields = [];
		$this->hasAccess = false;
		$this->hasReadAccess = false;
		$this->hasWriteAccess = false;
		$this->hasDeleteAccess = false;
		$instance = vtws_getModuleInstance($this->webserviceObject);
		$this->idColumn = $instance->tab_name_index[$instance->table_name];
		$this->baseTable = $instance->table_name;
		$this->tableList = $instance->tab_name;
		$this->tableIndexList = $instance->tab_name_index;
		if (in_array('vtiger_crmentity', $instance->tab_name)) {
			$this->defaultTableList = array('vtiger_crmentity');
		} else {
			$this->defaultTableList = [];
		}
		$this->tabId = null;
	}

	/**
	 * returns tabid of the current object.
	 * @return Integer 
	 */
	public function getTabId()
	{
		if ($this->tabId === null) {
			$this->tabId = \App\Module::getModuleId($this->objectName);
		}
		return $this->tabId;
	}

	/**
	 * returns tabid that can be consumed for database lookup purpose generally, events and
	 * calendar are treated as the same module
	 * @return Integer
	 */
	public function getEffectiveTabId()
	{
		return \App\Module::getModuleId($this->getTabName());
	}

	public function getTabName()
	{
		if ($this->objectName == 'Events') {
			return 'Calendar';
		}
		return $this->objectName;
	}

	private function computeAccess()
	{
		$adb = PearDatabase::getInstance();
		$active = \App\Module::isModuleActive($this->getTabName());
		if ($active === false) {
			$this->hasAccess = false;
			$this->hasReadAccess = false;
			$this->hasWriteAccess = false;
			$this->hasDeleteAccess = false;
			return;
		}
		$currentUser = Users_Privileges_Model::getInstanceById($this->user->id);
		$profileGlobalPermission = $currentUser->get('profile_global_permission');
		if ($currentUser->isAdminUser() || $profileGlobalPermission[1] === 0 || $profileGlobalPermission[2] === 0) {
			$this->hasAccess = true;
			$this->hasReadAccess = true;
			$this->hasWriteAccess = true;
			$this->hasDeleteAccess = true;
		} else {
			$profileList = $currentUser->getProfiles();

			$sql = sprintf('SELECT globalactionpermission,globalactionid FROM vtiger_profile2globalpermissions WHERE profileid IN (%s)', generateQuestionMarks($profileList));
			$result = $adb->pquery($sql, array($profileList));
			while ($row = $adb->getRow($result)) {
				$permission = $row['globalactionpermission'];
				$globalactionid = $row['globalactionid'];
				if ($permission != 1 || $permission != '1') {
					$this->hasAccess = true;
					if ($globalactionid == 2 || $globalactionid == '2') {
						$this->hasWriteAccess = true;
						$this->hasDeleteAccess = true;
					} else {
						$this->hasReadAccess = true;
					}
				}
			}

			$sql = sprintf('select permissions from vtiger_profile2tab where profileid in (%s) and tabid = ?', generateQuestionMarks($profileList));
			$result = $adb->pquery($sql, array($profileList, $this->getTabId()));
			$standardDefined = false;
			$permission = $adb->getSingleValue($result);
			if ($permission == 1 || $permission == '1') {
				$this->hasAccess = false;
				return;
			} else {
				$this->hasAccess = true;
			}

			$sql = sprintf("select * from vtiger_profile2standardpermissions where profileid in (%s) and tabid=?", generateQuestionMarks($profileList));
			$result = $adb->pquery($sql, array($profileList, $this->getTabId()));
			while ($row = $adb->getRow($result)) {
				$standardDefined = true;
				$permission = $row['permissions'];
				$operation = $row['operation'];
				if ($permission != 1 || $permission != '1') {
					$this->hasAccess = true;
					if ($operation == 0 || $operation == '0') {
						$this->hasWriteAccess = true;
					} else if ($operation == 1 || $operation == '1') {
						$this->hasWriteAccess = true;
					} else if ($operation == 2 || $operation == '2') {
						$this->hasDeleteAccess = true;
					} else if ($operation == 4 || $operation == '4') {
						$this->hasReadAccess = true;
					}
				}
			}
			if (!$standardDefined) {
				$this->hasReadAccess = true;
				$this->hasWriteAccess = true;
				$this->hasDeleteAccess = true;
			}
		}
	}

	public function hasAccess()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return $this->hasAccess;
	}

	public function hasWriteAccess()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return $this->hasWriteAccess;
	}

	public function hasReadAccess()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return $this->hasReadAccess;
	}

	public function hasDeleteAccess()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return $this->hasDeleteAccess;
	}

	public function hasPermission($operation, $webserviceId)
	{

		$idComponents = vtws_getIdComponents($webserviceId);
		$id = $idComponents[1];

		$permitted = isPermitted($this->getTabName(), $operation, $id);
		if (strcmp($permitted, "yes") === 0) {
			return true;
		}
		return false;
	}

	public function hasAssignPrivilege($webserviceId)
	{
		$adb = PearDatabase::getInstance();

		// administrator's have assign privilege
		if (\vtlib\Functions::userIsAdministrator($this->user))
			return true;

		$idComponents = vtws_getIdComponents($webserviceId);
		$userId = $idComponents[1];
		$ownerTypeId = $idComponents[0];

		if ($userId === null || $userId == '' || $ownerTypeId === null || $ownerTypeId == '') {
			return false;
		}
		$webserviceObject = VtigerWebserviceObject::fromId($adb, $ownerTypeId);
		if (strcasecmp($webserviceObject->getEntityName(), "Users") === 0) {
			if ($userId == $this->user->id) {
				return true;
			}
			if (!$this->assign) {
				$this->retrieveUserHierarchy();
			}
			if (in_array($userId, array_keys($this->assignUsers))) {
				return true;
			} else {
				return false;
			}
		} elseif (strcasecmp($webserviceObject->getEntityName(), "Groups") === 0) {
			$tabId = $this->getTabId();
			$groups = vtws_getUserAccessibleGroups($tabId, $this->user);
			foreach ($groups as $group) {
				if ($group['id'] == $userId) {
					return true;
				}
			}
			return false;
		}
	}

	public function getUserAccessibleColumns()
	{

		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return parent::getUserAccessibleColumns();
	}

	public function getModuleFields()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return parent::getModuleFields();
	}

	public function getColumnTableMapping()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return parent::getColumnTableMapping();
	}

	public function getFieldColumnMapping()
	{

		if (!$this->meta) {
			$this->retrieveMeta();
		}
		if ($this->fieldColumnMapping === null) {
			$this->fieldColumnMapping = [];
			foreach ($this->moduleFields as $fieldName => $webserviceField) {
				if (strcasecmp($webserviceField->getFieldDataType(), 'file') !== 0) {
					$this->fieldColumnMapping[$fieldName] = $webserviceField->getColumnName();
				}
			}
			$this->fieldColumnMapping['id'] = $this->idColumn;
		}
		return $this->fieldColumnMapping;
	}

	public function getMandatoryFields()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return parent::getMandatoryFields();
	}

	public function getReferenceFieldDetails()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return parent::getReferenceFieldDetails();
	}

	public function getOwnerFields()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return parent::getOwnerFields();
	}

	public function getEntityName()
	{
		return $this->objectName;
	}

	public function getEntityId()
	{
		return $this->objectId;
	}

	public function getEmailFields()
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}
		return parent::getEmailFields();
	}

	public function getFieldIdFromFieldName($fieldName)
	{
		if (!$this->meta) {
			$this->retrieveMeta();
		}

		if (isset($this->moduleFields[$fieldName])) {
			$webserviceField = $this->moduleFields[$fieldName];
			return $webserviceField->getFieldId();
		}
		return null;
	}

	public function retrieveMeta()
	{

		require_once('modules/CustomView/CustomView.php');
		$current_user = vtws_preserveGlobal('current_user', $this->user);
		$theme = vtws_preserveGlobal('theme', $this->user->theme);
		$default_language = AppConfig::main('default_language');
		$current_language = vglobal('current_language');
		if (empty($current_language))
			$current_language = $default_language;
		$current_language = vtws_preserveGlobal('current_language', $current_language);

		$this->computeAccess();

		$fields = \App\Field::getFieldsPermissions($this->getTabId());
		foreach ($fields as &$field) {
			$webserviceField = new WebserviceField($field);
			$this->moduleFields[$webserviceField->getFieldName()] = $webserviceField;
		}
		$this->meta = true;
		VTWS_PreserveGlobal::flush();
	}

	private function retrieveUserHierarchy()
	{

		$heirarchyUsers = \App\Fields\Owner::getInstance()->getUsers(false, 'Active', $this->user->id);
		$groupUsers = vtws_getUsersInTheSameGroup($this->user->id);
		$this->assignUsers = $heirarchyUsers + $groupUsers;
		$this->assign = true;
	}

	public function getObjectEntityName($webserviceId)
	{
		$adb = PearDatabase::getInstance();

		$idComponents = vtws_getIdComponents($webserviceId);
		$id = $idComponents[1];

		$seType = null;
		if ($this->objectName === 'Users') {
			if (\App\User::isExists($id)) {
				$seType = $this->objectName;
			}
		} else {
			$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($id);
			if ($recordMetaData && $recordMetaData['deleted'] === 0) {
				$seType = $recordMetaData['setype'];
				if ($seType === 'Calendar') {
					$seType = vtws_getCalendarEntityType($id);
				}
			}
		}
		return $seType;
	}

	protected static $userExistsCache = [];

	/**
	 * Function checks if record exists
	 * @param int $recordId - Rekord ID
	 * @return boolean
	 */
	public function exists($recordId)
	{
		// Caching user existence value for optimizing repeated reads.
		// 
		// NOTE: We are not caching the record existence 
		// to ensure only latest state from DB is sent.
		$exists = false;
		if ($this->objectName == 'Users') {
			$exists = \App\User::isExists($recordId);
		} else {
			$exists = \App\Record::isExists($recordId, $this->objectName);
		}
		return $exists;
	}

	public function getNameFields()
	{
		$data = \App\Module::getEntityInfo(\App\Module::getModuleName($this->getEffectiveTabId()));
		$fieldNames = '';
		if ($data) {
			$fieldNames = $data['fieldname'];
		}
		return $fieldNames;
	}

	public function getName($webserviceId)
	{

		$idComponents = vtws_getIdComponents($webserviceId);
		$id = $idComponents[1];

		$nameList = getEntityName($this->getTabName(), array($id));
		return $nameList[$id];
	}

	public function getEntityAccessControlQuery()
	{
		$accessControlQuery = '';
		$instance = vtws_getModuleInstance($this->webserviceObject);
		if ($this->getTabName() != 'Users') {
			$accessControlQuery = $instance->getNonAdminAccessControlQuery($this->getTabName(), $this->user);
		}
		return $accessControlQuery;
	}

	public function getJoinClause($tableName)
	{
		$instance = vtws_getModuleInstance($this->webserviceObject);
		return $instance->getJoinClause($tableName);
	}

	public function isModuleEntity()
	{
		return true;
	}
}
