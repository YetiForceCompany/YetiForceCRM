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

class Vtiger_Relation_Model extends \App\Base
{
	protected static $cachedInstances = [];
	protected $parentModule = false;
	protected $relatedModule = false;
	/**
	 * @var object Class that includes basic operations on relations
	 */
	protected $typeRelationModel;

	//one to many
	const RELATION_O2M = 1;
	//Many to many and many to one
	const RELATION_M2M = 2;

	/** @var string[] */
	protected static $RELATIONS_O2M = ['getDependentsList'];

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
		return $this->isActionSupported('add');
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
	public function getQueryGenerator()
	{
		if (!$this->has('query_generator')) {
			$this->set('query_generator', new \App\QueryGenerator($this->getRelationModuleName()));
		}
		return $this->get('query_generator');
	}

	/**
	 * Get relation type.
	 *
	 * @return self::RELATION_O2M|self::RELATION_M2M
	 */
	public function getRelationType()
	{
		if (!$this->get('relationType')) {
			if (\in_array($this->get('name'), self::$RELATIONS_O2M) || $this->getRelationField()) {
				$this->set('relationType', self::RELATION_O2M);
			} else {
				$this->set('relationType', self::RELATION_M2M);
			}
		}
		return $this->get('relationType');
	}

	/**
	 * Get related view type.
	 *
	 * @return string[]
	 */
	public function getRelatedViewType()
	{
		return explode(',', $this->get('view_type'));
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
	 * Get relation list model instance.
	 *
	 * @param Vtiger_Module_Model $parentModuleModel
	 * @param Vtiger_Module_Model $relatedModuleModel
	 * @param bool|string         $label
	 *
	 * @return self|false
	 */
	public static function getInstance($parentModuleModel, $relatedModuleModel, $label = false)
	{
		$relKey = $parentModuleModel->getId() . '_' . $relatedModuleModel->getId() . '_' . ($label ? 1 : 0);
		if (isset(self::$cachedInstances[$relKey])) {
			return self::$cachedInstances[$relKey];
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

			return $relationModel;
		}
		$query = (new \App\Db\Query())->select(['vtiger_relatedlists.*', 'modulename' => 'vtiger_tab.name'])
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
	 * Gets relation object.
	 */
	public function getTypeRelationModel()
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
		if (!($object = $this->getTypeRelationModel())) {
			App\Log::error("Not exist relation: {$this->get('name')} in " . __METHOD__);
			throw new \App\Exceptions\NotAllowedMethod('LBL_NOT_EXIST_RELATION: ' . $this->get('name'));
		}
		if (empty($this->get('parentRecord'))) {
			App\Log::error('No value parentRecord in ' . __METHOD__);
			throw new \App\Exceptions\IllegalValue('ERR_NO_VALUE||parentRecord');
		}

		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->setSourceRecord($this->get('parentRecord')->getId());
		$object->getQuery();

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
		$relatedModuleName = $relatedModuleModel->getName();
		$fieldRel = App\Field::getRelatedFieldForModule($relatedModuleName, $parentModuleName);
		$relatedModelFields = $relatedModuleModel->getFields();
		if (isset($fieldRel['fieldid'])) {
			foreach ($relatedModelFields as &$fieldModel) {
				if ($fieldModel->getId() === $fieldRel['fieldid']) {
					$relationField = $fieldModel;
					break;
				}
			}
		}
		if (!isset($relationField) || !$relationField) {
			$relationField = false;
			foreach ($relatedModelFields as &$fieldModel) {
				if ($fieldModel->isReferenceField()) {
					$referenceList = $fieldModel->getReferenceList();
					if (!empty($referenceList) && \in_array($parentModuleName, $referenceList)) {
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
	public function isEditable()
	{
		return $this->getRelationModuleModel()->isPermitted('EditView');
	}

	/**
	 * Function which will specify whether the relation is deletable.
	 *
	 * @return bool
	 */
	public function privilegeToDelete(): bool
	{
		$returnVal = $this->getRelationModuleModel()->isPermitted('RemoveRelation');
		if ($returnVal && $this->getRelationType() === static::RELATION_O2M && ($fieldModel = $this->getRelationField())) {
			$returnVal = !$fieldModel->isMandatory() && $fieldModel->isEditable() && !$fieldModel->isEditableReadOnly();
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

	public function getListUrl($parentRecordModel)
	{
		$url = 'module=' . $this->getParentModuleModel()->get('name') . '&relatedModule=' . $this->get('modulename') .
			'&view=Detail&record=' . $parentRecordModel->getId() . '&mode=showRelatedList&tab_label=' . $this->get('label');
		if ('Calendar' == $this->get('modulename')) {
			$url .= '&time=current';
		}
		return $url;
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
		$typeRelation = $this->getTypeRelationModel();
		if (!\is_array($destinationRecordIds)) {
			$destinationRecordIds = [$destinationRecordIds];
		}
		$data = [
			'CRMEntity' => $this->getParentModuleModel()->getEntityInstance(),
			'sourceModule' => $sourceModuleName,
			'sourceRecordId' => $sourceRecordId,
			'destinationModule' => $this->getRelationModuleModel()->getName(),
		];
		$eventHandler = new \App\EventHandler();
		$eventHandler->setModuleName($sourceModuleName);
		foreach ($destinationRecordIds as $destinationRecordId) {
			$data['destinationRecordId'] = $destinationRecordId;
			$eventHandler->setParams($data);
			$eventHandler->trigger('EntityBeforeLink');
			if ($result = $typeRelation->create($sourceRecordId, $destinationRecordId)) {
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
			'destinationRecordId' => $relId];
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
	 * @param \Vtiger_Module_Model $parentModuleModel
	 * @param bool                 $selected
	 * @param bool                 $onlyActive
	 * @param bool                 $permissions
	 *
	 * @return \Vtiger_Relation_Model[]
	 */
	public static function getAllRelations(Vtiger_Module_Model $parentModuleModel, bool $selected = true, bool $onlyActive = true, bool $permissions = true)
	{
		$cacheName = "{$parentModuleModel->getId()}:$selected:$onlyActive";
		if (\App\Cache::has('getAllRelations', $cacheName)) {
			$relationList = \App\Cache::get('getAllRelations', $cacheName);
		} else {
			$query = new \App\Db\Query();
			$query->select(['vtiger_relatedlists.*', 'modulename' => 'vtiger_tab.name', 'moduleid' => 'vtiger_tab.tabid'])
				->from('vtiger_relatedlists')
				->innerJoin('vtiger_tab', 'vtiger_relatedlists.related_tabid = vtiger_tab.tabid')
				->where(['vtiger_relatedlists.tabid' => $parentModuleModel->getId()]);
			if ($selected) {
				$query->andWhere(['<>', 'vtiger_relatedlists.presence', 1]);
			}
			if ($onlyActive) {
				$query->andWhere(['<>', 'vtiger_tab.presence', 1]);
			}
			$relationList = $query->orderBy('sequence')->all();
			\App\Cache::save('getAllRelations', $cacheName, $relationList);
		}
		$relationModels = [];
		$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
		$privilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		foreach ($relationList as $row) {
			// Skip relation where target module does not exits or is no permitted for view.
			if ($permissions && !$privilegesModel->hasModuleActionPermission($row['moduleid'], 'DetailView')) {
				continue;
			}
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->set('relatedModuleName', $row['modulename']);
			$relationModels[$row['related_tabid']] = $relationModel;
		}
		return $relationModels;
	}

	public function getAutoCompleteField($recordModel)
	{
		$fields = [];
		$fieldsReferenceList = [];
		$excludedModules = ['Users'];
		$relatedModel = $this->getRelationModuleModel();
		$relatedModuleName = $relatedModel->getName();

		$parentModelFields = $this->getParentModuleModel()->getFields();
		foreach ($parentModelFields as $fieldName => $fieldModel) {
			if ($fieldModel->isReferenceField()) {
				$referenceList = $fieldModel->getReferenceList();
				foreach ($referenceList as $module) {
					if (!\in_array($module, $excludedModules) && 'userCreator' !== !$fieldModel->getFieldDataType()) {
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
					if (\array_key_exists($module, $fieldsReferenceList) && $module != $recordModel->getModuleName()) {
						$parentFieldModel = $fieldsReferenceList[$module];
						$relId = $recordModel->get($parentFieldModel->getName());
						if ('' != $relId && 0 != $relId) {
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
		\App\Cache::clear();
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
		\App\Cache::clear();
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
		}
		\App\Cache::clear();
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
					'fieldname' => $field['name'],
					'sequence' => $key,
				])->execute();
				$addedFields[] = $field['id'];
			}
		}
		\App\Cache::clear();
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
			$fields = false;
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
		\App\Cache::clear();
	}

	public function isFavorites()
	{
		return 1 == $this->get('favorites') ? true : false;
	}
}
