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

	protected $parentModule = false;
	protected $relatedModule = false;
	protected $relationType = false;

	//one to many
	const RELATION_DIRECT = 1;
	//Many to many and many to one
	const RELATION_INDIRECT = 2;

	/**
	 * Function returns the relation id
	 * @return <Integer>
	 */
	public function getId()
	{
		return $this->get('relation_id');
	}

	/**
	 * Function sets the relation's parent module model
	 * @param <Vtiger_Module_Model> $moduleModel
	 * @return Vtiger_Relation_Model
	 */
	public function setParentModuleModel($moduleModel)
	{
		$this->parentModule = $moduleModel;
		return $this;
	}

	/**
	 * Function that returns the relation's parent module model
	 * @return <Vtiger_Module_Model>
	 */
	public function getParentModuleModel()
	{
		if (empty($this->parentModule)) {
			$this->parentModule = Vtiger_Module_Model::getInstance($this->get('tabid'));
		}
		return $this->parentModule;
	}

	public function getRelationModuleModel()
	{
		if (empty($this->relatedModule)) {
			$this->relatedModule = Vtiger_Module_Model::getInstance($this->get('related_tabid'));
		}
		return $this->relatedModule;
	}

	public function getRelationModuleName()
	{
		$relationModuleName = $this->get('relatedModuleName');
		if (!empty($relationModuleName)) {
			return $relationModuleName;
		}
		return $this->getRelationModuleModel()->getName();
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

	public function setRelationModuleModel($relationModel)
	{
		$this->relatedModule = $relationModel;
		return $this;
	}

	public function isActionSupported($actionName)
	{
		$actionName = strtolower($actionName);
		$actions = $this->getActions();
		foreach ($actions as $action) {
			if (strcmp(strtolower($action), $actionName) == 0) {
				return true;
			}
		}
		return false;
	}

	public function isSelectActionSupported()
	{
		return $this->isActionSupported('select');
	}

	public function isAddActionSupported()
	{
		return $this->isActionSupported('add');
	}

	public function getActions()
	{
		$actionString = $this->get('actions');

		$label = $this->get('label');
		// No actions for Activity history
		if ($label == 'Activity History') {
			return array();
		}

		return explode(',', $actionString);
	}

	public function getQuery($parentRecord, $actions = false, $relationListView_Model = false)
	{
		$parentModuleModel = $this->getParentModuleModel();
		$relatedModuleModel = $this->getRelationModuleModel();
		$parentModuleName = $parentModuleModel->getName();
		$relatedModuleName = $relatedModuleModel->getName();
		$functionName = $this->get('name');
		$query = $parentModuleModel->getRelationQuery($parentRecord->getId(), $functionName, $relatedModuleModel, $this, $relationListView_Model);
		if ($relationListView_Model) {
			$searchParams = $relationListView_Model->get('search_params');
			$this->addSearchConditions($query, $searchParams, $relatedModuleName);
		}
		return $query;
	}

	public function addRelation($sourcerecordId, $destinationRecordId)
	{
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$sourceModuleFocus = CRMEntity::getInstance($sourceModuleName);
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		relateEntities($sourceModuleFocus, $sourceModuleName, $sourcerecordId, $destinationModuleName, $destinationRecordId);
	}

	public function deleteRelation($sourceRecordId, $relatedRecordId)
	{
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);
		DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId);
		return true;
	}

	public function isDirectRelation()
	{
		return ($this->getRelationType() == self::RELATION_DIRECT);
	}

	public function getRelationType()
	{
		if (empty($this->relationType)) {
			$this->relationType = self::RELATION_INDIRECT;
			if ($this->getRelationField()) {
				$this->relationType = self::RELATION_DIRECT;
			}
		}
		return $this->relationType;
	}

	/**
	 * Function which will specify whether the relation is editable
	 * @return <Boolean>
	 */
	public function isEditable()
	{
		return $this->getRelationModuleModel()->isPermitted('EditView');
	}

	/**
	 * Function which will specify whether the relation is deletable
	 * @return <Boolean>
	 */
	public function isDeletable()
	{
		return $this->getRelationModuleModel()->isPermitted('Delete');
	}

	public static function getInstance($parentModuleModel, $relatedModuleModel, $label = false)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT vtiger_relatedlists.*,vtiger_tab.name as modulename FROM vtiger_relatedlists
					INNER JOIN vtiger_tab on vtiger_tab.tabid = vtiger_relatedlists.related_tabid AND vtiger_tab.presence != 1
					WHERE vtiger_relatedlists.tabid = ? AND related_tabid = ?';
		$params = array($parentModuleModel->getId(), $relatedModuleModel->getId());

		if (!empty($label)) {
			$query .= ' AND label = ?';
			$params[] = $label;
		}

		$result = $db->pquery($query, $params);
		if ($db->num_rows($result)) {
			$row = $db->query_result_rowdata($result, 0);
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			return $relationModel;
		}
		return false;
	}

	public static function getAllRelations($parentModuleModel, $selected = true, $onlyActive = true)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT vtiger_relatedlists.*,vtiger_tab.name as modulename FROM vtiger_relatedlists 
                    INNER JOIN vtiger_tab on vtiger_relatedlists.related_tabid = vtiger_tab.tabid
                    WHERE vtiger_relatedlists.tabid = ? AND related_tabid != 0';

		if ($selected) {
			$query .= ' AND vtiger_relatedlists.presence <> 1';
		}
		if ($onlyActive) {
			$query .= ' AND vtiger_tab.presence <> 1 ';
		}
		$query .= ' ORDER BY sequence'; // TODO: Need to handle entries that has related_tabid 0

		$result = $db->pquery($query, array($parentModuleModel->getId()));

		$relationModels = array();
		$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			//$relationModuleModel = Vtiger_Module_Model::getCleanInstance($moduleName);
			// Skip relation where target module does not exits or is no permitted for view.
			if (!Users_Privileges_Model::isPermitted($row['modulename'], 'DetailView')) {
				continue;
			}
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->set('relatedModuleName', $row['modulename']);
			$relationModels[] = $relationModel;
		}
		return $relationModels;
	}

	/**
	 * Function to get relation field for relation module and parent module
	 * @return Vtiger_Field_Model
	 */
	public function getRelationField()
	{
		$relationField = $this->get('relationField');
		if (!$relationField) {
			$relationField = false;
			$relatedModel = $this->getRelationModuleModel();
			$parentModule = $this->getParentModuleModel();
			$relatedModelFields = $relatedModel->getFields();

			foreach ($relatedModelFields as $fieldName => $fieldModel) {
				if ($fieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
					$referenceList = $fieldModel->getReferenceList();
					if (in_array($parentModule->getName(), $referenceList)) {
						$this->set('relationField', $fieldModel);
						$relationField = $fieldModel;
						break;
					}
				}
			}
		}
		return $relationField;
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
			if ($fieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
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
			if ($fieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
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
		$map = [
			'Contacts::Potentials' => ['parent_id', 'related_to'],
		];
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
		$relation_ids = array();
		foreach ($relatedInfoList as $relatedInfo) {
			$relation_id = $relatedInfo['relation_id'];
			$relation_ids[] = $relation_id;
			$sequence = $relatedInfo['sequence'];
			$presence = $relatedInfo['presence'];
			$query .= ' WHEN relation_id=' . $relation_id . ' THEN ' . $sequence;
		}
		$query.= ' END , ';
		$query.= ' presence = CASE ';
		foreach ($relatedInfoList as $relatedInfo) {
			$relation_id = $relatedInfo['relation_id'];
			$relation_ids[] = $relation_id;
			$sequence = $relatedInfo['sequence'];
			$presence = $relatedInfo['presence'];
			$query .= ' WHEN relation_id=' . $relation_id . ' THEN ' . $presence;
		}
		$query .= ' END WHERE tabid=? AND relation_id IN (' . generateQuestionMarks($relation_ids) . ')';
		$result = $db->pquery($query, array($sourceModuleTabId, $relation_ids));
	}

	public function updateRelationPresence($relationId, $status)
	{
		$adb = PearDatabase::getInstance();
		$presence = 0;
		if ($status == 0)
			$presence = 1;
		$query = 'UPDATE vtiger_relatedlists SET `presence` = ? WHERE `relation_id` = ?;';
		$result = $adb->pquery($query, array($presence, $relationId));
	}

	public function removeRelationById($relationId)
	{
		$adb = PearDatabase::getInstance();
		if ($relationId) {
			$adb->pquery("DELETE FROM `vtiger_relatedlists` WHERE `relation_id` = ?;", [$relationId]);
		}
	}

	public function updateRelationSequence($modules)
	{
		$adb = PearDatabase::getInstance();
		foreach ($modules as $module) {
			$sequence = (int) $module['index'] + 1;
			$query = 'UPDATE vtiger_relatedlists SET `sequence` = ? WHERE `relation_id` = ?;';
			$result = $adb->pquery($query, array($sequence, $module['relationId']));
		}
	}

	public function updateModuleRelatedFields($relationId, $fields)
	{
		$adb = PearDatabase::getInstance();
		$query = 'DELETE FROM `vtiger_relatedlists_fields` WHERE `relation_id` = ?;';
		$adb->pquery($query, array($relationId));
		foreach ($fields as $key => $field) {
			$query = 'INSERT INTO `vtiger_relatedlists_fields` (`relation_id`, `fieldid`, `fieldname`, `sequence`) VALUES (?, ?, ?, ?);';
			$result = $adb->pquery($query, array($relationId, $field['id'], $field['name'], $key));
		}
	}

	public function getRelationFields($onlyFields = false, $association = false)
	{
		$adb = PearDatabase::getInstance();
		$relationId = $this->getId();
		$query = 'SELECT vtiger_field.columnname, vtiger_field.fieldname FROM vtiger_relatedlists_fields INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_relatedlists_fields.fieldid WHERE vtiger_relatedlists_fields.relation_id = ? AND vtiger_field.presence IN (0,2);';
		$result = $adb->pquery($query, [$relationId]);
		if ($onlyFields) {
			$fields = array();
			for ($i = 0; $i < $adb->num_rows($result); $i++) {
				$columnname = $adb->query_result_raw($result, $i, 'columnname');
				$fieldname = $adb->query_result_raw($result, $i, 'fieldname');
				if ($association)
					$fields[$columnname] = $fieldname;
				else
					$fields[] = $fieldname;
			}
			return $fields;
		}
		return $result->GetArray();
	}

	public function addSearchConditions($query, $searchParams, $related_module)
	{
		if (!empty($searchParams)) {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$queryGenerator = new QueryGenerator($related_module, $currentUserModel);
			$queryGenerator->parseAdvFilterList($searchParams);
			$where = $queryGenerator->getWhereClause(true);
			$query .= $where;
		}
		return $query;
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
				if($fieldModel->isViewable()){
					$fields[] = $fieldModel;
				}
			}
			$this->set('fields', $fields);
		}
		if($type){
			foreach ($fields as $key => $fieldModel) {
				if ($fieldModel->getFieldDataType() != $type) {
					unset($fields[$key]);
				}
			}
		}
		return $fields;
	}
}
