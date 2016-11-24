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

class Vtiger_Relation_Model extends Vtiger_Base_Model
{

	protected static $cachedInstances = [];
	protected $parentModule = false;
	protected $relatedModule = false;

	//one to many
	const RELATION_O2M = 1;
	//Many to many and many to one
	const RELATION_M2M = 2;
	const RELATIONS_O2M = ['getDependentsList'];

	/**
	 * Function returns the relation id
	 * @return int
	 */
	public function getId()
	{
		return $this->get('relation_id');
	}

	/**
	 * Function sets the relation's parent module model
	 * @param Vtiger_Module_Model $moduleModel
	 * @return Vtiger_Relation_Model
	 */
	public function setParentModuleModel($moduleModel)
	{
		$this->parentModule = $moduleModel;
		return $this;
	}

	/**
	 * Function that returns the relation's parent module model
	 * @return Vtiger_Module_Model
	 */
	public function getParentModuleModel()
	{
		if (empty($this->parentModule)) {
			$this->parentModule = Vtiger_Module_Model::getInstance($this->get('tabid'));
		}
		return $this->parentModule;
	}

	/**
	 * Set relation's parent module model
	 * @param Vtiger_Module_Model $relationModel
	 * @return $this
	 */
	public function setRelationModuleModel($relationModel)
	{
		$this->relatedModule = $relationModel;
		return $this;
	}

	/**
	 * Function that returns the relation's related module model
	 * @return Vtiger_Module_Model
	 */
	public function getRelationModuleModel()
	{
		if (!$this->relatedModule) {
			$this->relatedModule = Vtiger_Module_Model::getInstance($this->get('related_tabid'));
		}
		return $this->relatedModule;
	}

	/**
	 * Get relation module name
	 * @return string
	 */
	public function getRelationModuleName()
	{
		$relationModuleName = $this->get('relatedModuleName');
		if (!empty($relationModuleName)) {
			return $relationModuleName;
		}
		return $this->getRelationModuleModel()->getName();
	}

	/**
	 * Get actions
	 * @return string[]
	 */
	public function getActions()
	{
		if (is_array($this->get('actions'))) {
			return $this->get('actions');
		}
		// No actions for Activity history
		if ($this->get('c') === 'Activity History') {
			return [];
		}
		$actions = explode(',', strtolower($this->get('actions')));
		$this->set('actions', $actions);
		return $actions;
	}

	/**
	 * Check if action is supported
	 * @param string $actionName
	 * @return boolean
	 */
	public function isActionSupported($actionName)
	{
		return in_array(strtolower($actionName), $this->getActions());
	}

	/**
	 * Is record selection action available
	 * @return boolean
	 */
	public function isSelectActionSupported()
	{
		return $this->isActionSupported('select');
	}

	/**
	 * Is record add action available
	 * @return boolean
	 */
	public function isAddActionSupported()
	{
		return $this->isActionSupported('add');
	}

	/**
	 * Show user who created relation
	 * @return boolean
	 */
	public function showCreatorDetail()
	{
		if ($this->get('creator_detail') === 0 || $this->getRelationType() !== self::RELATION_M2M) {
			return false;
		}

		return (bool) $this->get('creator_detail');
	}

	/**
	 * Show comments in related module
	 * @return boolean
	 */
	public function showComment()
	{
		if ($this->get('relation_comment') === 0 || $this->getRelationType() !== self::RELATION_M2M) {
			return false;
		}
		return (bool) $this->get('relation_comment');
	}

	/**
	 * Get query generator instance
	 * @return \App\QueryGenerator
	 */
	public function getQueryGenerator()
	{
		if (!$this->has('query_generator')) {
			$this->set('query_generator', new \App\QueryGenerator($this->getRelationModuleName()));
		}
		return $this->get('query_generator');
	}

	/**
	 * Get relation type
	 * @return self::RELATION_O2M|self::RELATION_M2M
	 */
	public function getRelationType()
	{
		if (!$this->get('relationType')) {
			if (in_array($this->get('name'), self::RELATIONS_O2M) || $this->getRelationField()) {
				$this->set('relationType', self::RELATION_O2M);
			} else {
				$this->set('relationType', self::RELATION_M2M);
			}
		}
		return $this->get('relationType');
	}

	/**
	 * Get relation list model instance
	 * @param Vtiger_Module_Model $parentModuleModel
	 * @param Vtiger_Module_Model $relatedModuleModel
	 * @param string|boolean $label
	 * @return \self|boolean
	 */
	public static function getInstance($parentModuleModel, $relatedModuleModel, $label = false)
	{
		$relKey = $parentModuleModel->getId() . '_' . $relatedModuleModel->getId() . '_' . ($label ? 1 : 0);
		if (isset(self::$cachedInstances[$relKey])) {
			return self::$cachedInstances[$relKey];
		}
		if (($relatedModuleModel->getName() == 'ModComments' && $parentModuleModel->isCommentEnabled()) || $parentModuleModel->getName() == 'Documents') {
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			if (method_exists($relationModel, 'setExceptionData')) {
				$relationModel->setExceptionData();
			}
			self::$cachedInstances[$relKey] = $relationModel;
			return $relationModel;
		}
		$query = (new \App\Db\Query())->select('vtiger_relatedlists.*, vtiger_tab.name as modulename')
			->from('vtiger_relatedlists')
			->innerJoin('vtiger_tab', 'vtiger_relatedlists.related_tabid = vtiger_tab.tabid')
			->where(['vtiger_relatedlists.tabid' => $parentModuleModel->getId(), 'related_tabid' => $relatedModuleModel->getId()])
			->andWhere(['<>', 'vtiger_tab.presence', 1]);
		if (!empty($label)) {
			$query->andWhere(['label' => $label]);
		}
		$row = $query->one();
		if ($row) {
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			self::$cachedInstances[$relKey] = $relationModel;
			return $relationModel;
		}
		return false;
	}

	/**
	 * Get query form relation
	 * @return \App\QueryGenerator
	 * @throws \Exception\NotAllowedMethod
	 */
	public function getQuery()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->setSourceRecord($this->get('parentRecord')->getId());
		$functionName = $this->get('name');
		if (method_exists($this, $functionName)) {
			$this->$functionName();
		} else {
			App\Log::error("Not exist relation: $functionName in " . __METHOD__);
			throw new \Exception\NotAllowedMethod('LBL_NOT_EXIST_RELATION: ' . $functionName);
		}
		if ($this->showCreatorDetail()) {
			$queryGenerator->setCustomColumn('rel_created_user');
			$queryGenerator->setCustomColumn('rel_created_time');
		}
		if ($this->showComment()) {
			$queryGenerator->setCustomColumn('rel_comment');
		}
		$fields = array_keys($this->getQueryFields());
		$fields[] = 'id';
		$queryGenerator->setFields($fields);
		return $queryGenerator;
	}

	/**
	 * Get query fields
	 * @return Vtiger_Field_Model[] with field name as key
	 */
	public function getQueryFields()
	{
		if ($this->has('QueryFields')) {
			return $this->get('QueryFields');
		}
		$relatedListFields = [];
		$relatedModuleModel = $this->getRelationModuleModel();
		// Get fields from panel
		foreach (App\Field::getFieldsFromRelation($this->getId()) as &$fieldName) {
			$relatedListFields[$fieldName] = $relatedModuleModel->getFieldByName($fieldName);
		}
		if ($relatedListFields) {
			$this->set('QueryFields', $relatedListFields);
			return $relatedListFields;
		}
		$queryGenerator = $this->getQueryGenerator();
		$entity = $queryGenerator->getEntityModel();
		if (!empty($entity->relationFields)) {
			// Get fields from entity model
			foreach ($entity->relationFields as &$fieldName) {
				$relatedListFields[$fieldName] = $relatedModuleModel->getFieldByName($fieldName);
			}
		} else {
			// Get fields from default CustomView
			$queryGenerator->initForDefaultCustomView(true, true);
			foreach ($queryGenerator->getFields() as &$fieldName) {
				if ($fieldName !== 'id') {
					$relatedListFields[$fieldName] = $relatedModuleModel->getFieldByName($fieldName);
				}
			}
			$relatedListFields['id'] = true;
		}
		if ($relatedListFields) {
			$this->set('QueryFields', $relatedListFields);
			return $relatedListFields;
		}
		$this->set('QueryFields', $relatedListFields);
		return $relatedListFields;
	}

	/**
	 * Function to get relation field for relation module and parent module
	 * @return Vtiger_Field_Model
	 */
	public function getRelationField()
	{
		if ($this->has('RelationField')) {
			return $this->get('RelationField');
		}
		$relatedModuleModel = $this->getRelationModuleModel();
		$parentModuleName = $this->getParentModuleModel()->getName();
		$relatedModuleName = $relatedModuleModel->getName();
		$fieldRel = App\Field::getFieldModuleRel($relatedModuleName, $parentModuleName);
		$relatedModelFields = $relatedModuleModel->getFields();
		foreach ($relatedModelFields as &$fieldModel) {
			if ($fieldModel->getId() === $fieldRel['fieldid'] && $fieldModel->getUIType() === 10) {
				$relationField = $fieldModel;
				break;
			}
		}
		if (!$relationField) {
			foreach ($relatedModelFields as &$fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					if (!empty($referenceList) && in_array($parentModuleName, $referenceList)) {
						$relationField = $fieldModel;
						break;
					}
				}
			}
		}
		$this->set('RelationField', $relationField ? $relationField : false);
		return $relationField;
	}

	/**
	 * Get dependents record list
	 */
	public function getDependentsList()
	{
		$fieldModel = $this->getRelationField(true);
		$this->getQueryGenerator()->addAndConditionNative([
			$fieldModel->getTableName() . '.' . $fieldModel->getColumnName() => $this->get('parentRecord')->getId()
		]);
	}

	/**
	 * Get related record list
	 */
	public function getRelatedList()
	{
		$queryGenerator = $this->getQueryGenerator();
		$record = $this->get('parentRecord')->getId();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_crmentityrel', '(vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid OR vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)']);
		$queryGenerator->addAndConditionNative(['or', ['vtiger_crmentityrel.crmid' => $record], ['vtiger_crmentityrel.relcrmid' => $record]]);
	}

	/**
	 * Get attachments
	 */
	public function getAttachments()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_senotesrel', 'vtiger_senotesrel.notesid= vtiger_notes.notesid']);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_crmentity crm2', 'crm2.crmid = vtiger_senotesrel.crmid']);
		$queryGenerator->addAndConditionNative(['crm2.crmid' => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get Campaigns
	 */
	public function getCampaigns()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_campaign_records', 'vtiger_campaign_records.campaignid=vtiger_campaign.campaignid']);
		$queryGenerator->addAndConditionNative(['vtiger_campaign_records.crmid' => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get Activities for related module
	 * @throws \Exception\AppException
	 */
	public function getActivities()
	{
		$queryGenerator = $this->getQueryGenerator();
		$relatedModuleName = $this->getRelationModuleName();
		$moduleName = $this->getParentModuleModel()->getName();
		$referenceLinkClass = Vtiger_Loader::getComponentClassName('UIType', 'ReferenceLink', $relatedModuleName);
		$referenceLinkInstance = new $referenceLinkClass();
		if (in_array($moduleName, $referenceLinkInstance->getReferenceList())) {
			$queryGenerator->addAndConditionNative(['vtiger_activity.link' => $this->get('parentRecord')->getId()]);
		} else {
			$referenceProcessClass = Vtiger_Loader::getComponentClassName('UIType', 'ReferenceProcess', $relatedModuleName);
			$referenceProcessInstance = new $referenceProcessClass();
			if (in_array($moduleName, $referenceProcessInstance->getReferenceList())) {
				$queryGenerator->addAndConditionNative(['vtiger_activity.process' => $this->get('parentRecord')->getId()]);
			} else {
				$referenceSubProcessClass = Vtiger_Loader::getComponentClassName('UIType', 'ReferenceSubProcess', $relatedModuleName);
				$referenceSubProcessInstance = new $referenceSubProcessClass();
				if (in_array($moduleName, $referenceSubProcessInstance->getReferenceList())) {
					$queryGenerator->addAndConditionNative(['vtiger_activity.subprocess' => $this->get('parentRecord')->getId()]);
				} else {
					throw new \Exception\AppException('LBL_HANDLER_NOT_FOUND');
				}
			}
		}
		switch (AppRequest::get('time')) {
			case 'current':
				$queryGenerator->addAndConditionNative(['vtiger_activity.status' => Calendar_Module_Model::getComponentActivityStateLabel('current')]);
				break;
			case 'history':
				$queryGenerator->addAndConditionNative(['vtiger_activity.status' => Calendar_Module_Model::getComponentActivityStateLabel('history')]);
				break;
		}
	}

	/**
	 * Get related emails
	 */
	public function getEmails()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_ossmailview_relation', 'vtiger_ossmailview_relation.ossmailviewid = vtiger_ossmailview.ossmailviewid']);
		$queryGenerator->addAndConditionNative(['vtiger_ossmailview_relation.crmid' => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get records for emails
	 */
	public function getRecordToMails()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_ossmailview_relation', 'vtiger_ossmailview_relation.crmid = vtiger_crmentity.crmid']);
		$queryGenerator->addAndConditionNative(['vtiger_ossmailview_relation.ossmailviewid' => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get many to many
	 */
	public function getManyToMany()
	{
		$queryGenerator = $this->getQueryGenerator();
		$relatedModuleName = $this->getRelationModuleName();
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $this->getParentModuleModel()->getName());
		$queryGenerator->addJoin(['INNER JOIN', $referenceInfo['table'], $referenceInfo['table'] . '.' . $referenceInfo['rel'] . ' = vtiger_crmentity.crmid']);
		$queryGenerator->addAndConditionNative([$referenceInfo['table'] . '.' . $referenceInfo['base'] => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get relation inventory fields
	 * @return Vtiger_Basic_InventoryField[]
	 */
	public function getRelationInventoryFields()
	{
		if ($this->has('RelationInventoryFields')) {
			return $this->get('RelationInventoryFields');
		}
		$columns = (new \App\Db\Query())
			->select(['fieldname'])
			->from('a_#__relatedlists_inv_fields')
			->where(['relation_id' => $this->getId()])
			->orderBy('sequence')
			->column();
		$inventoryFields = Vtiger_InventoryField_Model::getInstance($this->get('modulename'))->getFields();
		$fields = [];
		foreach ($columns as &$column) {
			if (!empty($inventoryFields[$column]) && $inventoryFields[$column]->isVisible()) {
				$fields[$column] = $inventoryFields[$column];
			}
		}
		$this->set('RelationInventoryFields', $fields);
		return $fields;
	}

	/**
	 * Function which will specify whether the relation is editable
	 * @return boolean
	 */
	public function isEditable()
	{
		return $this->getRelationModuleModel()->isPermitted('EditView');
	}

	/**
	 * Function which will specify whether the relation is deletable
	 * @return boolean
	 */
	public function isDeletable()
	{
		return $this->getRelationModuleModel()->isPermitted('RemoveRelation');
	}

	public function getListUrl($parentRecordModel)
	{
		$url = 'module=' . $this->getParentModuleModel()->get('name') . '&relatedModule=' . $this->get('modulename') .
			'&view=Detail&record=' . $parentRecordModel->getId() . '&mode=showRelatedList';
		if ($this->get('modulename') == 'Calendar') {
			$url .= '&time=current';
		}
		return $url;
	}

	public function addRelation($sourceRecordId, $destinationRecordId)
	{
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		$sourceModuleFocus = CRMEntity::getInstance($sourceModuleName);
		relateEntities($sourceModuleFocus, $sourceModuleName, $sourceRecordId, $destinationModuleName, $destinationRecordId, $this->get('name'));
	}

	public function deleteRelation($sourceRecordId, $relatedRecordId)
	{
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');

		if ($destinationModuleName == 'OSSMailView' || $sourceModuleName == 'OSSMailView') {
			if ($destinationModuleName == 'OSSMailView') {
				$mailId = $relatedRecordId;
				$crmid = $sourceRecordId;
			} else {
				$mailId = $sourceRecordId;
				$crmid = $relatedRecordId;
			}
			$db = PearDatabase::getInstance();
			if ($db->delete('vtiger_ossmailview_relation', 'crmid = ? && ossmailviewid = ?', [$crmid, $mailId]) > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			if ($destinationModuleName == 'ModComments') {
				include_once('modules/ModTracker/ModTracker.php');
				ModTracker::unLinkRelation($sourceModuleName, $sourceRecordId, $destinationModuleName, $relatedRecordId);
				return true;
			}
			$relationFieldModel = $this->getRelationField();
			if ($relationFieldModel && $relationFieldModel->isMandatory()) {
				return false;
			}
			$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);
			DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId, $this->get('name'));
			return true;
		}
	}

	public function addRelTree($crmid, $tree)
	{
		$sourceModule = $this->getParentModuleModel();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$db->insert('u_yf_crmentity_rel_tree', [
			'crmid' => $crmid,
			'tree' => $tree,
			'module' => $sourceModule->getId(),
			'relmodule' => $this->getRelationModuleModel()->getId(),
			'rel_created_user' => $currentUserModel->getId(),
			'rel_created_time' => date('Y-m-d H:i:s')
		]);
	}

	public function deleteRelTree($crmid, $tree)
	{
		$sourceModule = $this->getParentModuleModel();
		$db = PearDatabase::getInstance();
		$db->delete('u_yf_crmentity_rel_tree', 'crmid = ? && tree = ? && module = ? && relmodule = ?', [$crmid, $tree, $sourceModule->getId(), $this->getRelationModuleModel()->getId()]);
	}

	public function isDirectRelation()
	{
		return ($this->getRelationType() == self::RELATION_O2M);
	}

	public static function getAllRelations($parentModuleModel, $selected = true, $onlyActive = true, $permissions = true)
	{
		$query = new \App\Db\Query();
		$query->select('vtiger_relatedlists.*, vtiger_tab.name as modulename, vtiger_tab.tabid as moduleid')
			->from('vtiger_relatedlists')
			->innerJoin('vtiger_tab', 'vtiger_relatedlists.related_tabid = vtiger_tab.tabid')
			->where(['vtiger_relatedlists.tabid' => $parentModuleModel->getId()])
			->andWhere(['<>', 'related_tabid', 0]);

		if ($selected) {
			$query->andWhere(['<>', 'vtiger_relatedlists.presence', 1]);
		}
		if ($onlyActive) {
			$query->andWhere(['<>', 'vtiger_tab.presence', 1]);
		}
		$query->orderBy('sequence');
		$dataReader = $query->createCommand()->query();

		$relationModels = [];
		$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
		$privilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		while ($row = $dataReader->read()) {
			// Skip relation where target module does not exits or is no permitted for view.
			if ($permissions && !$privilegesModel->hasModuleActionPermission($row['moduleid'], 'DetailView')) {
				continue;
			}
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->set('relatedModuleName', $row['modulename']);
			$relationModels[] = $relationModel;
		}
		return $relationModels;
	}

	public function getAutoCompleteField($recordModel)
	{
		$fields = [];
		$fieldsReferenceList = [];
		$excludedModules = ['Users'];
		$excludedFields = ['created_user_id', 'modifiedby'];
		$relatedModel = $this->getRelationModuleModel();
		$relatedModuleName = $relatedModel->getName();
		$parentModule = $this->getParentModuleModel();

		$parentModelFields = $parentModule->getFields();
		foreach ($parentModelFields as $fieldName => $fieldModel) {
			if ($fieldModel->isReferenceField()) {
				$referenceList = $fieldModel->getReferenceList();
				foreach ($referenceList as $module) {
					if (!in_array($module, $excludedModules) && !in_array($fieldName, $excludedFields)) {
						$fieldsReferenceList[$module] = $fieldModel;
					}
					if ($relatedModuleName == $module) {
						$fields[$fieldName] = $recordModel->getId();
					}
				}
			}
		}
		$relatedModelFields = $relatedModel->getFields();
		foreach ($relatedModelFields as $fieldName => $fieldModel) {
			if ($fieldModel->isReferenceField()) {
				$referenceList = $fieldModel->getReferenceList();
				foreach ($referenceList as $module) {
					if (array_key_exists($module, $fieldsReferenceList) && $module != $recordModel->getModuleName()) {
						$parentFieldModel = $fieldsReferenceList[$module];
						$relId = $recordModel->get($parentFieldModel->getName());
						if ($relId != '' && $relId != 0) {
							$fields[$fieldName] = $relId;
						}
					}
				}
			}
		}
		return $fields;
	}

	public function getRestrictionsPopupField($recordModel)
	{
		$fields = [];
		$map = [];
		$relatedModel = $this->getRelationModuleModel();
		$parentModule = $this->getParentModuleModel();
		$relatedModuleName = $relatedModel->getName();
		$parentModuleName = $parentModule->getName();

		if (array_key_exists("$relatedModuleName::$parentModuleName", $map)) {
			$fieldMap = $map["$relatedModuleName::$parentModuleName"];
			$fieldModel = $recordModel->getField($fieldMap[1]);
			$value = $fieldModel->getEditViewDisplayValue($recordModel->get($fieldMap[1]));
			$fields = ['key' => $fieldMap[0], 'name' => strip_tags($value)];
		}
		return $fields;
	}

	public static function updateRelationSequenceAndPresence($relatedInfoList, $sourceModuleTabId)
	{
		$db = PearDatabase::getInstance();
		$query = 'UPDATE vtiger_relatedlists SET sequence=CASE ';
		$relation_ids = [];
		foreach ($relatedInfoList as $relatedInfo) {
			$relation_id = $relatedInfo['relation_id'];
			$relation_ids[] = $relation_id;
			$sequence = $relatedInfo['sequence'];
			$presence = $relatedInfo['presence'];
			$query .= ' WHEN relation_id=' . $relation_id . ' THEN ' . $sequence;
		}
		$query .= ' END , ';
		$query .= ' presence = CASE ';
		foreach ($relatedInfoList as $relatedInfo) {
			$relation_id = $relatedInfo['relation_id'];
			$relation_ids[] = $relation_id;
			$sequence = $relatedInfo['sequence'];
			$presence = $relatedInfo['presence'];
			$query .= ' WHEN relation_id=' . $relation_id . ' THEN ' . $presence;
		}
		$query .= ' END WHERE tabid=? && relation_id IN (' . generateQuestionMarks($relation_ids) . ')';
		$result = $db->pquery($query, array($sourceModuleTabId, $relation_ids));
	}

	public static function updateRelationPresence($relationId, $status)
	{
		$adb = PearDatabase::getInstance();
		$presence = 0;
		if ($status == 0)
			$presence = 1;
		$query = 'UPDATE vtiger_relatedlists SET `presence` = ? WHERE `relation_id` = ?;';
		$result = $adb->pquery($query, array($presence, $relationId));
	}

	public static function removeRelationById($relationId)
	{
		$db = PearDatabase::getInstance();
		if ($relationId) {
			$db->delete('vtiger_relatedlists', 'relation_id = ?', [$relationId]);
			$db->delete('vtiger_relatedlists_fields', 'relation_id = ?', [$relationId]);
			$db->delete('a_yf_relatedlists_inv_fields', 'relation_id = ?', [$relationId]);
		}
	}

	public static function updateRelationSequence($modules)
	{
		$db = PearDatabase::getInstance();
		foreach ($modules as $module) {
			$db->update('vtiger_relatedlists', [
				'sequence' => (int) $module['index'] + 1
				], 'relation_id = ?', [$module['relationId']]
			);
		}
	}

	public static function updateModuleRelatedFields($relationId, $fields)
	{
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_relatedlists_fields', 'relation_id = ?', [$relationId]);
		if ($fields) {
			foreach ($fields as $key => $field) {
				$db->insert('vtiger_relatedlists_fields', [
					'relation_id' => $relationId,
					'fieldid' => $field['id'],
					'fieldname' => $field['name'],
					'sequence' => $key
				]);
			}
		}
	}

	public static function updateModuleRelatedInventoryFields($relationId, $fields)
	{
		$db = PearDatabase::getInstance();
		$db->delete('a_yf_relatedlists_inv_fields', 'relation_id = ?', [$relationId]);
		if ($fields) {
			foreach ($fields as $key => $field) {
				$db->insert('a_yf_relatedlists_inv_fields', [
					'relation_id' => $relationId,
					'fieldname' => $field,
					'sequence' => $key
				]);
			}
		}
	}

	public function isActive()
	{
		return $this->get('presence') == 0 ? true : false;
	}

	public function getFields($type = false)
	{
		$fields = $this->get('fields');
		if (!$fields) {
			$fields = false;
			$relatedModel = $this->getRelationModuleModel();
			$relatedModelFields = $relatedModel->getFields();

			foreach ($relatedModelFields as $fieldName => $fieldModel) {
				if ($fieldModel->isViewable()) {
					$fields[] = $fieldModel;
				}
			}
			$this->set('fields', $fields);
		}
		if ($type) {
			foreach ($fields as $key => $fieldModel) {
				if ($fieldModel->getFieldDataType() != $type) {
					unset($fields[$key]);
				}
			}
		}
		return $fields;
	}

	public static function getReferenceTableInfo($moduleName, $refModuleName)
	{
		$temp = [$moduleName, $refModuleName];
		sort($temp);
		$tableName = 'u_yf_' . strtolower($temp[0]) . '_' . strtolower($temp[1]);

		if ($temp[0] == $moduleName) {
			$baseColumn = 'relcrmid';
			$relColumn = 'crmid';
		} else {
			$baseColumn = 'crmid';
			$relColumn = 'relcrmid';
		}
		return ['table' => $tableName, 'module' => $temp[0], 'base' => $baseColumn, 'rel' => $relColumn];
	}

	public function updateFavoriteForRecord($action, $data)
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleName = $this->getParentModuleModel()->get('name');
		$result = false;
		if ('add' == $action) {
			$result = $db->insert('u_yf_favorites', [
				'crmid' => $data['crmid'],
				'module' => $moduleName,
				'relcrmid' => $data['relcrmid'],
				'relmodule' => $this->getRelationModuleName(),
				'userid' => $currentUser->getId()
			]);
		} elseif ('delete' == $action) {
			$where = 'crmid = ? && module = ? && relcrmid = ?  && relmodule = ? && userid = ?';
			$result = $db->delete('u_yf_favorites', $where, [$data['crmid'], $moduleName, $data['relcrmid'], $this->getRelationModuleName(), $currentUser->getId()]);
		}
		return $result;
	}

	public static function updateStateFavorites($relationId, $status)
	{
		$adb = PearDatabase::getInstance();
		$query = 'UPDATE vtiger_relatedlists SET `favorites` = ? WHERE `relation_id` = ?;';
		$result = $adb->pquery($query, [$status, $relationId]);
	}

	public function isFavorites()
	{
		return $this->get('favorites') == 1 ? true : false;
	}
}
