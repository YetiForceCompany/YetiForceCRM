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

	static $_cached_instance = [];
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
			return [];
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
			$queryGenerator = $relationListView_Model->get('query_generator');
			$joinTable = $queryGenerator->getFromClause(true);
			if ($joinTable) {
				$queryComponents = preg_split('/WHERE/i', $query);
				$query = $queryComponents[0] . $joinTable . ' WHERE ' . $queryComponents[1];
			}
			$where = $queryGenerator->getWhereClause(true);
			$query .= $where;
		}
		return $query;
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
		return $this->getRelationModuleModel()->isPermitted('RemoveRelation');
	}

	public function showCreatorDetail()
	{
		if ($this->getRelationType() != 2) {
			return false;
		}
		return $this->get('creator_detail');
	}

	public function showComment()
	{
		if ($this->getRelationType() != 2) {
			return false;
		}
		return $this->get('relation_comment');
	}

	public static function getInstance($parentModuleModel, $relatedModuleModel, $label = false)
	{
		$relKey = $parentModuleModel->getId() . '_' . $relatedModuleModel->getId() . '_' . ($label ? 1 : 0);
		if (key_exists($relKey, self::$_cached_instance)) {
			return self::$_cached_instance[$relKey];
		}
		if (($relatedModuleModel->getName() == 'ModComments' && $parentModuleModel->isCommentEnabled()) || $parentModuleModel->getName() == 'Documents') {
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			if (method_exists($relationModel, 'setExceptionData')) {
				$relationModel->setExceptionData();
			}
			self::$_cached_instance[$relKey] = $relationModel;
			return $relationModel;
		}
		$db = PearDatabase::getInstance();
		$query = 'SELECT vtiger_relatedlists.*,vtiger_tab.name as modulename FROM vtiger_relatedlists
					INNER JOIN vtiger_tab on vtiger_tab.tabid = vtiger_relatedlists.related_tabid && vtiger_tab.presence != 1
					WHERE vtiger_relatedlists.tabid = ? && related_tabid = ?';
		$params = [$parentModuleModel->getId(), $relatedModuleModel->getId()];
		if (!empty($label)) {
			$query .= ' && label = ?';
			$params[] = $label;
		}

		$result = $db->pquery($query, $params);
		if ($db->getRowCount($result)) {
			$row = $db->getRow($result);
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			self::$_cached_instance[$relKey] = $relationModel;
			return $relationModel;
		}
		return false;
	}

	public static function getAllRelations($parentModuleModel, $selected = true, $onlyActive = true, $permissions = true)
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT vtiger_relatedlists.*,vtiger_tab.name as modulename,vtiger_tab.tabid as moduleid FROM vtiger_relatedlists 
                    INNER JOIN vtiger_tab on vtiger_relatedlists.related_tabid = vtiger_tab.tabid
                    WHERE vtiger_relatedlists.tabid = ? && related_tabid != 0';

		if ($selected) {
			$query .= ' && vtiger_relatedlists.presence <> 1';
		}
		if ($onlyActive) {
			$query .= ' && vtiger_tab.presence <> 1 ';
		}
		$query .= ' ORDER BY sequence'; 

		$result = $db->pquery($query, array($parentModuleModel->getId()));

		$relationModels = [];
		$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
		$privilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		while ($row = $db->getRow($result)) {
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
				if ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					if (!empty($referenceList) && in_array($parentModule->getName(), $referenceList)) {
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
		$query.= ' END , ';
		$query.= ' presence = CASE ';
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

	public function getRelationFields($onlyFields = false, $association = false)
	{
		$adb = PearDatabase::getInstance();
		$relationId = $this->getId();
		$query = 'SELECT vtiger_field.columnname, vtiger_field.fieldname FROM vtiger_relatedlists_fields INNER JOIN vtiger_field ON vtiger_field.fieldid = vtiger_relatedlists_fields.fieldid WHERE vtiger_relatedlists_fields.relation_id = ? && vtiger_field.presence IN (0,2);';
		$result = $adb->pquery($query, [$relationId]);
		if ($onlyFields) {
			$fields = [];
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

	public function getRelationInventoryFields()
	{
		$db = PearDatabase::getInstance();
		$relationId = $this->getId();
		$moduleName = $this->get('modulename');
		$inventoryFields = Vtiger_InventoryField_Model::getInstance($moduleName)->getFields();
		$query = 'SELECT a_yf_relatedlists_inv_fields.fieldname FROM a_yf_relatedlists_inv_fields WHERE a_yf_relatedlists_inv_fields.relation_id = ? ORDER BY sequence;';
		$result = $db->pquery($query, [$relationId]);
		$fields = [];
		while ($name = $db->getSingleValue($result)) {
			if ($inventoryFields[$name] && $inventoryFields[$name]->isVisible()) {
				$fields[] = $name;
			}
		}
		return $fields;
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
