<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * Vtiger Module Model Class.
 */
class Vtiger_Module_Model extends \vtlib\Module
{
	/** Standard module type */
	const STANDARD_TYPE = 0;
	/** Advanced module type */
	const ADVANCED_TYPE = 1;

	protected $blocks;
	protected $nameFields;
	protected $moduleMeta;
	protected $fields;
	protected $relations;
	protected $moduleType = '0';
	protected $entityInstance;
	/** @var bool */
	public $allowTypeChange = true;

	/**
	 * Function to get the Module/Tab id.
	 *
	 * @return <Number>
	 */
	public function getId()
	{
		return (int) $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	/**
	 * Function to check whether the module is an entity type module or not.
	 *
	 * @return bool
	 */
	public function isEntityModule()
	{
		return '1' == $this->isentitytype;
	}

	/**
	 * Function to check whether the module is enabled for quick create.
	 *
	 * @return bool
	 */
	public function isQuickCreateSupported()
	{
		return $this->isEntityModule() && !$this->isInventory() && \App\Privilege::isPermitted($this->getName(), 'CreateView');
	}

	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool
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
		return static::ADVANCED_TYPE === $this->getModuleType();
	}

	/**
	 * Function to get singluar label key.
	 *
	 * @return string - Singular module label key
	 */
	public function getSingularLabelKey()
	{
		return 'SINGLE_' . $this->getName();
	}

	/**
	 * Function to get the value of a given property.
	 *
	 * @param string $propertyName
	 *
	 * @throws Exception
	 *
	 * @return <Object>
	 */
	public function get($propertyName)
	{
		if (property_exists($this, $propertyName)) {
			return $this->{$propertyName};
		}
		throw new \App\Exceptions\AppException($propertyName . ' doest not exists in class ' . static::class);
	}

	/**
	 * Function to set the value of a given property.
	 *
	 * @param string   $propertyName
	 * @param <Object> $propertyValue
	 *
	 * @return Vtiger_Module_Model instance
	 */
	public function set($propertyName, $propertyValue)
	{
		$this->{$propertyName} = $propertyValue;

		return $this;
	}

	/**
	 * Function checks if the module is Active.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return \in_array($this->get('presence'), [0, 2]);
	}

	/**
	 * Function checks if the module is enabled for tracking changes.
	 *
	 * @return bool
	 */
	public function isTrackingEnabled()
	{
		require_once 'modules/ModTracker/ModTracker.php';
		$trackingEnabled = ModTracker::isTrackingEnabledForModule($this->getName());

		return $this->isActive() && $trackingEnabled;
	}

	/**
	 * Function checks if comment is enabled.
	 *
	 * @return bool
	 */
	public function isCommentEnabled()
	{
		$moduleName = $this->getName();
		$cacheName = 'isModuleCommentEnabled';
		if (!\App\Cache::has($cacheName, $moduleName)) {
			$moduleModel = self::getInstance('ModComments');
			$fieldModel = $moduleModel && $moduleModel->isActive() ? $moduleModel->getFieldByName('related_to') : null;
			$enabled = $fieldModel && \in_array($moduleName, $fieldModel->getReferenceList());
			\App\Cache::save($cacheName, $moduleName, $enabled, \App\Cache::LONG);
		}
		return \App\Cache::get($cacheName, $moduleName);
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name.
	 *
	 * @param int|string $mixed id or name of the module
	 *
	 * @return $this
	 */
	public static function getInstance($mixed)
	{
		if (is_numeric($mixed)) {
			$mixed = \App\Module::getModuleName($mixed);
		}
		if (\App\Cache::staticHas('module', $mixed)) {
			return \App\Cache::staticGet('module', $mixed);
		}
		$instance = false;
		$moduleObject = parent::getInstance($mixed);
		if ($moduleObject) {
			$instance = self::getInstanceFromModuleObject($moduleObject);
			\App\Cache::staticSave('module', $moduleObject->name, $instance);
		}
		return $instance;
	}

	/**
	 * Function to get the instance of Vtiger Module Model from a given vtlib\Module object.
	 *
	 * @param vtlib\Module $moduleObj
	 *
	 * @return $this
	 */
	public static function getInstanceFromModuleObject(vtlib\Module $moduleObj)
	{
		$objectProperties = get_object_vars($moduleObj);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $objectProperties['name']);
		$moduleModel = new $modelClassName();
		foreach ($objectProperties as $properName => $propertyValue) {
			$moduleModel->{$properName} = $propertyValue;
		}
		return $moduleModel;
	}

	/**
	 * Function to get the instance of Vtiger Module Model from a given list of key-value mapping.
	 *
	 * @param array $valueArray
	 *
	 * @return $this
	 */
	public static function getInstanceFromArray($valueArray)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $valueArray['name']);
		$instance = new $modelClassName();
		$instance->initialize($valueArray);
		return $instance;
	}

	/**
	 * Function to get the ListView Component Name.
	 *
	 * @return string
	 */
	public function getListViewName()
	{
		return 'List';
	}

	/**
	 * Function to get the DetailView Component Name.
	 *
	 * @return string
	 */
	public function getDetailViewName()
	{
		return 'Detail';
	}

	/**
	 * Function to get the EditView Component Name.
	 *
	 * @return string
	 */
	public function getEditViewName()
	{
		return 'Edit';
	}

	/**
	 * Function to get the DuplicateView Component Name.
	 *
	 * @return string
	 */
	public function getDuplicateViewName()
	{
		return 'Edit';
	}

	/**
	 * Function to get the Delete Action Component Name.
	 *
	 * @return string
	 */
	public function getDeleteActionName()
	{
		return 'Delete';
	}

	/**
	 * Function to get the Default View Component Name.
	 *
	 * @return string
	 */
	public function getDefaultViewName()
	{
		$viewName = App\Config::module($this->getName(), 'defaultViewName');
		if (!empty($viewName)) {
			return $viewName;
		}
		return 'List';
	}

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=' . $this->getDefaultViewName();
	}

	/**
	 * Function to get the url for list view of the module.
	 *
	 * @return string - url
	 */
	public function getListViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=' . $this->getListViewName();
	}

	/**
	 * Function to get the url for the Create Record view of the module.
	 *
	 * @return string - url
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=' . $this->getEditViewName();
	}

	/**
	 * Function to get the url for the Create Record view of the module.
	 *
	 * @return string - url
	 */
	public function getQuickCreateUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=QuickCreateAjax';
	}

	/**
	 * Function to get the url for the Import action of the module.
	 *
	 * @return string - url
	 */
	public function getImportUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=Import';
	}

	/**
	 * Function to get the url for the Export action of the module.
	 *
	 * @return string - url
	 */
	public function getExportUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=Export';
	}

	/**
	 * Function to get the url to view Dashboard for the module.
	 *
	 * @return string - url
	 */
	public function getDashBoardUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=DashBoard';
	}

	/**
	 * Function to get the url to view Details for the module.
	 *
	 * @param mixed $id
	 *
	 * @return string - url
	 */
	public function getDetailViewUrl($id)
	{
		return 'index.php?module=' . $this->getName() . '&view=' . $this->getDetailViewName() . '&record=' . $id;
	}

	/**
	 * Function to get a Vtiger Record Model instance from an array of key-value mapping.
	 *
	 * @param array $valueArray
	 *
	 * @return \Vtiger_Record_Model Record Model instance
	 */
	public function getRecordFromArray(array $valueArray): Vtiger_Record_Model
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $this->getName());
		$recordInstance = new $modelClassName();
		return $recordInstance->setData($valueArray)->setModuleFromInstance($this);
	}

	/**
	 * Function returns all the blocks for the module.
	 *
	 * @return Vtiger_Block_Model[] - list of block models
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
	 * Function that returns all the fields for the module.
	 *
	 * @param mixed $blockInstance
	 *
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
	 * Function that returns all the fields by blocks.
	 *
	 * @return array
	 */
	public function getFieldsByBlocks()
	{
		$fieldList = [];
		foreach ($this->getFields() as &$field) {
			$fieldList[$field->getBlockName()][$field->getName()] = $field;
		}
		return $fieldList;
	}

	/**
	 * Function to get the field mode, the function creates a new object and does not pass a reference.
	 *
	 * @param string $fieldName - field name or field id
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getField($fieldName)
	{
		return Vtiger_Field_Model::getInstance($fieldName, $this);
	}

	/**
	 * Function to get the field by column name.
	 *
	 * @param string $columnName - column name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldByColumn($columnName)
	{
		foreach ($this->getFields() as &$field) {
			if ($field->get('column') === $columnName) {
				return $field;
			}
		}
		return null;
	}

	/**
	 * Get field by field name.
	 *
	 * @param string $fieldName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return Vtiger_Field_Model|false
	 */
	public function getFieldByName($fieldName)
	{
		return $this->getFields()[$fieldName] ?? false;
	}

	/**
	 * Function gives fields based on the type.
	 *
	 * @param string|string[] $type   - field type
	 * @param bool            $active
	 *
	 * @return Vtiger_Field_Model[] - list of field models `fieldName => Vtiger_Field_Model`
	 */
	public function getFieldsByType($type, bool $active = false): array
	{
		if (!\is_array($type)) {
			$type = [$type];
		}
		$fieldList = [];
		foreach ($this->getFields() as $field) {
			if (\in_array($field->getFieldDataType(), $type) && (!$active || ($active && $field->isActiveField()))) {
				$fieldList[$field->getName()] = $field;
			}
		}
		return $fieldList;
	}

	public function getFieldsByReference()
	{
		$fieldList = [];
		foreach ($this->getFields() as $field) {
			if ($field->isReferenceField()) {
				$fieldList[$field->getName()] = $field;
			}
		}
		return $fieldList;
	}

	/**
	 * Gets reference fields for module.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public function getReferenceFieldsForModule(string $moduleName): array
	{
		$fieldList = [];
		foreach ($this->getFields() as $field) {
			if ($field->isActiveField() && $field->isReferenceField() && \in_array($moduleName, $field->getReferenceList())) {
				$fieldList[$field->getName()] = $field;
			}
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the uitype.
	 *
	 * @param mixed $uitype
	 *
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
	 * Function gives fields based on the type.
	 *
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
	 * Function gives fields based on the fieldid.
	 *
	 * @return Vtiger_Field_Model[] with field id as key
	 */
	public function getFieldsById()
	{
		$fieldList = [];
		foreach ($this->getFields() as &$field) {
			$fieldList[$field->getId()] = $field;
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the type.
	 *
	 * @param mixed $type
	 *
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
	 * Function gives list fields for save.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return array
	 */
	public function getFieldsForSave(Vtiger_Record_Model $recordModel)
	{
		$editFields = [];
		foreach (App\Field::getFieldsPermissions($this->getId(), false) as $field) {
			$editFields[] = $field['fieldname'];
		}
		return array_diff($editFields, ['shownerid', 'smcreatorid', 'modifiedtime', 'modifiedby']);
	}

	/**
	 * Function to get list of field for summary view.
	 *
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
	 * Function that returns all the quickcreate fields for the module.
	 *
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getQuickCreateFields()
	{
		$quickCreateFieldList = [];
		foreach ($this->getFieldsByBlocks() as $blockFields) {
			uksort($blockFields, function ($a, $b) use ($blockFields) {
				if ($blockFields[$a]->get('quicksequence') === $blockFields[$b]->get('quicksequence')) {
					return 0;
				}
				return $blockFields[$a]->get('quicksequence') < $blockFields[$b]->get('quicksequence') ? -1 : 1;
			});
			foreach ($blockFields as $fieldName => $fieldModel) {
				if ($fieldModel->isQuickCreateEnabled() && $fieldModel->isEditable()) {
					$quickCreateFieldList[$fieldName] = $fieldModel;
				}
			}
		}
		return $quickCreateFieldList;
	}

	/**
	 * Function returns all the relation models.
	 *
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
	 * Function to retrieve name fields of a module.
	 *
	 * @return array - array which contains fields which together construct name fields
	 */
	public function getNameFields()
	{
		$entityInfo = App\Module::getEntityInfo($this->getName());
		$fieldsName = [];
		if ($entityInfo) {
			foreach ($entityInfo['fieldnameArr'] as $columnName) {
				$fieldsName[] = $this->getFieldByColumn($columnName)->getName();
			}
		}
		return $fieldsName;
	}

	/**
	 * Funtion that returns fields that will be showed in the record selection popup.
	 *
	 * @return <Array of fields>
	 */
	public function getPopupFields()
	{
		if (!empty($this->getEntityInstance()->search_fields_name)) {
			return $this->getEntityInstance()->search_fields_name;
		}
		$queryGenerator = new \App\QueryGenerator($this->getName());
		$queryGenerator->initForDefaultCustomView(true, true);
		return $queryGenerator->getFields();
	}

	public function isWorkflowSupported()
	{
		if ($this->isEntityModule()) {
			return true;
		}
		return false;
	}

	/**
	 * Function checks if a module has module sequence numbering.
	 *
	 * @return bool
	 */
	public function hasSequenceNumberField()
	{
		if (!empty($this->fields)) {
			foreach ($this->getFields() as $fieldModel) {
				if (4 === $fieldModel->getUIType()) {
					return true;
				}
			}
		} else {
			return (bool) \App\Fields\RecordNumber::getSequenceNumberFieldName($this->getId());
		}
		return false;
	}

	/**
	 * Get sequence number field name.
	 *
	 * @return string|bool
	 */
	public function getSequenceNumberFieldName()
	{
		if (!empty($this->fields)) {
			foreach ($this->getFields() as $fieldModel) {
				if (4 === $fieldModel->getUIType() && $fieldModel->isActiveField()) {
					return $fieldModel->getName();
				}
			}
		} else {
			return \App\Fields\RecordNumber::getSequenceNumberFieldName($this->getId());
		}
		return false;
	}

	/**
	 * Function to get all modules from CRM.
	 *
	 * @param <array> $presence
	 * @param <array> $restrictedModulesList
	 * @param mixed   $isEntityType
	 *
	 * @return <array> List of module models Vtiger_Module_Model
	 */
	public static function getAll($presence = [], $restrictedModulesList = [], $isEntityType = false)
	{
		$allModules = \vtlib\Functions::getAllModules($isEntityType, true);
		$moduleModels = [];
		foreach ($allModules as &$row) {
			$moduleModels[$row['tabid']] = self::getInstanceFromArray($row);
		}
		if ($presence && $moduleModels) {
			foreach ($moduleModels as $key => $moduleModel) {
				if (!\in_array($moduleModel->get('presence'), $presence)) {
					unset($moduleModels[$key]);
				}
			}
		}
		if ($restrictedModulesList && $moduleModels) {
			foreach ($moduleModels as $key => $moduleModel) {
				if (\in_array($moduleModel->getName(), $restrictedModulesList)) {
					unset($moduleModels[$key]);
				}
			}
		}
		return $moduleModels;
	}

	/**
	 * Get entity instance.
	 *
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
		$moduleModels = Vtiger_Cache::get('vtiger', 'EntityModules');
		if (!$moduleModels) {
			$presence = [0, 2];
			$moduleModels = self::getAll($presence);
			$restrictedModules = ['Integration', 'Dashboard'];
			foreach ($moduleModels as $key => $moduleModel) {
				if (\in_array($moduleModel->getName(), $restrictedModules) || 1 != $moduleModel->get('isentitytype')) {
					unset($moduleModels[$key]);
				}
			}
			Vtiger_Cache::set('vtiger', 'EntityModules', $moduleModels);
		}
		return $moduleModels;
	}

	/**
	 * Get model instance for given module.
	 *
	 * @param string $moduleName
	 *
	 * @return $this
	 */
	public static function getCleanInstance(string $moduleName)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $moduleName);
		return new $modelClassName();
	}

	/**
	 * Function to get the Quick Links for the module.
	 *
	 * @param array $linkParams
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getSideBarLinks($linkParams)
	{
		$menuUrl = '';
		if (isset($_REQUEST['parent'])) {
			$menuUrl .= '&parent=' . \App\Request::_getByType('parent', 'Alnum');
		}
		if (isset($_REQUEST['mid'])) {
			$menuUrl .= '&mid=' . \App\Request::_getInteger('mid');
		}
		$links = Vtiger_Link_Model::getAllByType($this->getId(), ['SIDEBARLINK', 'SIDEBARWIDGET'], $linkParams);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_RECORDS_LIST',
			'linkurl' => $this->getListViewUrl() . $menuUrl,
			'linkicon' => 'fas fa-list',
		]);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_RECORDS_PREVIEW_LIST',
			'linkurl' => "index.php?module={$this->getName()}&view=ListPreview{$menuUrl}",
			'linkicon' => 'far fa-list-alt',
		]);
		if ($userPrivilegesModel->hasModuleActionPermission($this->getId(), 'Dashboard')) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_DASHBOARD',
				'linkurl' => $this->getDashBoardUrl() . $menuUrl,
				'linkicon' => 'fas fa-desktop',
			]);
		}
		$treeViewModel = Vtiger_TreeView_Model::getInstance($this->getName());
		if ($treeViewModel->isActive()) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => $treeViewModel->getName(),
				'linkurl' => $treeViewModel->getTreeViewUrl() . $menuUrl,
				'linkicon' => 'yfi-tree-records',
			]);
		}
		if ($this->isPermitted('Kanban') && \App\Utils\Kanban::getBoards($this->getName(), true)) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_VIEW_KANBAN',
				'linkurl' => 'index.php?module=' . $this->getName() . '&view=Kanban' . $menuUrl,
				'linkicon' => 'yfi yfi-kanban',
			]);
		}
		if ($this->isPermitted('TilesView')) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_TILES_VIEW',
				'linkurl' => "index.php?module={$this->getName()}&view=Tiles{$menuUrl}",
				'linkicon' => 'far fa-list-alt',
			]);
		}
		return $links;
	}

	/**
	 * Function returns latest comments for the module.
	 *
	 * @param <Vtiger_Paging_Model> $pagingModel
	 *
	 * @return <Array>
	 */
	public function getComments($pagingModel)
	{
		$comments = [];
		if (!$this->isCommentEnabled()) {
			return $comments;
		}
		$query = (new \App\Db\Query())->select(['vtiger_crmentity.setype', 'vtiger_modcomments.related_to', 'vtiger_modcomments.commentcontent', 'vtiger_crmentity.createdtime', 'assigned_user_id' => 'vtiger_crmentity.smownerid',
			'parentId' => 'crmentity2.crmid', 'parentModule' => 'crmentity2.setype', ])
			->from('vtiger_modcomments')
			->innerJoin('vtiger_crmentity', 'vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_crmentity crmentity2', 'vtiger_modcomments.related_to = crmentity2.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'crmentity2.setype' => $this->getName(), 'crmentity2.deleted' => 0]);
		\App\PrivilegeQuery::getConditions($query, 'ModComments');
		$dataReader = $query->orderBy(['vtiger_modcomments.modcommentsid' => SORT_DESC])
			->limit($pagingModel->getPageLimit())
			->offset($pagingModel->getStartIndex())
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (\App\Privilege::isPermitted($row['setype'], 'DetailView', $row['related_to'])) {
				$commentModel = Vtiger_Record_Model::getCleanInstance('ModComments');
				$commentModel->setData($row);
				$time = $commentModel->get('createdtime');
				$comments[$time] = $commentModel;
			}
		}
		$dataReader->close();

		return $comments;
	}

	/**
	 * Function returns comments and recent activities across module.
	 *
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @param string                $type        - comments, updates or all
	 *
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type = false)
	{
		if (empty($type)) {
			$type = 'all';
		}
		$comments = [];
		if ('all' == $type || 'comments' == $type) {
			$modCommentsModel = self::getInstance('ModComments');
			if ($modCommentsModel->isPermitted('DetailView')) {
				$comments = $this->getComments($pagingModel);
			}
			if ('comments' == $type) {
				return $comments;
			}
		}
		$dataReader = (new App\Db\Query())->select(['vtiger_modtracker_basic.*'])
			->from('vtiger_modtracker_basic')
			->innerJoin('vtiger_crmentity', 'vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_modtracker_basic.module' => $this->getName()])
			->orderBy(['vtiger_modtracker_basic.id' => SORT_DESC])
			->offset($pagingModel->getStartIndex())
			->limit($pagingModel->getPageLimit())
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (\App\Privilege::isPermitted($row['module'], 'DetailView', $row['crmid'])) {
				$modTrackerRecorModel = new ModTracker_Record_Model();
				$modTrackerRecorModel->setData($row)->setParent($row['crmid'], $row['module']);
				$time = $modTrackerRecorModel->get('changedon');
				$activites[$time] = $modTrackerRecorModel;
			}
		}
		$dataReader->close();
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
	 * Getting Widgets.
	 *
	 * @param string|null $module
	 *
	 * @return array
	 */
	public function getWidgets(?string $module = null): array
	{
		return Settings_Widgets_Module_Model::getWidgets($module ?? $this->getName());
	}

	/**
	 * Function to get the module is permitted to specific action.
	 *
	 * @param string $actionName
	 *
	 * @return bool
	 */
	public function isPermitted($actionName)
	{
		return $this->isActive() && Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModuleActionPermission($this->getId(), $actionName);
	}

	/**
	 * Get settings links,will be shown in the panel ModuleManager.
	 *
	 * @return string[]
	 */
	public function getSettingLinks(): array
	{
		if (!$this->isEntityModule()) {
			return [];
		}
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		$settingsLinks = [];
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_EDIT_FIELDS',
			'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule=' . $this->getName(),
			'linkicon' => 'adminIcon-modules-fields',
		];
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_ARRANGE_RELATED_TABS',
			'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&mode=showRelatedListLayout&block=2&fieldid=41&sourceModule=' . $this->getName(),
			'linkicon' => 'adminIcon-modules-relations',
		];
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_QUICK_CREATE_EDITOR',
			'linkurl' => 'index.php?parent=Settings&module=QuickCreateEditor&sourceModule=' . $this->getName(),
			'linkicon' => 'adminIcon-fields-quick-create',
		];
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_TREES_MANAGER',
			'linkurl' => 'index.php?parent=Settings&module=TreesManager&view=List&sourceModule=' . $this->getName(),
			'linkicon' => 'adminIcon-field-folders',
		];
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_WIDGETS_MANAGMENT',
			'linkurl' => 'index.php?parent=Settings&module=Widgets&view=Index&sourceModule=' . $this->getName(),
			'linkicon' => 'adminIcon-modules-widgets',
		];
		if (\App\Security\AdminAccess::isPermitted('Workflows') && VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_WORKFLOWS',
				'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
				'linkicon' => 'adminIcon-triggers',
			];
		}
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
			'linkurl' => 'index.php?parent=Settings&module=Picklist&view=Index&source_module=' . $this->getName(),
			'linkicon' => 'adminIcon-fields-picklists',
		];
		$settingsLinks[] = [
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_PICKLIST_DEPENDENCY',
			'linkurl' => 'index.php?parent=Settings&module=PickListDependency&view=List&forModule=' . $this->getName(),
			'linkicon' => 'adminIcon-fields-picklists-relations',
		];
		foreach ($settingsLinks as $key => $data) {
			$moduleName = \vtlib\Functions::getQueryParams($data['linkurl'])['module'] ?? null;
			if (!$moduleName || !\App\Security\AdminAccess::isPermitted($moduleName)) {
				unset($settingsLinks[$key]);
			}
		}
		if (\App\Security\AdminAccess::isPermitted('RecordNumbering') && $this->hasSequenceNumberField()) {
			$settingsLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_MODULE_SEQUENCE_NUMBERING',
				'linkurl' => 'index.php?parent=Settings&module=RecordNumbering&view=CustomRecordNumbering&sourceModule=' . $this->getName(),
				'linkicon' => 'fas fa-exchange-alt',
			];
		}
		return $settingsLinks;
	}

	/**
	 * Function searches the records in the module.
	 * Mainly used in reference fields for autocomplete mechanisms.
	 *
	 * @param string $searchValue
	 * @param int    $limit
	 * @param int    $srcRecord
	 *
	 * @return App\QueryGenerator
	 */
	public function getQueryForRecords(string $searchValue, int $limit, int $srcRecord = null): App\QueryGenerator
	{
		$queryGenerator = \App\RecordSearch::getQueryByModule($searchValue, $this->getName(), $limit);
		if ($srcRecord && \App\Record::getType($srcRecord) === $this->getName()) {
			$queryGenerator->addCondition('id', $srcRecord, 'n');
		}
		return $queryGenerator;
	}

	/**
	 * Function returns the default column for Alphabetic search.
	 *
	 * @return string columnname
	 */
	public function getAlphabetSearchField()
	{
		return $this->getEntityInstance()->def_basicsearch_col;
	}

	/**
	 * Function returns mandatory field Models.
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public function getMandatoryFieldModels()
	{
		$mandatoryFields = [];
		if ($fieldsArray = $this->getFields()) {
			foreach ($fieldsArray as $field) {
				if ($field->isActiveField() && $field->isMandatory()) {
					$mandatoryFields[$field->getName()] = $field;
				}
			}
		}
		return $mandatoryFields;
	}

	/**
	 * Function to get orderby sql from orderby field.
	 *
	 * @param mixed $orderBy
	 */
	public function getOrderBySql($orderBy)
	{
		$orderByField = $this->getFieldByColumn($orderBy);

		return $orderByField->get('table') . '.' . $orderBy;
	}

	/**
	 * Function to get modal records list view fields.
	 *
	 * @param \App\QueryGenerator $queryGenerator
	 * @param bool|string         $sourceModule
	 *
	 * @return string[]
	 */
	public function getModalRecordsListFields(App\QueryGenerator $queryGenerator, $sourceModule = false)
	{
		if (App\Cache::staticHas('PopupViewFieldsList', $this->getName())) {
			$popupFields = App\Cache::staticGet('PopupViewFieldsList', $this->getName());
		} else {
			$popupFields = [];
			if (!empty($sourceModule) && ($parentModuleModel = self::getInstance($sourceModule))) {
				$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $this);
				if ($relationModel) {
					foreach (App\Field::getFieldsFromRelation($relationModel->getId()) as $fieldName) {
						$popupFields[$fieldName] = $fieldName;
					}
				}
				if (!$popupFields) {
					foreach ($this->getPopupFields() as $fieldName) {
						$popupFields[$fieldName] = $fieldName;
					}
					$popupFields = $parentModuleModel->getModalRecordsListSourceFields($queryGenerator, $this, $popupFields);
				}
			}
			$popupFields[] = 'id';
			App\Cache::staticSave('PopupViewFieldsList', $this->getName(), $popupFields);
		}
		$queryGenerator->setFields($popupFields);
		return $popupFields;
	}

	/**
	 * Function to get modal records list view fields by source.
	 *
	 * @param \App\QueryGenerator $queryGenerator
	 * @param Vtiger_Module_Model $baseModule
	 * @param string[]            $popupFields
	 *
	 * @return string[]
	 */
	public function getModalRecordsListSourceFields(App\QueryGenerator $queryGenerator, self $baseModule, $popupFields)
	{
		return $popupFields;
	}

	/**
	 * Function to identify if the module supports quick search or not.
	 *
	 * @return bool
	 */
	public function isQuickSearchEnabled(): bool
	{
		return true;
	}

	/**
	 * Function to identify if the module supports sort or not.
	 *
	 * @return bool
	 */
	public function isAdvSortEnabled(): bool
	{
		return true;
	}

	/**
	 * The function determines whether the module custom view supports advanced conditions.
	 *
	 * @return bool
	 */
	public function isCustomViewAdvCondEnabled(): bool
	{
		return $this->isPermitted('CustomViewAdvCond');
	}

	/**
	 * function to check if the extension module is permitted for utility action.
	 *
	 * @return bool
	 */
	public function isUtilityActionEnabled()
	{
		return false;
	}

	public function isListViewNameFieldNavigationEnabled()
	{
		return true;
	}

	public function getValuesFromSource(App\Request $request, $moduleName = false)
	{
		$data = [];
		if (!$moduleName) {
			$moduleName = $request->getModule();
		}
		$sourceModule = $request->getByType('sourceModule', 2);
		if ($sourceModule && ($request->has('sourceRecord') || !$request->isEmpty('sourceRecordData'))) {
			$moduleModel = self::getInstance($moduleName);
			if ($request->isEmpty('sourceRecord')) {
				$sourceRecordData = $request->getRaw('sourceRecordData');
				$recordModel = Vtiger_Record_Model::getCleanInstance($sourceModule);
				$fieldModelList = $recordModel->getModule()->getFields();
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if (!$fieldModel->isWritable()) {
						continue;
					}
					if (isset($sourceRecordData[$fieldName])) {
						$fieldModel->getUITypeModel()->setValueFromRequest(new \App\Request($sourceRecordData, false), $recordModel);
					} else {
						$defaultValue = $fieldModel->getDefaultFieldValue();
						if ('' !== $defaultValue) {
							$recordModel->set($fieldName, $defaultValue);
						}
					}
				}
			} else {
				$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'), $sourceModule);
			}
			$sourceModuleModel = $recordModel->getModule();
			$relationField = false;
			$fieldMap = [];

			$modelFields = $moduleModel->getFields();
			foreach ($modelFields as $fieldName => $fieldModel) {
				if ($fieldModel->isReferenceField() && $fieldModel->isViewable()) {
					$referenceList = $fieldModel->getReferenceList();
					if (!empty($referenceList)) {
						foreach ($referenceList as $referenceModule) {
							$fieldMap[$referenceModule] = $fieldName;
						}
						if (\in_array($sourceModule, $referenceList)) {
							$relationField = $fieldName;
						}
					}
				}
			}

			$sourceModelFields = $sourceModuleModel->getFields();
			$fillFields = 'all' === $request->getRaw('fillFields');
			foreach ($sourceModelFields as $fieldName => $fieldModel) {
				if (!$fieldModel->isViewable()) {
					continue;
				}
				if ($fillFields) {
					$fieldValue = $recordModel->get($fieldName);
					if ('' !== $fieldValue) {
						$data[$fieldName] = $fieldValue;
					}
				} elseif ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					if (!empty($referenceList)) {
						foreach ($referenceList as $referenceModule) {
							if (isset($fieldMap[$referenceModule]) && $sourceModule != $referenceModule) {
								$fieldValue = $recordModel->get($fieldName);
								if (0 != $fieldValue && empty($data[$fieldMap[$referenceModule]]) && \App\Record::getType($fieldValue) == $referenceModule) {
									$data[$fieldMap[$referenceModule]] = $fieldValue;
								}
							}
						}
					}
				}
			}
			$mappingRelatedField = \App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName);
			if (!empty($mappingRelatedField)) {
				foreach ($mappingRelatedField as $relatedModules) {
					foreach ($relatedModules as $relatedModule => $relatedFields) {
						if ($relatedModule == $sourceModule) {
							foreach ($relatedFields as $to => $from) {
								$fieldValue = $recordModel->get($from[0]);
								if ($recordModel->getField($from[0])->isViewable() && '' !== $fieldValue) {
									$data[$to] = $fieldValue;
								}
							}
						}
					}
				}
			}
			if ($relationField && ($moduleName != $sourceModule || \App\Request::_get('addRelation'))) {
				$data[$relationField] = $recordModel->getId();
			}
		}
		return $data;
	}

	/**
	 * Function changes the module type.
	 *
	 * @param int $type
	 *
	 * @return bool
	 */
	public function changeType(int $type): bool
	{
		$result = false;
		if ($this->isTypeChangeAllowed() && $type !== $this->getModuleType() && \in_array($type, [static::ADVANCED_TYPE, static::STANDARD_TYPE])) {
			$result = \App\Db::getInstance()->createCommand()->update('vtiger_tab', ['type' => $type], ['name' => $this->getName()])->execute();
			if ($result && $type === static::ADVANCED_TYPE) {
				Vtiger_Inventory_Model::getInstance($this->getName())->createInventoryTables();
			}
			$tabId = \App\Module::getModuleId($this->getName());
			\App\Cache::delete('moduleTabByName', $this->getName());
			\App\Cache::delete('moduleTabById', $tabId);
			\App\Cache::delete('moduleTabs', 'all');
			\App\Cache::staticDelete('module', $this->getName());
			\App\Cache::staticDelete('module', $tabId);
			$this->type = $type;
		}
		return $result;
	}

	/**
	 * Check if change module type is supported.
	 *
	 * @return bool
	 */
	public function isTypeChangeAllowed(): bool
	{
		return $this->allowTypeChange || static::ADVANCED_TYPE === $this->getModuleType();
	}

	/**
	 * Get layout type for quick create.
	 *
	 * @return string
	 */
	public function getLayoutTypeForQuickCreate(): string
	{
		return \App\Config::performance('quickCreateLayout', 'blocks');
	}

	/**
	 * Clear cache.
	 *
	 * @return void
	 */
	public function clearCache(): void
	{
		$this->fields = null;
	}

	/**
	 * Get custom link label.
	 *
	 * @param int    $id
	 * @param string $label
	 *
	 * @return string
	 */
	public function getCustomLinkLabel(int $id, string $label): string
	{
		return \App\Purifier::encodeHtml($label);
	}
}
