<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/**
 * Vtiger Module Model Class
 */
class Vtiger_Module_Model extends \vtlib\Module
{

	protected $blocks;
	protected $nameFields;
	protected $moduleMeta;
	protected $fields;
	protected $relations = null;
	protected $moduleType = '0';
	protected $entityInstance;

	/**
	 * Function to get the Module/Tab id
	 * @return <Number>
	 */
	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return boolean true/false
	 */
	public function isEntityModule()
	{
		return ($this->isentitytype == '1') ? true : false;
	}

	/**
	 * Function to check whether the module is enabled for quick create
	 * @return boolean - true/false
	 */
	public function isQuickCreateSupported()
	{
		return $this->isEntityModule() && !$this->isInventory() && Users_Privileges_Model::isPermitted($this->getName(), 'CreateView');
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return boolean - true/false
	 */
	public function isSummaryViewSupported()
	{
		return true;
	}

	public function getModuleType()
	{
		return $this->get('type');
	}

	public function isInventory()
	{
		return $this->getModuleType() == 1;
	}

	/**
	 * Function to get singluar label key
	 * @return string - Singular module label key
	 */
	public function getSingularLabelKey()
	{
		return 'SINGLE_' . $this->get('name');
	}

	/**
	 * Function to get the value of a given property
	 * @param string $propertyName
	 * @return <Object>
	 * @throws Exception
	 */
	public function get($propertyName)
	{
		if (property_exists($this, $propertyName)) {
			return $this->$propertyName;
		}
		throw new Exception($propertyName . ' doest not exists in class ' . get_class($this));
	}

	/**
	 * Function to set the value of a given property
	 * @param string $propertyName
	 * @param <Object> $propertyValue
	 * @return Vtiger_Module_Model instance
	 */
	public function set($propertyName, $propertyValue)
	{
		$this->$propertyName = $propertyValue;
		return $this;
	}

	/**
	 * Function checks if the module is Active
	 * @return boolean
	 */
	public function isActive()
	{
		return in_array($this->get('presence'), array(0, 2));
	}

	/**
	 * Function checks if the module is enabled for tracking changes
	 * @return boolean
	 */
	public function isTrackingEnabled()
	{
		require_once 'modules/ModTracker/ModTracker.php';
		$trackingEnabled = ModTracker::isTrackingEnabledForModule($this->getName());
		return ($this->isActive() && $trackingEnabled);
	}

	/**
	 * Function checks if comment is enabled
	 * @return boolean
	 */
	public function isCommentEnabled()
	{
		$enabled = false;
		$query = new \App\Db\Query();
		$commentsModuleModel = Vtiger_Module_Model::getInstance('ModComments');
		if ($commentsModuleModel && $commentsModuleModel->isActive()) {
			$fieldId = $query->select('fieldid')->from('vtiger_field')->where(['fieldname' => 'related_to', 'tabid' => $commentsModuleModel->getId()])->scalar();
			if (!empty($fieldId)) {
				$query->select('relmodule')->from('vtiger_fieldmodulerel')->where(['fieldid' => $fieldId]);
				$dataReader = $query->createCommand()->query();
				while ($row = $dataReader->read()) {
					if ($this->getName() === $row['relmodule']) {
						$enabled = true;
					}
				}
			}
		} else {
			$enabled = false;
		}
		return $enabled;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name
	 * @param int|string $mixed id or name of the module
	 * @return self
	 */
	public static function getInstance($mixed)
	{
		$instance = Vtiger_Cache::get('module', $mixed);
		if (!$instance) {
			$instance = false;
			$moduleObject = parent::getInstance($mixed);
			if ($moduleObject) {
				$instance = self::getInstanceFromModuleObject($moduleObject);
				Vtiger_Cache::set('module', $moduleObject->id, $instance);
				Vtiger_Cache::set('module', $moduleObject->name, $instance);
			}
		}
		return $instance;
	}

	/**
	 * Function to get the instance of Vtiger Module Model from a given vtlib\Module object
	 * @param vtlib\Module $moduleObj
	 * @return self
	 */
	public static function getInstanceFromModuleObject(vtlib\Module $moduleObj)
	{
		$objectProperties = get_object_vars($moduleObj);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $objectProperties['name']);
		$moduleModel = new $modelClassName();
		foreach ($objectProperties as $properName => $propertyValue) {
			$moduleModel->$properName = $propertyValue;
		}
		return $moduleModel;
	}

	/**
	 * Function to get the instance of Vtiger Module Model from a given list of key-value mapping
	 * @param array $valueArray
	 * @return self
	 */
	public static function getInstanceFromArray($valueArray)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $valueArray['name']);
		$instance = new $modelClassName();
		$instance->initialize($valueArray);
		return $instance;
	}

	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(\Vtiger_Record_Model $recordModel)
	{
		$moduleName = $this->get('name');
		$focus = $this->getEntityInstance();
		$fields = $focus->column_fields;
		foreach ($fields as $fieldName => $fieldValue) {
			$fieldValue = $recordModel->get($fieldName);
			if (is_array($fieldValue)) {
				$focus->column_fields[$fieldName] = $fieldValue;
			} else if ($fieldValue !== null) {
				$focus->column_fields[$fieldName] = decode_html($fieldValue);
			}
		}
		if (!$recordModel->isNew() && empty($recordModel->changes)) {
			App\Log::warning('ERR_NO_DATA');
			//return $recordModel;
		}
		$focus->isInventory = $this->isInventory();

		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($recordModel);
		$eventHandler->setModuleName($moduleName);

		$eventHandler->trigger('EntityInventoryBeforeSave');
		if ($this->isInventory()) {
			$focus->inventoryData = $recordModel->getInventoryData();
		}
		$eventHandler->trigger('EntityInventoryAfterSave');


		$focus->mode = $recordModel->get('mode');
		$recordId = $focus->id = $recordModel->getId();
		if ($recordModel->isNew()) {
			$recordId = $recordModel->get('newRecord');
		}
		$focus->newRecord = $recordId;
		$eventHandler->trigger('EntityBeforeSave');
		$recordModel->setData($focus->column_fields)->setEntity($focus)->set('mode', $focus->mode);
		$recordModel->setId($recordId);
		$focus->save($moduleName);
		//$recordModel->saveToDb();
		$recordModel->setData($focus->column_fields)->setEntity($focus)->set('mode', $focus->mode);
		if ($moduleName !== 'Users') {
			$recordModel->setId($recordId);
		}
		$eventHandler->trigger('EntityAfterSave');
		$eventHandler->setSystemTrigger('EntityAfterSaveSystem');
		return $recordModel;
	}

	/**
	 * Function to delete a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function deleteRecord($recordModel)
	{
		$moduleName = $this->get('name');
		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($recordModel);
		$eventHandler->setModuleName($moduleName);
		$eventHandler->trigger('EntityBeforeDelete');

		$focus = $this->getEntityInstance();
		$focus->trash($moduleName, $recordModel->getId());

		$eventHandler->trigger('EntityAfterDelete');
		if (method_exists($focus, 'transferRelatedRecords') && $recordModel->get('transferRecordIDs')) {
			$focus->transferRelatedRecords($moduleName, $recordModel->get('transferRecordIDs'), $recordModel->getId());
		}

		vimport('~~modules/com_vtiger_workflow/include.php');
		vimport('~~modules/com_vtiger_workflow/VTEntityMethodManager.php');
		$wfs = new VTWorkflowManager(PearDatabase::getInstance());
		$workflows = $wfs->getWorkflowsForModule($moduleName, VTWorkflowManager::$ON_DELETE);
		if (count($workflows)) {
			$wsId = vtws_getWebserviceEntityId($moduleName, $recordModel->getId());
			$entityCache = new VTEntityCache(Users_Record_Model::getCurrentUserModel());
			$entityData = $entityCache->forId($wsId);
			foreach ($workflows as $id => &$workflow) {
				if ($workflow->evaluate($entityCache, $entityData->getId())) {
					$workflow->performTasks($entityData);
				}
			}
		}
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('u_yf_crmentity_label', ['crmid' => $recordModel->getId()])->execute();
		$db->createCommand()->delete('u_yf_crmentity_search_label', ['crmid' => $recordModel->getId()])->execute();
	}

	/**
	 * Function to get the module meta information
	 * @param <type> $userModel - user model
	 */
	public function getModuleMeta($userModel = false)
	{
		if (empty($this->moduleMeta)) {
			if (empty($userModel)) {
				$userModel = Users_Record_Model::getCurrentUserModel();
			}
			$this->moduleMeta = Vtiger_ModuleMeta_Model::getInstance($this->get('name'), $userModel);
		}
		return $this->moduleMeta;
	}
	//Note : This api is using only in RelationListview - for getting columnfields of Related Module
	//Need to review........

	/**
	 * Function to get the module field mapping
	 * @return <array>
	 */
	public function getColumnFieldMapping()
	{
		$moduleMeta = $this->getModuleMeta();
		$meta = $moduleMeta->getMeta();
		$fieldColumnMapping = $meta->getFieldColumnMapping();
		return array_flip($fieldColumnMapping);
	}

	/**
	 * Function to get the ListView Component Name
	 * @return string
	 */
	public function getListViewName()
	{
		return 'List';
	}

	/**
	 * Function to get listview url with all filter
	 * @return string URL
	 */
	public function getListViewUrlWithAllFilter()
	{
		return $this->getListViewUrl() . '&viewname=' . $this->getAllFilterCvidForModule();
	}

	/**
	 * Function returns the All filter for the module
	 * @return int custom filter id
	 */
	public function getAllFilterCvidForModule()
	{
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT cvid FROM vtiger_customview WHERE viewname = 'All' AND entitytype = ?", [$this->getName()]);
		if ($result->rowCount()) {
			return $db->getSingleValue($result);
		}
		return false;
	}

	/**
	 * Function to get the DetailView Component Name
	 * @return string
	 */
	public function getDetailViewName()
	{
		return 'Detail';
	}

	/**
	 * Function to get the EditView Component Name
	 * @return string
	 */
	public function getEditViewName()
	{
		return 'Edit';
	}

	/**
	 * Function to get the DuplicateView Component Name
	 * @return string
	 */
	public function getDuplicateViewName()
	{
		return 'Edit';
	}

	/**
	 * Function to get the Delete Action Component Name
	 * @return string
	 */
	public function getDeleteActionName()
	{
		return 'Delete';
	}

	/**
	 * Function to get the Default View Component Name
	 * @return string
	 */
	public function getDefaultViewName()
	{
		return 'List';
	}

	/**
	 * Function to get the url for default view of the module
	 * @return string - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getDefaultViewName();
	}

	/**
	 * Function to get the url for list view of the module
	 * @return string - url
	 */
	public function getListViewUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getListViewName();
	}

	/**
	 * Function to get the url for the Create Record view of the module
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getEditViewName();
	}

	/**
	 * Function to get the url for the Create Record view of the module
	 * @return string - url
	 */
	public function getQuickCreateUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=QuickCreateAjax';
	}

	/**
	 * Function to get the url for the Import action of the module
	 * @return string - url
	 */
	public function getImportUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=Import';
	}

	/**
	 * Function to get the url for the Export action of the module
	 * @return string - url
	 */
	public function getExportUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=Export';
	}

	/**
	 * Function to get the url for the Find Duplicates action of the module
	 * @return string - url
	 */
	public function getFindDuplicatesUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=FindDuplicates';
	}

	/**
	 * Function to get the url to view Dashboard for the module
	 * @return string - url
	 */
	public function getDashBoardUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=DashBoard';
	}

	/**
	 * Function to get the url to view Details for the module
	 * @return string - url
	 */
	public function getDetailViewUrl($id)
	{
		return 'index.php?module=' . $this->get('name') . '&view=' . $this->getDetailViewName() . '&record=' . $id;
	}

	/**
	 * Function to get a Vtiger Record Model instance from an array of key-value mapping
	 * @param <Array> $valueArray
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function getRecordFromArray($valueArray, $rawData = false)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $this->get('name'));
		$recordInstance = new $modelClassName();
		if ($rawData !== false) {
			foreach ($this->getFields() as $field) {
				$column = $field->get('column');
				if (isset($rawData[$column])) {
					$rawData[$field->getName()] = $rawData[$column];
					unset($rawData[$column]);
				}
			}
		}
		$recordInstance->setFullForm(false);
		return $recordInstance->setData($valueArray)->setModuleFromInstance($this)->setRawData($rawData);
	}

	/**
	 * Function returns all the blocks for the module
	 * @return <Array of Vtiger_Block_Model> - list of block models
	 */
	public function getBlocks()
	{
		if (empty($this->blocks)) {
			$blocksList = [];
			$moduleBlocks = Vtiger_Block_Model::getAllForModule($this);
			foreach ($moduleBlocks as &$block) {
				$blocksList[$block->get('label')] = $block;
			}
			$this->blocks = $blocksList;
		}
		return $this->blocks;
	}

	/**
	 * Function that returns all the fields for the module
	 * @return Vtiger_Field_Model[] - list of field models
	 */
	public function getFields($blockInstance = false)
	{
		if (empty($this->fields)) {
			$moduleBlockFields = Vtiger_Field_Model::getAllForModule($this);
			$this->fields = [];
			foreach ($moduleBlockFields as $moduleFields) {
				foreach ($moduleFields as $moduleField) {
					$block = $moduleField->get('block');
					if (empty($block)) {
						continue;
					}
					$this->fields[$moduleField->get('name')] = $moduleField;
				}
			}
		}
		return $this->fields;
	}

	/**
	 * Function to get the field mode
	 * @param string $fieldName - field name
	 * @return Vtiger_Field_Model
	 */
	public function getField($fieldName)
	{
		return Vtiger_Field_Model::getInstance($fieldName, $this);
	}

	/**
	 * Function to get the field by column name.
	 * @param string $columnName - column name
	 * @return Vtiger_Field_Model
	 */
	public function getFieldByColumn($columnName)
	{
		foreach ($this->getFields() as &$field) {
			if ($field->get('column') === $columnName) {
				return $field;
			}
		}
		return NULL;
	}

	/**
	 * Get field by field name
	 * @param string $fieldName
	 * @return Vtiger_Field_Model
	 * @throws \Exception\AppException
	 */
	public function getFieldByName($fieldName)
	{
		if (!$this->fields) {
			$this->getFields();
		}
		if (isset($this->fields[$fieldName])) {
			return $this->fields[$fieldName];
		}
		App\Log::error("Field does not exist: $fieldName in " . __METHOD__);
		return false;
	}

	/**
	 * Function gives fields based on the type
	 * @param string $type - field type
	 * @return Vtiger_Field_Model[] - list of field models
	 */
	public function getFieldsByType($type)
	{
		if (!is_array($type)) {
			$type = array($type);
		}
		$fieldList = [];
		foreach ($this->getFields() as &$field) {
			if (in_array($field->getFieldDataType(), $type)) {
				$fieldList[$field->getName()] = $field;
			}
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the uitype
	 * @return Vtiger_Field_Model[] with field id as key
	 */
	public function getFieldsByUiType($uitype)
	{
		$fieldList = [];
		foreach ($this->getFields() as &$field) {
			if ($field->get('uitype') === $uitype) {
				$fieldList[$field->getName()] = $field;
			}
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the type
	 * @return Vtiger_Field_Model[] with field label as key
	 */
	public function getFieldsByLabel()
	{
		$fieldList = [];
		foreach ($this->getFields() as &$field) {
			$fieldList[$field->get('label')] = $field;
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the fieldid
	 * @return Vtiger_Field_Model[] with field id as key
	 */
	public function getFieldsById()
	{
		$fields = $this->getFields();
		$fieldList = [];
		foreach ($fields as &$field) {
			$fieldList[$field->getId()] = $field;
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the type
	 * @return Vtiger_Field_Model[] with field id as key
	 */
	public function getFieldsByDisplayType($type)
	{
		$fieldList = [];
		foreach ($this->getFields() as &$field) {
			if ($field->get('displaytype') === $type) {
				$fieldList[$field->getName()] = $field;
			}
		}
		return $fieldList;
	}

	/**
	 * Function gives list fields for save
	 * @return type
	 */
	public function getFieldsForSave(\Vtiger_Record_Model $recordModel)
	{
		$tabId = $this->getId();
		if ($this->getName() === 'Events') {
			$tabId = \App\Module::getModuleId('Calendar');
		}
		if ($this->getName() === 'Calendar' && $recordModel->get('activitytype') !== 'Task') {
			$tabId = \App\Module::getModuleId('Events');
		}
		$editFields = [];
		foreach (App\Field::getFieldsPermissions($tabId, false) as &$field) {
			$editFields[] = $field['fieldname'];
		}
		return array_diff($editFields, ['closedtime', 'shownerid', 'smcreatorid', 'modifiedtime', 'modifiedby']);
	}

	/**
	 * Function to get list of field for summary view
	 * @return Vtiger_Field_Model[] list of field models
	 */
	public function getSummaryViewFieldsList()
	{
		if (!isset($this->summaryFields)) {
			$summaryFields = [];
			foreach ($this->getFields() as $fieldName => &$fieldModel) {
				if ($fieldModel->isSummaryField() && $fieldModel->isActiveField()) {
					$summaryFields[$fieldName] = $fieldModel;
				}
			}
			$this->summaryFields = $summaryFields;
		}
		return $this->summaryFields;
	}

	/**
	 * Function that returns all the quickcreate fields for the module
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getQuickCreateFields()
	{
		$fieldList = $this->getFields();
		$quickCreateFieldList = [];

		$quickSequenceTemp = [];
		foreach ($fieldList as $fieldName => $fieldModel) {
			if ($fieldModel->isQuickCreateEnabled() && $fieldModel->isEditable()) {
				$quickCreateFieldList[$fieldName] = $fieldModel;
				$quickSequenceTemp[$fieldName] = $fieldModel->get('quicksequence');
			}
		}

		// sort quick create fields by sequence
		asort($quickSequenceTemp, SORT_NUMERIC);
		$quickCreateSortedList = [];
		foreach ($quickSequenceTemp as $key => $value) {
			$quickCreateSortedList[$key] = $quickCreateFieldList[$key];
		}

		return $quickCreateSortedList;
	}

	/**
	 * Function returns all the relation models
	 * @return Vtiger_Relation_Model[]
	 */
	public function getRelations()
	{
		if (empty($this->relations)) {
			$this->relations = Vtiger_Relation_Model::getAllRelations($this);
		}
		return $this->relations;
	}

	/**
	 * Function to retrieve name fields of a module
	 * @return <array> - array which contains fields which together construct name fields
	 */
	public function getNameFields()
	{

		$nameFieldObject = Vtiger_Cache::get('EntityField', $this->getName());
		$moduleName = $this->getName();
		if ($nameFieldObject && $nameFieldObject->fieldname) {
			$this->nameFields = explode(',', $nameFieldObject->fieldname);
		} else {
			$adb = PearDatabase::getInstance();

			$query = "SELECT fieldname, tablename, entityidfield FROM vtiger_entityname WHERE tabid = ?";
			$result = $adb->pquery($query, array($this->getId()));
			$this->nameFields = [];
			if ($result) {
				$rowCount = $adb->num_rows($result);
				if ($rowCount > 0) {
					$fieldNames = $adb->query_result($result, 0, 'fieldname');
					$this->nameFields = explode(',', $fieldNames);
				}
			}
			$entiyObj = new stdClass();
			$entiyObj->basetable = $adb->query_result($result, 0, 'tablename');
			$entiyObj->basetableid = $adb->query_result($result, 0, 'entityidfield');
			$entiyObj->fieldname = $fieldNames;
			Vtiger_Cache::set('EntityField', $this->getName(), $entiyObj);
		}

		return $this->nameFields;
	}

	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public function getRecentRecords($limit = 10)
	{
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$deletedCondition = $this->getDeletedRecordCondition();
		$nonAdminQuery .= Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());
		$query = sprintf('SELECT * FROM vtiger_crmentity %s WHERE setype=? && %s && modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?', $nonAdminQuery, $deletedCondition);
		$params = array($this->getName(), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);

		$recentRecords = [];
		while ($row = $db->getRow($result)) {
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

	/**
	 * Function that returns deleted records condition
	 * @return string
	 */
	public function getDeletedRecordCondition()
	{
		return 'vtiger_crmentity.deleted = 0';
	}

	/**
	 * Funtion that returns fields that will be showed in the record selection popup
	 * @return <Array of fields>
	 */
	public function getPopupFields()
	{
		return $this->getEntityInstance()->search_fields_name;
	}

	public function isWorkflowSupported()
	{
		if ($this->isEntityModule()) {
			return true;
		}
		return false;
	}

	/**
	 * Function checks if a module has module sequence numbering
	 * @return boolean
	 */
	public function hasSequenceNumberField()
	{
		if (!empty($this->fields)) {
			$fieldList = $this->getFields();
			foreach ($fieldList as $fieldName => $fieldModel) {
				if ($fieldModel->get('uitype') === '4') {
					return true;
				}
			}
		} else {
			$db = PearDatabase::getInstance();
			$query = 'SELECT 1 FROM vtiger_field WHERE uitype=4 and tabid=?';
			$params = array($this->getId());
			$result = $db->pquery($query, $params);
			return $db->num_rows($result) > 0 ? true : false;
		}
		return false;
	}

	/**
	 * Function to get all modules from CRM
	 * @param <array> $presence
	 * @param <array> $restrictedModulesList
	 * @return <array> List of module models Vtiger_Module_Model
	 */
	public static function getAll($presence = [], $restrictedModulesList = [], $isEntityType = false)
	{
		self::preModuleInitialize2();
		$moduleModels = Vtiger_Cache::get('vtiger', 'modules');
		if (!$moduleModels) {
			$moduleModels = [];
			$query = (new \App\Db\Query())->from('vtiger_tab');
			$where = [];
			if ($presence) {
				$where['presence'] = $presence;
			}
			if ($isEntityType) {
				$where['isentitytype'] = 1;
			}
			if ($where) {
				$query->where($where);
			}
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$moduleModels[$row['tabid']] = self::getInstanceFromArray($row);
				Vtiger_Cache::set('module', $row['tabid'], $moduleModels[$row['tabid']]);
				Vtiger_Cache::set('module', $row['name'], $moduleModels[$row['tabid']]);
			}
			if (!$presence) {
				Vtiger_Cache::set('vtiger', 'modules', $moduleModels);
			}
		}
		if ($presence && $moduleModels) {
			foreach ($moduleModels as $key => $moduleModel) {
				if (!in_array($moduleModel->get('presence'), $presence)) {
					unset($moduleModels[$key]);
				}
			}
		}
		if ($restrictedModulesList && $moduleModels) {
			foreach ($moduleModels as $key => $moduleModel) {
				if (in_array($moduleModel->getName(), $restrictedModulesList)) {
					unset($moduleModels[$key]);
				}
			}
		}
		return $moduleModels;
	}

	/**
	 * Get entity instance
	 * @return CRMEntity
	 */
	public function getEntityInstance()
	{
		if (isset($this->entityInstance)) {
			return $this->entityInstance;
		}
		return $this->entityInstance = CRMEntity::getInstance($this->getName());
	}

	public static function getEntityModules()
	{
		self::preModuleInitialize2();
		$moduleModels = Vtiger_Cache::get('vtiger', 'EntityModules');
		if (!$moduleModels) {
			$presence = array(0, 2);
			$moduleModels = self::getAll($presence);
			$restrictedModules = array('Emails', 'Integration', 'Dashboard');
			foreach ($moduleModels as $key => $moduleModel) {
				if (in_array($moduleModel->getName(), $restrictedModules) || $moduleModel->get('isentitytype') != 1) {
					unset($moduleModels[$key]);
				}
			}
			Vtiger_Cache::set('vtiger', 'EntityModules', $moduleModels);
		}
		return $moduleModels;
	}

	/**
	 * Function to get the list of all accessible modules for Quick Create
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public static function getQuickCreateModules($restrictList = false)
	{
		$quickCreateModules = Vtiger_Cache::get('getQuickCreateModules', $restrictList ? 1 : 0);
		if ($quickCreateModules !== false) {
			return $quickCreateModules;
		}

		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		self::preModuleInitialize2();
		$query = new \App\Db\Query();
		$query->select('vtiger_tab.*')->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_field.tabid')
			->where(['or', 'quickcreate = 0', 'quickcreate = 2'])
			->andWhere(['<>', 'vtiger_tab.presence', 1])
			->andWhere(['<>', 'vtiger_tab.type', 1])->distinct();
		if ($restrictList) {
			$query->andWhere(['not in', 'vtiger_tab.name', ['ModComments', 'PriceBooks', 'Events']]);
		}
		$quickCreateModules = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			if ($userPrivModel->hasModuleActionPermission($row['tabid'], 'CreateView')) {
				$moduleModel = self::getInstanceFromArray($row);
				$quickCreateModules[$row['name']] = $moduleModel;
			}
		}
		Vtiger_Cache::set('getQuickCreateModules', $restrictList ? 1 : 0, $quickCreateModules);
		return $quickCreateModules;
	}

	/**
	 * Function to get the list of all searchable modules
	 * @return array - List of Vtiger_Module_Model instances
	 */
	public static function getSearchableModules()
	{
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$entityModules = self::getEntityModules();
		$searchableModules = [];
		$dataReader = (new \App\Db\Query())->select('tabid')
				->from('vtiger_entityname')->where(['turn_off' => 0])
				->createCommand()->query();
		$turnOffModules = [];
		while ($row = $dataReader->read()) {
			$turnOffModules[$row['tabid']] = $row['tabid'];
		}
		foreach ($entityModules as $tabid => $moduleModel) {
			$moduleName = $moduleModel->getName();
			if ($moduleName == 'Users' || $moduleName == 'Emails' || $moduleName == 'Events' || in_array($tabid, $turnOffModules))
				continue;
			if ($userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'DetailView')) {
				$searchableModules[$moduleName] = $moduleModel;
			}
		}
		return $searchableModules;
	}

	protected static function preModuleInitialize2()
	{
		if (!Vtiger_Cache::get('EntityField', 'all')) {
			// Initialize meta information - to speed up instance creation (vtlib\ModuleBasic::initialize2)
			$dataReader = (new \App\Db\Query())->select('modulename,tablename,entityidfield,fieldname')
					->from('vtiger_entityname')
					->createCommand()->query();
			while ($row = $dataReader->read()) {
				$entiyObj = new stdClass();
				$entiyObj->basetable = $row['tablename'];
				$entiyObj->basetableid = $row['entityidfield'];
				$entiyObj->fieldname = $row['fieldname'];

				Vtiger_Cache::set('EntityField', $row['modulename'], $entiyObj);
				Vtiger_Cache::set('EntityField', 'all', true);
			}
		}
	}

	public static function getPicklistSupportedModules()
	{
		$modules = App\Fields\Picklist::getPickListModules();
		$modulesModelsList = [];
		foreach ($modules as $moduleLabel => $moduleName) {
			$instance = new self();
			$instance->name = $moduleName;
			$instance->label = $moduleLabel;
			$modulesModelsList[] = $instance;
		}
		return $modulesModelsList;
	}

	public static function getCleanInstance($moduleName)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $moduleName);
		$instance = new $modelClassName();
		return $instance;
	}

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams)
	{
		$linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
		$links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$quickLinks = [
				[
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			],
		];

		if ($userPrivilegesModel->hasModulePermission('Dashboard') && $userPrivilegesModel->hasModuleActionPermission($this->getId(), 'Dashboard')) {
			$quickLinks[] = [
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_DASHBOARD',
				'linkurl' => $this->getDashBoardUrl(),
				'linkicon' => '',
			];
		}

		$treeViewModel = Vtiger_TreeView_Model::getInstance($this);
		if ($treeViewModel->isActive()) {
			$quickLinks[] = [
				'linktype' => 'SIDEBARLINK',
				'linklabel' => $treeViewModel->getName(),
				'linkurl' => $treeViewModel->getTreeViewUrl(),
				'linkicon' => '',
			];
		}

		foreach ($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		$quickWidgets = array(
			array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_RECENTLY_MODIFIED',
				'linkurl' => 'module=' . $this->get('name') . '&view=IndexAjax&mode=showActiveRecords',
				'linkicon' => ''
			),
		);
		foreach ($quickWidgets as $quickWidget) {
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickWidget);
		}

		return $links;
	}

	/**
	 * Function returns export query - deprecated
	 * @param string $where
	 * @return string export query
	 */
	public function getExportQuery($focus, $where)
	{
		return $this->getEntityInstance()->create_export_query($where);
	}

	/**
	 * Function returns the default custom filter for the module
	 * @return <Int> custom filter id
	 */
	public function getDefaultCustomFilter()
	{
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT cvid FROM vtiger_customview WHERE setdefault = 1 && entitytype = ?", array($this->getName()));
		if ($db->num_rows($result)) {
			return $db->query_result($result, 0, 'cvid');
		}
		return false;
	}

	/**
	 * Function returns latest comments for the module
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getComments($pagingModel)
	{
		$comments = [];
		if (!$this->isCommentEnabled()) {
			return $comments;
		}
		$db = PearDatabase::getInstance();
		$accessConditions = \App\PrivilegeQuery::getAccessConditions('ModComments');
		$query = sprintf('SELECT vtiger_crmentity.*, vtiger_modcomments.* FROM vtiger_modcomments
			INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid
			INNER JOIN vtiger_crmentity crmentity2 ON vtiger_modcomments.related_to = crmentity2.crmid
			WHERE vtiger_crmentity.deleted = 0 && crmentity2.deleted = 0 && crmentity2.setype = ? %s
			ORDER BY vtiger_crmentity.createdtime DESC LIMIT ?, ?', $accessConditions);
		$result = $db->pquery($query, [$this->getName(), $pagingModel->getStartIndex(), $pagingModel->getPageLimit()]);
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$commentModel = Vtiger_Record_Model::getCleanInstance('ModComments');
			$commentModel->setData($row);
			$time = $commentModel->get('createdtime');
			$comments[$time] = $commentModel;
		}

		return $comments;
	}

	/**
	 * Function returns comments and recent activities across module
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @param string $type - comments, updates or all
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type = false)
	{
		if (empty($type)) {
			$type = 'all';
		}
		$comments = [];
		if ($type == 'all' || $type == 'comments') {
			$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
			if ($modCommentsModel->isPermitted('DetailView')) {
				$comments = $this->getComments($pagingModel);
			}
			if ($type == 'comments') {
				return $comments;
			}
		}

		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT vtiger_modtracker_basic.*
								FROM vtiger_modtracker_basic
								INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid
									AND deleted = 0 && module = ?
								ORDER BY vtiger_modtracker_basic.id DESC LIMIT ?, ?', array($this->getName(), $pagingModel->getStartIndex(), $pagingModel->getPageLimit()));

		$activites = [];
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			if (Users_Privileges_Model::isPermitted($row['module'], 'DetailView', $row['crmid'])) {
				$modTrackerRecorModel = new ModTracker_Record_Model();
				$modTrackerRecorModel->setData($row)->setParent($row['crmid'], $row['module']);
				$time = $modTrackerRecorModel->get('changedon');
				$activites[$time] = $modTrackerRecorModel;
			}
		}

		$history = array_merge($activites, $comments);

		$dateTime = [];
		foreach ($history as $time => $model) {
			$dateTime[] = $time;
		}

		if (!empty($history)) {
			array_multisort($dateTime, SORT_DESC, SORT_STRING, $history);
			return $history;
		}
		return false;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param string $mode - upcoming/overdue mode
	 * @param <Vtiger_Paging_Model> $pagingModel - $pagingModel
	 * @param string $user - all/userid
	 * @param string $recordId - record id
	 * @return <Array>
	 */
	public function getCalendarActivities($mode, $pagingModel, $user, $recordId = false)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$user) {
			$user = $currentUser->getId();
		}
		$moduleName = 'Calendar';
		$currentActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('current');
		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$referenceLinkClass = Vtiger_Loader::getComponentClassName('UIType', 'ReferenceLink', $moduleName);
		$referenceLinkInstance = new $referenceLinkClass();
		if (in_array($this->getName(), $referenceLinkInstance->getReferenceList())) {
			$relationField = 'link';
		} else {
			$referenceProcessClass = Vtiger_Loader::getComponentClassName('UIType', 'ReferenceProcess', $moduleName);
			$referenceProcessInstance = new $referenceProcessClass();
			if (in_array($this->getName(), $referenceProcessInstance->getReferenceList())) {
				$relationField = 'process';
			} else {
				$referenceSubProcessClass = Vtiger_Loader::getComponentClassName('UIType', 'ReferenceSubProcess', $moduleName);
				$referenceSubProcessInstance = new $referenceSubProcessClass();
				if (in_array($this->getName(), $referenceSubProcessInstance->getReferenceList())) {
					$relationField = 'subprocess';
				} else {
					throw new \Exception\AppException('LBL_HANDLER_NOT_FOUND');
				}
			}
		}
		$query = (new \App\Db\Query())->select(['vtiger_crmentity.crmid', 'parent_id' => 'crmentity2.crmid', 'description' => 'vtiger_crmentity.description',
				'vtiger_crmentity.smownerid', 'vtiger_crmentity.smcreatorid', 'vtiger_crmentity.setype', 'vtiger_activity.*'])
			->from('vtiger_activity')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')
			->innerJoin(['crmentity2' => 'vtiger_crmentity'], "vtiger_activity.$relationField = crmentity2.crmid AND crmentity2.deleted = :deleted AND crmentity2.setype = :module", [':deleted' => 0, ':module' => $this->getName()])
			->leftJoin('vtiger_groups', 'vtiger_groups.groupid = vtiger_crmentity.smownerid')
			->where(['vtiger_crmentity.deleted' => 0]);
		$andWhere = ['and'];
		if ($recordId) {
			$andWhere [] = ["vtiger_activity.$relationField" => $recordId];
		}
		if ($mode === 'current') {
			$andWhere [] = ['vtiger_activity.status' => $currentActivityLabels];
		} elseif ($mode === 'history') {
			$andWhere [] = ['not in', 'vtiger_activity.status', $currentActivityLabels];
		} elseif ($mode === 'upcoming') {
			$andWhere [] = ['and', ['or', ['vtiger_activity.status' => null], ['not in', 'vtiger_activity.status', ['Completed', 'Deferred']]], ['>=', 'due_date', $currentDate]];
		} elseif ($mode === 'overdue') {
			$andWhere [] = ['and', ['or', ['vtiger_activity.status' => null], ['not in', 'vtiger_activity.status', ['Completed', 'Deferred']]], ['<', 'due_date', $currentDate]];
		}
		if ($user != 'all' && $user != '') {
			if ($user === $currentUser->id) {
				$andWhere [] = ['vtiger_crmentity.smownerid' => $user];
			}
		}
		$query->andWhere($andWhere);
		App\PrivilegeQuery::getConditions($query, $moduleName, false, $recordId);
		$groupsIds = Vtiger_Util_Helper::getGroupsIdsForUsers($currentUser->getId());
		$dataReader = $query->createCommand()->query();
		$numOfRows = $dataReader->count();
		$activities = [];
		$visibleFields = ['activitytype', 'date_start', 'time_start', 'due_date', 'time_end', 'assigned_user_id', 'visibility', 'smownerid', 'crmid'];
		while ($row = $dataReader->read()) {
			$model = Vtiger_Record_Model::getCleanInstance('Calendar');
			$ownerId = $row['smownerid'];
			$visibility = true;
			if (in_array($ownerId, $groupsIds)) {
				$visibility = false;
			} else if ($ownerId == $currentUser->getId()) {
				$visibility = false;
			}
			if (!$currentUser->isAdminUser() && $row['activitytype'] != 'Task' && $row['visibility'] == 'Private' && $ownerId && $visibility) {
				foreach ($row as $data => $value) {
					if (in_array($data, $visibleFields) != -1) {
						unset($row[$data]);
					}
				}
				$row['subject'] = vtranslate('Busy', 'Events') . '*';
			}
			if ($row['activitytype'] == 'Task') {
				unset($row['visibility']);
			}
			$dataReaderSecond = (new \App\Db\Query())->select(['inviteesid'])->from('u_#__activity_invitation')->where(['activityid' => $row['crmid']])
					->createCommand()->query();
			while ($invite = $dataReaderSecond->readColumn(0)) {
				$row['selectedusers'][] = $invite;
			}
			$model->setData($row);
			$model->setId($row['crmid']);
			$activities[] = $model;
		}

		$pagingModel->calculatePageRange($numOfRows);
		if ($numOfRows > $pagingModel->getPageLimit()) {
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $activities;
	}

	/**
	 * Function to get list of fields which are required while importing records
	 * @param string $module
	 * @return <Array> list of fields
	 */
	public function getRequiredFields($module = '')
	{
		$moduleInstance = $this->getEntityInstance();
		$requiredFields = $moduleInstance->required_fields;
		if (empty($requiredFields)) {
			if (empty($module)) {
				$module = $this->getName();
			}
			$moduleInstance->initRequiredFields($module);
		}
		return $moduleInstance->required_fields;
	}

	public function getWidgets($module, $record)
	{
		return Settings_Widgets_Module_Model::getWidgets($module, $record);
	}

	/**
	 * Function to get the module is permitted to specific action
	 * @param string $actionName
	 * @return <boolean>
	 */
	public function isPermitted($actionName)
	{
		return ($this->isActive() && Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModuleActionPermission($this->getId(), $actionName));
	}

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule)
	{
		if ($relatedModule == 'Documents') {
			return ' AND vtiger_notes.filestatus = 1 ';
		}
		return;
	}

	/**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks()
	{
		if (!$this->isEntityModule()) {
			return [];
		}
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');

		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$editWorkflowsImagePath = Vtiger_Theme::getImagePath('EditWorkflows.png');
		$settingsLinks = [];

		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_EDIT_FIELDS',
			'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule=' . $this->getName(),
			'linkicon' => $layoutEditorImagePath
		);
		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_ARRANGE_RELATED_TABS',
			'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&mode=showRelatedListLayout&block=2&fieldid=41&sourceModule=' . $this->getName(),
			'linkicon' => $layoutEditorImagePath
		);
		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_QUICK_CREATE_EDITOR',
			'linkurl' => 'index.php?parent=Settings&module=QuickCreateEditor&sourceModule=' . $this->getName(),
			'linkicon' => $layoutEditorImagePath
		);
		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_TREES_MANAGER',
			'linkurl' => 'index.php?parent=Settings&module=TreesManager&view=List&sourceModule=' . $this->getName(),
			'linkicon' => $layoutEditorImagePath
		);
		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_WIDGETS_MANAGMENT',
			'linkurl' => 'index.php?parent=Settings&module=Widgets&view=Index&sourceModule=' . $this->getName(),
			'linkicon' => $layoutEditorImagePath
		);
		if (VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_WORKFLOWS',
				'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
				'linkicon' => $editWorkflowsImagePath
			);
		}

		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
			'linkurl' => 'index.php?parent=Settings&module=Picklist&view=Index&source_module=' . $this->getName(),
			'linkicon' => ''
		);
		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_PICKLIST_DEPENDENCY',
			'linkurl' => 'index.php?parent=Settings&module=PickListDependency&view=List&formodule=' . $this->getName(),
			'linkicon' => ''
		);
		if ($this->hasSequenceNumberField()) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_MODULE_SEQUENCE_NUMBERING',
				'linkurl' => 'index.php?parent=Settings&module=Vtiger&view=CustomRecordNumbering&sourceModule=' . $this->getName(),
				'linkicon' => ''
			);
		}

		$webformSupportedModule = Settings_Webforms_Module_Model :: getSupportedModulesList();
		if (array_key_exists($this->getName(), $webformSupportedModule)) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_SETUP_WEBFORMS',
				'linkurl' => 'index.php?module=Webforms&parent=Settings&view=Edit&sourceModule=' . $this->getName(),
				'linkicon' => '');
		}
		return $settingsLinks;
	}

	public function isCustomizable()
	{
		return $this->customized == '1' ? true : false;
	}

	public function isModuleUpgradable()
	{
		return $this->isCustomizable() ? true : false;
	}

	public function isExportable()
	{
		return $this->isCustomizable() ? true : false;
	}

	/**
	 * Function returns query for module record's search
	 * @param string $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param string $parentModule - parent module name
	 * @return string - query
	 */
	public function getSearchRecordsQuery($searchValue, $parentId = false, $parentModule = false)
	{
		$currentUser = \Users_Record_Model::getCurrentUserModel();
		return sprintf('SELECT `crmid`,`setype`,`searchlabel` FROM `u_yf_crmentity_search_label` WHERE `userid` LIKE \'%s\' && `searchlabel` LIKE \'%s\'', '%,' . $currentUser->getId() . ',%', "%$searchValue%");
	}

	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param string $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param string $parentModule - parent module name
	 * @return <Array of Vtiger_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId = false, $parentModule = false, $relatedModule = false)
	{
		if (empty($searchValue)) {
			return [];
		}
		if (empty($parentId) || empty($parentModule)) {
			$matchingRecords = Vtiger_Record_Model::getSearchResult($searchValue, $this->getName());
		} else if ($parentId && $parentModule) {
			$adb = PearDatabase::getInstance();
			$result = $adb->query($this->getSearchRecordsQuery($searchValue, $parentId, $parentModule));

			while ($row = $adb->getRow($result)) {
				$recordMeta = \vtlib\Functions::getCRMRecordMetadata($row['crmid']);
				$row['id'] = $row['crmid'];
				$row['smownerid'] = $recordMeta['smownerid'];
				$row['createdtime'] = $recordMeta['createdtime'];
				$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
				$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
				$recordInstance = new $modelClassName();
				$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
			}
		}
		return $matchingRecords;
	}

	/**
	 * Function returns the default column for Alphabetic search
	 * @return string columnname
	 */
	public function getAlphabetSearchField()
	{
		return $this->getEntityInstance()->def_basicsearch_col;
	}

	/**
	 * Function which will give complusory mandatory fields
	 * @return type
	 */
	public function getCumplosoryMandatoryFieldList()
	{
		$focus = $this->getEntityInstance();
		if (empty($focus->mandatory_fields)) {
			return [];
		}
		return $focus->mandatory_fields;
	}

	/**
	 * Function returns all the related modules for workflows create entity task
	 * @return <JSON>
	 */
	public function vtJsonDependentModules()
	{
		vimport('~modules/com_vtiger_workflow/WorkflowComponents.php');
		$db = PearDatabase::getInstance();
		$param = array('modulename' => $this->getName());
		return vtJsonDependentModules($db, $param);
	}

	/**
	 * Function returns mandatory field Models
	 * @return <Array of Vtiger_Field_Model>
	 */
	public function getMandatoryFieldModels()
	{
		$fields = $this->getFields();
		$mandatoryFields = [];
		if ($fields) {
			foreach ($fields as $field) {
				if ($field->isMandatory()) {
					$mandatoryFields[] = $field;
				}
			}
		}
		return $mandatoryFields;
	}

	/**
	 * Function to get orderby sql from orderby field
	 */
	public function getOrderBySql($orderBy)
	{
		$orderByField = $this->getFieldByColumn($orderBy);
		return $orderByField->get('table') . '.' . $orderBy;
	}

	public function getDefaultSearchField()
	{
		$nameFields = $this->getNameFields();
		//To make the first field as the name field
		return $nameFields[0];
	}

	/**
	 * Function to get popup view fields
	 * @param string|boolean $sourceModule
	 * @return string[]
	 */
	public function getPopupViewFieldsList($sourceModule = false)
	{
		if (App\Cache::staticHas('PopupViewFieldsList', $this->getName())) {
			return App\Cache::staticGet('PopupViewFieldsList', $this->getName());
		}
		$parentRecordModel = Vtiger_Module_Model::getInstance($sourceModule);
		if (!empty($sourceModule) && $parentRecordModel) {
			$relationModel = Vtiger_Relation_Model::getInstance($parentRecordModel, $this);
		}
		$popupFields = [];
		if ($relationModel) {
			foreach (App\Field::getFieldsFromRelation($relationModel->getId()) as &$fieldName) {
				$popupFields[$fieldName] = $fieldName;
			}
		}
		if (!$popupFields) {
			foreach ($this->getPopupFields() as &$fieldName) {
				$popupFields[$fieldName] = $fieldName;
			}
		}
		App\Cache::staticSave('PopupViewFieldsList', $this->getName(), $popupFields);
		return $popupFields;
	}

	/**
	 * Funxtion to identify if the module supports quick search or not
	 */
	public function isQuickSearchEnabled()
	{
		return true;
	}

	/**
	 * function to check if the extension module is permitted for utility action
	 * @return <boolean> false
	 */
	public function isUtilityActionEnabled()
	{
		return false;
	}

	public function isListViewNameFieldNavigationEnabled()
	{
		return true;
	}

	public function getValuesFromSource(Vtiger_Request $request, $moduleName = false)
	{
		$data = [];
		if (!$moduleName) {
			$moduleName = $request->getModule();
		}
		$sourceModule = $request->get('sourceModule');
		$sourceRecord = $request->get('sourceRecord');
		$sourceRecordData = $request->get('sourceRecordData');

		if ($sourceModule && ($sourceRecord || $sourceRecordData)) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			if (empty($sourceRecord)) {
				$recordModel = Vtiger_Record_Model::getCleanInstance($sourceModule);
				$recordModel->setData($sourceRecordData);
			} else {
				$recordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
			}
			$sourceModuleModel = $recordModel->getModule();
			$relationField = false;
			$fieldMap = [];

			$modelFields = $moduleModel->getFields();
			foreach ($modelFields as $fieldName => $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					if (!empty($referenceList)) {
						foreach ($referenceList as $referenceModule) {
							$fieldMap[$referenceModule] = $fieldName;
						}
						if (in_array($sourceModule, $referenceList)) {
							$relationField = $fieldName;
						}
					}
				}
			}
			$sourceModelFields = $sourceModuleModel->getFields();
			foreach ($sourceModelFields as $fieldName => $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					if (!empty($referenceList)) {
						foreach ($referenceList as $referenceModule) {
							if (isset($fieldMap[$referenceModule]) && $sourceModule != $referenceModule) {
								$fieldValue = $recordModel->get($fieldName);
								if ($fieldValue != 0 && vtlib\Functions::getCRMRecordType($fieldValue) == $referenceModule)
									$data[$fieldMap[$referenceModule]] = $fieldValue;
							}
						}
					}
				}
			}
			$mappingRelatedField = Vtiger_ModulesHierarchy_Model::getRelationFieldByHierarchy($moduleName);
			if (!empty($mappingRelatedField)) {
				foreach ($mappingRelatedField as $relatedModules) {
					foreach ($relatedModules as $relatedModule => $relatedFields) {
						if ($relatedModule == $sourceModule) {
							foreach ($relatedFields as $to => $from) {
								$fieldValue = $recordModel->get($from[0]);
								if (!empty($fieldValue)) {
									$data[$to] = $fieldValue;
								}
							}
						}
					}
				}
			}
			if ($relationField && ($moduleName != $sourceModule || AppRequest::get('addRelation'))) {
				$data[$relationField] = $sourceRecord;
			}
		}
		return $data;
	}
}
