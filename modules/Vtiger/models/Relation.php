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

class Vtiger_Relation_Model extends \App\Base
{
	/**
	 *  Cached instances.
	 *
	 * @var Vtiger_Relation_Model[]
	 */
	protected static $cachedInstances = [];
	/**
	 * Cached instances by relation id.
	 *
	 * @var Vtiger_Relation_Model[]
	 */
	protected static $cachedInstancesById = [];
	protected $parentModule = false;
	protected $relatedModule = false;
	/**
	 * @var \App\Relation\RelationAbstraction Class that includes basic operations on relations
	 */
	protected $typeRelationModel;
	/**
	 * @var array Custom view list
	 */
	protected $customViewList;

	//one to many
	const RELATION_O2M = 1;

	//Many to many and many to one
	const RELATION_M2M = 2;

	/**
	 * Function returns the relation id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->get('relation_id');
	}

	/**
	 * Function sets the relation's parent module model.
	 *
	 * @param Vtiger_Module_Model $moduleModel
	 *
	 * @return Vtiger_Relation_Model
	 */
	public function setParentModuleModel($moduleModel)
	{
		$this->parentModule = $moduleModel;
		return $this;
	}

	/**
	 * Function that returns the relation's parent module model.
	 *
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
	 * Gets parent record model.
	 *
	 * @return Vtiger_Record_Model|null
	 */
	public function getParentRecord(): ?Vtiger_Record_Model
	{
		return $this->get('parentRecord') ?? null;
	}

	/**
	 * Set relation's parent module model.
	 *
	 * @param Vtiger_Module_Model $relationModel
	 *
	 * @return $this
	 */
	public function setRelationModuleModel($relationModel)
	{
		$this->relatedModule = $relationModel;
		return $this;
	}

	/**
	 * Function that returns the relation's related module model.
	 *
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
	 * Get relation module name.
	 *
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
	 * Get actions.
	 *
	 * @return string[]
	 */
	public function getActions()
	{
		if (\is_array($this->get('actions'))) {
			return $this->get('actions');
		}
		// No actions for Activity history
		if ('Activity History' === $this->get('c')) {
			return [];
		}
		$actions = explode(',', strtolower($this->get('actions')));
		$this->set('actions', $actions);
		return $actions;
	}

	/**
	 * Check if action is supported.
	 *
	 * @param string $actionName
	 *
	 * @return bool
	 */
	public function isActionSupported($actionName)
	{
		return \in_array(strtolower($actionName), $this->getActions());
	}

	/**
	 * Is record selection action available.
	 *
	 * @return bool
	 */
	public function isSelectActionSupported()
	{
		return $this->isActionSupported('select');
	}

	/**
	 * Is record add action available.
	 *
	 * @return bool
	 */
	public function isAddActionSupported()
	{
		return $this->isActionSupported('add') && $this->getRelationModuleModel()->isPermitted('CreateView');
	}

	/**
	 * Check favorite.
	 */
	public function isFavorites()
	{
		return 1 == $this->get('favorites') ? true : false;
	}

	/**
	 * Check related view type.
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function isRelatedViewType($type)
	{
		return false !== strpos($this->get('view_type'), $type);
	}

	/**
	 * Show user who created relation.
	 *
	 * @return bool
	 */
	public function showCreatorDetail()
	{
		if (0 === $this->get('creator_detail') || self::RELATION_M2M !== $this->getRelationType()) {
			return false;
		}
		return (bool) $this->get('creator_detail');
	}

	/**
	 * Show comments in related module.
	 *
	 * @return bool
	 */
	public function showComment()
	{
		if (0 === $this->get('relation_comment') || self::RELATION_M2M !== $this->getRelationType()) {
			return false;
		}
		return (bool) $this->get('relation_comment');
	}

	/**
	 * Get query generator instance.
	 *
	 * @return \App\QueryGenerator
	 */
	public function getQueryGenerator(): App\QueryGenerator
	{
		if (!$this->has('query_generator')) {
			$this->set('query_generator', new \App\QueryGenerator($this->getRelationModuleName()));
		}
		return $this->get('query_generator');
	}

	/**
	 * Get relation type.
	 *
	 * @return int
	 */
	public function getRelationType()
	{
		if (!$this->has('relationType')) {
			$this->set('relationType', $this->getTypeRelationModel()->getRelationType());
		}
		return $this->get('relationType');
	}

	/**
	 * Get related view type.
	 *
	 * @return string[]
	 */
	public function getRelatedViewType(): array
	{
		return explode(',', $this->get('view_type')) ?? [];
	}

	/**
	 * Get custom view.
	 *
	 * @return string[]
	 */
	public function getCustomView(): array
	{
		if ($this->isEmpty('custom_view')) {
			return [];
		}
		return explode(',', $this->get('custom_view')) ?? [];
	}

	/**
	 * Get relation model instance.
	 *
	 * @param Vtiger_Module_Model $parentModuleModel
	 * @param Vtiger_Module_Model $relatedModuleModel
	 * @param bool|int            $relationId
	 *
	 * @return $this|bool
	 */
	public static function getInstance($parentModuleModel, $relatedModuleModel, $relationId = false)
	{
		$relKey = $parentModuleModel->getId() . '_' . $relatedModuleModel->getId() . '_' . $relationId;
		if (isset(self::$cachedInstances[$relKey])) {
			return self::$cachedInstances[$relKey] ? clone self::$cachedInstances[$relKey] : self::$cachedInstances[$relKey];
		}
		if (('ModComments' == $relatedModuleModel->getName() && $parentModuleModel->isCommentEnabled()) || 'Documents' == $parentModuleModel->getName()) {
			$moduleName = 'ModComments' == $relatedModuleModel->getName() ? $relatedModuleModel->getName() : $parentModuleModel->getName();
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $moduleName);
			$relationModel = new $relationModelClassName();
			$relationModel->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			if (method_exists($relationModel, 'setExceptionData')) {
				$relationModel->setExceptionData();
			}
			self::$cachedInstances[$relKey] = $relationModel;
			return clone $relationModel;
		}
		if (empty($relationId)) {
			$row = current(\App\Relation::getByModule($parentModuleModel->getName(), true, $relatedModuleModel->getName()));
		} else {
			$row = \App\Relation::getById($relationId);
			if (1 === $row['presence'] || $row['tabid'] !== $parentModuleModel->getId() || $row['related_tabid'] !== $relatedModuleModel->getId()) {
				$row = [];
			}
		}
		if ($row) {
			$row['modulename'] = $row['related_modulename'];
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			$relationModel->set('relatedModuleName', $row['related_modulename']);
			self::$cachedInstances[$relKey] = $relationModel;
			self::$cachedInstancesById[$row['relation_id']] = $relationModel;
			return clone $relationModel;
		}
		self::$cachedInstances[$relKey] = false;
		return false;
	}

	/**
	 * Get relation model instance by relation id.
	 *
	 * @param int $relationId
	 *
	 * @return self|bool
	 */
	public static function getInstanceById(int $relationId)
	{
		if (!isset(self::$cachedInstancesById[$relationId])) {
			$row = \App\Relation::getById($relationId);
			$relationModel = false;
			if ($row) {
				$row['modulename'] = $row['related_modulename'];
				$parentModuleModel = Vtiger_Module_Model::getInstance($row['tabid']);
				$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->getName());
				$relationModel = new $relationModelClassName();
				$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel(Vtiger_Module_Model::getInstance($row['related_modulename']));
				$relationModel->set('relatedModuleName', $row['related_modulename']);
				if (method_exists($relationModel, 'setExceptionData')) {
					$relationModel->setExceptionData();
				}
			}
			self::$cachedInstancesById[$relationId] = $relationModel;
		}
		return self::$cachedInstancesById[$relationId] ? clone self::$cachedInstancesById[$relationId] : null;
	}

	/**
	 * Get type relation model.
	 *
	 * @return \App\Relation\RelationAbstraction
	 */
	public function getTypeRelationModel(): App\Relation\RelationAbstraction
	{
		if (!isset($this->typeRelationModel)) {
			$name = ucfirst($this->get('name'));
			$relationClassName = Vtiger_Loader::getComponentClassName('Relation', $name, $this->getParentModuleModel()->getName(), false);
			if (!$relationClassName) {
				$relationClassName = Vtiger_Loader::getComponentClassName('Relation', $name, $this->getRelationModuleName());
			}
			if (class_exists($relationClassName)) {
				$this->typeRelationModel = new $relationClassName();
				$this->typeRelationModel->relationModel = &$this;
			} else {
				App\Log::error("Not exist relation: {$name} in " . __METHOD__);
				throw new \App\Exceptions\NotAllowedMethod('LBL_NOT_EXIST_RELATION: ' . $name);
			}
		}
		return $this->typeRelationModel;
	}

	/**
	 * Get query form relation.
	 *
	 * @throws \App\Exceptions\NotAllowedMethod
	 *
	 * @return \App\QueryGenerator
	 */
	public function getQuery()
	{
		if (empty($this->get('parentRecord'))) {
			App\Log::error('No value parentRecord in ' . __METHOD__);
			throw new \App\Exceptions\IllegalValue('ERR_NO_VALUE||parentRecord');
		}
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->setSourceRecord($this->get('parentRecord')->getId());
		$this->getTypeRelationModel()->getQuery();
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
	 * Get query fields.
	 *
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
		foreach (App\Field::getFieldsFromRelation($this->getId()) as $fieldName) {
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
			foreach ($entity->relationFields as $fieldName) {
				$relatedListFields[$fieldName] = $relatedModuleModel->getFieldByName($fieldName);
			}
		} else {
			// Get fields from default CustomView
			$queryGenerator->initForDefaultCustomView(true, true);
			foreach ($queryGenerator->getFields() as $fieldName) {
				if ('id' !== $fieldName) {
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
	 * Function to get relation field for relation module and parent module.
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getRelationField()
	{
		if ($this->has('RelationField')) {
			return $this->get('RelationField');
		}
		$relatedModuleModel = $this->getRelationModuleModel();
		$parentModuleName = $this->getParentModuleModel()->getName();
		$relatedModelFields = $relatedModuleModel->getFields();
		if (!$this->isEmpty('field_name') && isset($relatedModelFields[$this->get('field_name')])) {
			$relationField = $relatedModelFields[$this->get('field_name')];
		} else {
			$fieldRel = App\Field::getRelatedFieldForModule($relatedModuleModel->getName(), $parentModuleName);
			if (isset($fieldRel['fieldid'])) {
				foreach ($relatedModelFields as $fieldModel) {
					if ($fieldModel->getId() === $fieldRel['fieldid']) {
						$relationField = $fieldModel;
						break;
					}
				}
			}
		}
		if (empty($relationField)) {
			$relationField = false;
			foreach ($relatedModelFields as $fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					if (!empty($referenceList) && \in_array($parentModuleName, $referenceList)) {
						$relationField = $fieldModel;
						break;
					}
				}
			}
		}
		$this->set('RelationField', $relationField ?: false);
		return $relationField;
	}

	/**
	 * Get relation inventory fields.
	 *
	 * @return Vtiger_Basic_InventoryField[]
	 */
	public function getRelationInventoryFields()
	{
		if (!$this->has('RelationInventoryFields')) {
			$this->set('RelationInventoryFields', []);
			if ($this->getRelationModuleModel()->isInventory()) {
				$columns = (new \App\Db\Query())
					->select(['fieldname'])
					->from('a_#__relatedlists_inv_fields')
					->where(['relation_id' => $this->getId()])
					->orderBy('sequence')
					->column();
				$inventoryFields = Vtiger_Inventory_Model::getInstance($this->getRelationModuleModel()->getName())->getFields();
				$fields = [];
				foreach ($columns as &$column) {
					if (!empty($inventoryFields[$column]) && $inventoryFields[$column]->isVisible()) {
						$fields[$column] = $inventoryFields[$column];
					}
				}
				$this->set('RelationInventoryFields', $fields);
			}
		}
		return $this->get('RelationInventoryFields');
	}

	/**
	 * Function which will specify whether the relation is editable.
	 *
	 * @return bool
	 */
	public function isEditable(): bool
	{
		return $this->getRelationModuleModel()->isPermitted('EditView');
	}

	/**
	 * Function which will specify whether the relation is deletable.
	 *
	 * @param \Vtiger_Record_Model|null $recordModel
	 * @param int|null                  $recordId
	 *
	 * @return bool
	 */
	public function privilegeToDelete(Vtiger_Record_Model $recordModel = null, int $recordId = null): bool
	{
		$returnVal = $this->getRelationModuleModel()->isPermitted('RemoveRelation');
		if ($returnVal && $this->getRelationType() === static::RELATION_O2M && ($fieldModel = $this->getRelationField())) {
			if (!$recordModel && $recordId) {
				$recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
			}
			$returnVal = !$fieldModel->isMandatory() && $fieldModel->isEditable() && !$fieldModel->isEditableReadOnly() && (!$recordModel || $recordModel->isEditable());
		}
		return $returnVal;
	}

	/**
	 * Function which will specify whether the tree element is deletable.
	 *
	 * @return bool
	 */
	public function privilegeToTreeDelete(): bool
	{
		return $this->getRelationModuleModel()->isPermitted('RemoveRelation');
	}

	/**
	 * Get list url for record.
	 *
	 * @param Vtiger_Module_Model $parentRecordModel
	 *
	 * @return string
	 */
	public function getListUrl(Vtiger_Record_Model $parentRecordModel): string
	{
		$url = 'index.php?module=' . $this->getParentModuleModel()->get('name') . '&relatedModule=' . $this->get('modulename') .
			'&view=Detail&record=' . $parentRecordModel->getId() . '&mode=showRelatedList&relationId=' . $this->getId();
		if ('Calendar' == $this->get('modulename')) {
			$url .= '&time=current';
		}
		return $url;
	}

	/**
	 * Get create url from parent record.
	 *
	 * @param bool $fullView
	 *
	 * @return string
	 */
	public function getCreateViewUrl(bool $fullView = false)
	{
		$parentRecord = $this->getParentRecord();
		$relatedModuleModel = $this->getRelationModuleModel();
		if (!$fullView && $relatedModuleModel->isQuickCreateSupported()) {
			$createViewUrl = $relatedModuleModel->getQuickCreateUrl();
		} else {
			$createViewUrl = $relatedModuleModel->getCreateRecordUrl();
		}
		$createViewUrl .= '&sourceModule=' . $parentRecord->getModule()->getName() . '&sourceRecord=' . $parentRecord->getId() . '&relationOperation=true&relationId=' . $this->getId();
		if ($this->isDirectRelation()) {
			$relationField = $this->getRelationField();
			$createViewUrl .= '&' . $relationField->getName() . '=' . $parentRecord->getId();
		}

		return $createViewUrl;
	}

	/**
	 * Get delete url from parent record.
	 *
	 * @param int $relatedRecordId
	 *
	 * @return string
	 */
	public function getDeleteUrl(int $relatedRecordId)
	{
		$parentModuleName = $this->getParentModuleModel()->getName();
		$relatedModuleName = $this->getRelationModuleModel()->getName();
		$recordId = $this->getParentRecord()->getId();

		return "index.php?module={$parentModuleName}&related_module={$relatedModuleName}&action=RelationAjax&mode=deleteRelation&related_record_list=[{$relatedRecordId}]&src_record={$recordId}&relationId={$this->getId()}";
	}

	/**
	 * Add relation.
	 *
	 * @param int       $sourceRecordId
	 * @param int|int[] $destinationRecordIds
	 * @param mixed     $params
	 */
	public function addRelation($sourceRecordId, $destinationRecordIds, $params = false)
	{
		$result = false;
		$sourceModuleName = $this->getParentModuleModel()->getName();
		$relationModel = $this->getTypeRelationModel();
		if (!\is_array($destinationRecordIds)) {
			$destinationRecordIds = [$destinationRecordIds];
		}
		$data = [
			'CRMEntity' => $this->getParentModuleModel()->getEntityInstance(),
			'sourceModule' => $sourceModuleName,
			'sourceRecordId' => $sourceRecordId,
			'destinationModule' => $this->getRelationModuleModel()->getName(),
			'relationId' => $this->getId(),
		];
		$eventHandler = new \App\EventHandler();
		$eventHandler->setModuleName($sourceModuleName);
		foreach ($destinationRecordIds as $destinationRecordId) {
			$data['destinationRecordId'] = $destinationRecordId;
			$eventHandler->setParams($data);
			$eventHandler->trigger('EntityBeforeLink');
			if ($result = $relationModel->create($sourceRecordId, $destinationRecordId)) {
				\CRMEntity::trackLinkedInfo($sourceRecordId);
				$eventHandler->trigger('EntityAfterLink');
			}
		}
		return $result;
	}

	/**
	 * Transfer.
	 *
	 * @param array $recordsToTransfer
	 */
	public function transfer(array $recordsToTransfer)
	{
		$relationModel = $this->getTypeRelationModel();
		$eventHandler = new \App\EventHandler();
		$eventHandler->setModuleName($this->getParentModuleModel()->getName());
		$toRecordId = $this->get('parentRecord')->getId();
		$params = ['sourceRecordId' => $toRecordId, 'sourceModule' => $eventHandler->getModuleName(), 'destinationModule' => $this->getRelationModuleModel()->getName()];

		foreach ($recordsToTransfer as $relatedRecordId => $fromRecordId) {
			$params['destinationRecordId'] = $relatedRecordId;
			$eventHandler->setParams($params);
			$eventHandler->trigger('EntityBeforeTransferUnLink');
			if ($relationModel->transfer($relatedRecordId, $fromRecordId, $toRecordId)) {
				\CRMEntity::trackLinkedInfo([$toRecordId, $fromRecordId]);
				$eventHandler->trigger('EntityAfterTransferLink');
			}
		}
	}

	/**
	 * Transfer tree relation.
	 *
	 * @param array $relationRecords
	 */
	public function transferTree(array $relationRecords)
	{
		$recordId = $this->get('parentRecord')->getId();
		$dbCommand = \App\Db::getInstance()->createCommand();
		foreach ($relationRecords as $tree => $fromId) {
			if ($dbCommand->update('u_#__crmentity_rel_tree', ['crmid' => $recordId], ['crmid' => $fromId, 'relmodule' => $this->getRelationModuleModel()->getId(), 'tree' => $tree])->execute()) {
				$dbCommand->update('vtiger_crmentity', ['modifiedtime' => date('Y-m-d H:i:s'), 'modifiedby' => \App\User::getCurrentUserId()], ['crmid' => [$fromId, $recordId]])->execute();
			}
		}
	}

	/**
	 * Delete relation.
	 *
	 * @param int $relId
	 */
	public function transferDelete(int $relId)
	{
		$recordId = $this->get('parentRecord')->getId();
		$params = ['sourceRecordId' => $recordId,
			'sourceModule' => $this->getParentModuleModel()->getName(),
			'destinationModule' => $this->getRelationModuleModel()->getName(),
			'destinationRecordId' => $relId, ];
		$eventHandler = new \App\EventHandler();
		$eventHandler->setModuleName($this->getParentModuleModel()->getName());
		$eventHandler->setParams($params);
		$eventHandler->trigger('EntityBeforeTransferUnLink');
		if ($this->getTypeRelationModel()->delete($recordId, $relId)) {
			$eventHandler->trigger('EntityAfterTransferUnLink');
		}
	}

	/**
	 * Delete relation.
	 *
	 * @param int $sourceRecordId
	 * @param int $relatedRecordId
	 *
	 * @return bool
	 */
	public function deleteRelation($sourceRecordId, $relatedRecordId)
	{
		$sourceModuleName = $this->getParentModuleModel()->getName();
		$destinationModuleName = $this->getRelationModuleModel()->getName();
		$result = false;
		if ('ModComments' === $destinationModuleName) {
			include_once 'modules/ModTracker/ModTracker.php';
			ModTracker::unLinkRelation($sourceModuleName, $sourceRecordId, $destinationModuleName, $relatedRecordId);
			$result = true;
		} elseif (!($this->getRelationField() && $this->getRelationField()->isMandatory())) {
			$destinationModuleFocus = $this->getRelationModuleModel()->getEntityInstance();
			$eventHandler = new \App\EventHandler();
			$eventHandler->setModuleName($sourceModuleName);
			$eventHandler->setParams([
				'CRMEntity' => $destinationModuleFocus,
				'sourceModule' => $sourceModuleName,
				'sourceRecordId' => $sourceRecordId,
				'destinationModule' => $destinationModuleName,
				'destinationRecordId' => $relatedRecordId,
				'relatedName' => $this->get('name'),
			]);
			$eventHandler->trigger('EntityBeforeUnLink');
			if ($result = $this->getTypeRelationModel()->delete($sourceRecordId, $relatedRecordId)) {
				$destinationModuleFocus->trackUnLinkedInfo($sourceRecordId);
				$eventHandler->trigger('EntityAfterUnLink');
			}
		}
		return $result;
	}

	/**
	 * Function to add tree type relation.
	 *
	 * @param int    $crmid
	 * @param string $tree
	 */
	public function addRelationTree($crmid, $tree)
	{
		App\Db::getInstance()->createCommand()->insert('u_#__crmentity_rel_tree', [
			'crmid' => $crmid,
			'tree' => $tree,
			'module' => $this->getParentModuleModel()->getId(),
			'relmodule' => $this->getRelationModuleModel()->getId(),
			'rel_created_user' => App\User::getCurrentUserId(),
			'rel_created_time' => date('Y-m-d H:i:s'),
		])->execute();
	}

	/**
	 * Function to delete tree type relation.
	 *
	 * @param int    $crmid
	 * @param string $tree
	 */
	public function deleteRelationTree($crmid, $tree)
	{
		App\Db::getInstance()->createCommand()
			->delete('u_#__crmentity_rel_tree', ['crmid' => $crmid, 'tree' => $tree, 'module' => $this->getParentModuleModel()->getId(), 'relmodule' => $this->getRelationModuleModel()->getId()])
			->execute();
	}

	/**
	 * Query tree category relation.
	 *
	 * @return \App\Db\Query
	 */
	public function getRelationTreeQuery()
	{
		$template = [];
		foreach ($this->getRelationModuleModel()->getFieldsByType('tree') as $field) {
			if ($field->isActiveField()) {
				$template[] = $field->getFieldParams();
			}
		}
		return (new \App\Db\Query())
			->select(['ttd.*', 'rel.crmid', 'rel.rel_created_time', 'rel.rel_created_user', 'rel.rel_comment'])
			->from('vtiger_trees_templates_data ttd')
			->innerJoin('u_#__crmentity_rel_tree rel', 'rel.tree = ttd.tree')
			->where(['ttd.templateid' => $template, 'rel.crmid' => $this->get('parentRecord')->getId(), 'rel.relmodule' => $this->getRelationModuleModel()->getId()]);
	}

	/**
	 * Tree category relation.
	 *
	 * @return array
	 */
	public function getRelationTree()
	{
		return $this->getRelationTreeQuery()->all();
	}

	/**
	 * Is the tree type relation available.
	 *
	 * @return bool
	 */
	public function isTreeRelation()
	{
		if (\in_array($this->getRelationModuleModel()->getName(), ['OutsourcedProducts', 'Products', 'Services', 'OSSOutsourcedServices'])) {
			foreach ($this->getRelationModuleModel()->getFieldsByType('tree') as $field) {
				if ($field->isActiveField()) {
					return true;
				}
			}
		}
		return false;
	}

	public function isDirectRelation()
	{
		return self::RELATION_O2M == $this->getRelationType();
	}

	/**
	 * Getting all relations.
	 *
	 * @param \Vtiger_Module_Model $moduleModel
	 * @param bool                 $selected
	 * @param bool                 $onlyActive
	 * @param bool                 $permissions
	 * @param string               $key
	 *
	 * @return \Vtiger_Relation_Model[]
	 */
	public static function getAllRelations(Vtiger_Module_Model $moduleModel, bool $selected = true, bool $onlyActive = true, bool $permissions = true, string $key = 'relation_id')
	{
		$relationModels = [];
		$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $moduleModel->get('name'));
		$privilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		foreach (\App\Relation::getByModule($moduleModel->getName(), $onlyActive) as $row) {
			if ($selected && 1 === $row['presence']) {
				continue;
			}
			$row['modulename'] = $row['related_modulename'];
			$row['moduleid'] = $row['related_tabid'];
			// Skip relation where target module does not exits or is no permitted for view.
			if ($permissions && !$privilegesModel->hasModuleActionPermission($row['related_modulename'], 'DetailView')) {
				continue;
			}
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($moduleModel)->set('relatedModuleName', $row['related_modulename']);
			$relationModels[$row[$key]] = $relationModel;
		}
		return $relationModels;
	}

	/**
	 * Get autocomplete fields.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return array
	 */
	public function getAutoCompleteField($recordModel): array
	{
		$fields = [];
		$fieldsReferenceList = [];
		$excludedModules = ['Users'];
		$relatedModel = $this->getRelationModuleModel();
		if ($relationField = $this->getRelationField()) {
			$fields[$relationField->getName()] = $recordModel->getId();
		}
		$parentModelFields = $this->getParentModuleModel()->getFields();
		foreach ($parentModelFields as $fieldName => $fieldModel) {
			if ($fieldModel->isReferenceField()) {
				$referenceList = $fieldModel->getReferenceList();
				foreach ($referenceList as $module) {
					if (!\in_array($module, $excludedModules) && 'userCreator' !== !$fieldModel->getFieldDataType()) {
						$fieldsReferenceList[$module] = $fieldModel;
					}
				}
			}
		}
		$relatedModelFields = $relatedModel->getFields();
		foreach ($relatedModelFields as $fieldName => $fieldModel) {
			if ($fieldModel->isReferenceField()) {
				$referenceList = $fieldModel->getReferenceList();
				foreach ($referenceList as $module) {
					if (\array_key_exists($module, $fieldsReferenceList) && $module != $recordModel->getModuleName()) {
						$parentFieldModel = $fieldsReferenceList[$module];
						$relId = $recordModel->get($parentFieldModel->getName());
						if (!empty($relId) && \App\Record::isExists($relId)) {
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
		$relatedModuleName = $relatedModel->getName();
		$parentModuleName = $this->getParentModuleModel()->getName();

		if (\array_key_exists("$relatedModuleName::$parentModuleName", $map)) {
			$fieldMap = $map["$relatedModuleName::$parentModuleName"];
			$fieldModel = $recordModel->getField($fieldMap[1]);
			$value = $fieldModel->getEditViewDisplayValue($recordModel->get($fieldMap[1]), $recordModel);
			$fields = ['key' => $fieldMap[0], 'name' => strip_tags($value)];
		}
		return $fields;
	}

	/**
	 * Function to set presence relation.
	 *
	 * @param int    $relationId
	 * @param string $status
	 */
	public static function updateRelationPresence($relationId, $status)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_relatedlists', ['presence' => !$status ? 1 : 0], ['relation_id' => $relationId])->execute();
		\App\Relation::clearCacheById($relationId);
	}

	/**
	 * Function to set presence relation.
	 *
	 * @param int   $relationId
	 * @param array $customView
	 */
	public static function updateRelationCustomView(int $relationId, array $customView): void
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_relatedlists', ['custom_view' => implode(',', $customView)], ['relation_id' => $relationId])->execute();
		\App\Relation::clearCacheById($relationId);
	}

	/**
	 * Removes relation between modules.
	 *
	 * @param int $relationId
	 */
	public static function removeRelationById($relationId)
	{
		if ($relationId) {
			$dbCommand = App\Db::getInstance()->createCommand();
			$dbCommand->delete('vtiger_relatedlists', ['relation_id' => $relationId])->execute();
			$dbCommand->delete('vtiger_relatedlists_fields', ['relation_id' => $relationId])->execute();
			App\Db::getInstance('admin')->createCommand()->delete('a_yf_relatedlists_inv_fields', ['relation_id' => $relationId])->execute();
		}
		\App\Relation::clearCacheById($relationId);
	}

	/**
	 * Function to save sequence of relation.
	 *
	 * @param array $modules
	 */
	public static function updateRelationSequence($modules)
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		foreach ($modules as $module) {
			$dbCommand->update('vtiger_relatedlists', ['sequence' => (int) $module['index'] + 1], ['relation_id' => $module['relationId']])->execute();
			\App\Relation::clearCacheById((int) $module['relationId']);
		}
	}

	/**
	 * Update module related fields.
	 *
	 * @param int   $relationId
	 * @param array $fields
	 *
	 * @throws \yii\db\Exception
	 */
	public static function updateModuleRelatedFields(int $relationId, $fields)
	{
		$db = \App\Db::getInstance();
		$transaction = $db->beginTransaction();
		try {
			$db->createCommand()->delete('vtiger_relatedlists_fields', ['relation_id' => $relationId])->execute();
			if ($fields) {
				$addedFields = [];
				foreach ($fields as $key => $field) {
					if (\in_array($field['id'], $addedFields)) {
						continue;
					}
					$db->createCommand()->insert('vtiger_relatedlists_fields', [
						'relation_id' => $relationId,
						'fieldid' => $field['id'],
						'sequence' => $key,
					])->execute();
					$addedFields[] = $field['id'];
				}
			}
			$transaction->commit();
		} catch (\Throwable $e) {
			$transaction->rollBack();
			throw $e;
		}
		\App\Relation::clearCacheById($relationId);
	}

	public static function updateModuleRelatedInventoryFields($relationId, $fields)
	{
		$db = \App\Db::getInstance('admin');
		$db->createCommand()->delete('a_#__relatedlists_inv_fields', ['relation_id' => $relationId])->execute();
		if ($fields) {
			foreach ($fields as $key => $field) {
				$db->createCommand()->insert('a_#__relatedlists_inv_fields', [
					'relation_id' => $relationId,
					'fieldname' => $field,
					'sequence' => $key,
				])->execute();
			}
		}
		\App\Cache::clear();
	}

	public function isActive()
	{
		return 0 == $this->get('presence') ? true : false;
	}

	public function getFields($type = false)
	{
		$fields = $this->get('fields');
		if (!$fields) {
			$fields = [];
			$relatedModel = $this->getRelationModuleModel();
			$relatedModelFields = $relatedModel->getFields();

			foreach ($relatedModelFields as $fieldModel) {
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

	/**
	 * Gets relation data fields.
	 *
	 * @return array
	 */
	public function getRelationFields(): array
	{
		return method_exists($this->getTypeRelationModel(), 'getFields') ? $this->getTypeRelationModel()->getFields() : [];
	}

	/**
	 * Set conditions for relation fields.
	 *
	 * @param array $conditions
	 *
	 * @return self
	 */
	public function setRelationConditions(array $conditions): self
	{
		$group = 'and';
		$relFields = $this->getRelationFields();
		foreach ($conditions as $groupInfo) {
			if (empty($groupInfo) || !array_filter($groupInfo)) {
				$group = 'or';
				continue;
			}
			$dataGroup = [$group];
			foreach ($groupInfo as $fieldSearchInfo) {
				[$fieldName, $operator, $fieldValue] = array_pad($fieldSearchInfo, 3, false);
				$field = $relFields[$fieldName] ?? null;
				if (!$field || (($className = '\App\Conditions\QueryFields\\' . ucfirst($field->getFieldDataType()) . 'Field') && !class_exists($className))) {
					continue;
				}
				$queryField = new $className($this->getQueryGenerator(), $field);
				$queryField->setValue($fieldValue);
				$queryField->setOperator($operator);
				$condition = $queryField->getCondition();
				if ($condition) {
					$dataGroup[] = $condition;
				}
			}
			$this->getQueryGenerator()->addNativeCondition($dataGroup);
		}
		return $this;
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
		$db = App\Db::getInstance();
		$moduleName = $this->getParentModuleModel()->get('name');
		$result = false;
		if ('add' === $action) {
			$result = $db->createCommand()->insert('u_#__favorites', [
				'data' => date('Y-m-d H:i:s'),
				'crmid' => $data['crmid'],
				'module' => $moduleName,
				'relcrmid' => $data['relcrmid'],
				'relmodule' => $this->getRelationModuleName(),
				'userid' => App\User::getCurrentUserId(),
			])->execute();
		} elseif ('delete' === $action) {
			$result = $db->createCommand()->delete('u_#__favorites', [
				'crmid' => $data['crmid'],
				'module' => $moduleName,
				'relcrmid' => $data['relcrmid'],
				'relmodule' => $this->getRelationModuleName(),
				'userid' => App\User::getCurrentUserId(),
			])->execute();
		}
		return $result;
	}

	public static function updateStateFavorites($relationId, $status)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_relatedlists', ['favorites' => $status], ['relation_id' => $relationId])->execute();
		\App\Relation::clearCacheById($relationId);
	}

	/**
	 * Get custom view list.
	 *
	 * @return string[]
	 */
	public function getCustomViewList(): array
	{
		if (isset($this->customViewList)) {
			return $this->customViewList;
		}
		$cv = [];
		$selectedCv = $this->getCustomView();
		if (empty($selectedCv) || \in_array('relation', $selectedCv)) {
			$cv['relation'] = \App\Language::translate('LBL_RECORDS_FROM_RELATION');
			unset($selectedCv[array_search('relation', $selectedCv)]);
		}
		if ($selectedCv) {
			$moduleName = $this->getRelationModuleName();
			$all = CustomView_Record_Model::getAll($moduleName);
			if (\in_array('all', $selectedCv)) {
				unset($selectedCv[array_search('all', $selectedCv)]);
				foreach ($all as $cvId => $cvModel) {
					$cv[$cvId] = \App\Language::translate($cvModel->get('viewname'), $moduleName);
				}
			} elseif (\in_array('private', $selectedCv)) {
				unset($selectedCv[array_search('private', $selectedCv)]);
				foreach ($all as $cvId => $cvModel) {
					if ($cvModel->isMine()) {
						$cv[$cvId] = \App\Language::translate($cvModel->get('viewname'), $moduleName);
					}
				}
			}
			foreach ($selectedCv as $cvId) {
				if (isset($all[$cvId])) {
					$cv[$cvId] = \App\Language::translate($all[$cvId]->get('viewname'), $moduleName);
				}
			}
		}
		return $this->customViewList = $cv;
	}
}
