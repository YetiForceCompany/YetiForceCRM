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

class Vtiger_RelationListView_Model extends \App\Base
{
	/**
	 * Relation model instance.
	 *
	 * @var Vtiger_Relation_Model
	 */
	protected $relationModel;

	/**
	 * Parent record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $parentRecordModel;

	/**
	 * Related module model instance.
	 *
	 * @var Vtiger_Module_Model
	 */
	protected $relatedModuleModel;

	/**
	 * Mandatory columns.
	 *
	 * @var array
	 */
	protected $mandatoryColumns = [];

	/**
	 * Set relation model instance.
	 *
	 * @param Vtiger_Relation_Model $relation
	 *
	 * @return $this
	 */
	public function setRelationModel($relation)
	{
		$this->relationModel = $relation;

		return $this;
	}

	/**
	 * Get relation model instance.
	 *
	 * @return Vtiger_Relation_Model
	 */
	public function getRelationModel()
	{
		return $this->relationModel;
	}

	/**
	 * Set parent record model instance.
	 *
	 * @param Vtiger_Record_Model $parentRecord
	 *
	 * @return $this
	 */
	public function setParentRecordModel($parentRecord)
	{
		$this->parentRecordModel = $parentRecord;

		return $this;
	}

	/**
	 * Get parent record model instance.
	 *
	 * @return Vtiger_Record_Model
	 */
	public function getParentRecordModel()
	{
		return $this->parentRecordModel;
	}

	/**
	 * Set related module model instance.
	 *
	 * @param Vtiger_Module_Model $relatedModuleModel
	 *
	 * @return $this
	 */
	public function setRelatedModuleModel($relatedModuleModel)
	{
		$this->relatedModuleModel = $relatedModuleModel;

		return $this;
	}

	/**
	 * Get related module model instance.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getRelatedModuleModel()
	{
		return $this->relatedModuleModel;
	}

	/**
	 * Get query generator instance.
	 *
	 * @return \App\QueryGenerator
	 */
	public function getQueryGenerator()
	{
		return $this->get('query_generator');
	}

	/**
	 * Get relation list view model instance.
	 *
	 * @param Vtiger_Module_Model $parentRecordModel
	 * @param Vtiger_Module_Model $relationModuleName
	 * @param bool|string         $label
	 *
	 * @return self
	 */
	public static function getInstance($parentRecordModel, $relationModuleName, $label = false)
	{
		$parentModuleName = $parentRecordModel->getModule()->get('name');
		$className = Vtiger_Loader::getComponentClassName('Model', 'RelationListView', $parentModuleName);
		$instance = new $className();

		$parentModuleModel = $parentRecordModel->getModule();
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relationModuleName);
		$instance->setRelatedModuleModel($relatedModuleModel);

		$relationModelInstance = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModuleModel, $label);
		$instance->setParentRecordModel($parentRecordModel);
		if (!$relationModelInstance) {
			return false;
		}
		$queryGenerator = new \App\QueryGenerator($relatedModuleModel->getName());
		$relationModelInstance->set('query_generator', $queryGenerator);
		$relationModelInstance->set('parentRecord', $parentRecordModel);
		$instance->setRelationModel($relationModelInstance)->set('query_generator', $queryGenerator);

		return $instance;
	}

	/**
	 * Function to get Relation query.
	 *
	 * @param mixed $returnQueryGenerator
	 *
	 * @return \App\Db\Query|\App\QueryGenerator
	 */
	public function getRelationQuery($returnQueryGenerator = false)
	{
		if ($this->has('Query')) {
			return $this->get('Query');
		}
		$this->loadCondition();
		$this->loadOrderBy();
		$relationModelInstance = $this->getRelationModel();
		if (!empty($relationModelInstance) && $relationModelInstance->get('name')) {
			$queryGenerator = $relationModelInstance->getQuery();
			$relationModuleName = $queryGenerator->getModule();
			if (isset($this->mandatoryColumns[$relationModuleName])) {
				foreach ($this->mandatoryColumns[$relationModuleName] as &$columnName) {
					$queryGenerator->setCustomColumn($columnName);
				}
			}
			if ($returnQueryGenerator) {
				return $queryGenerator;
			}
			$query = $queryGenerator->createQuery();
			$this->set('Query', $query);

			return $query;
		}
		throw new \App\Exceptions\AppException('>>> No relationModel instance, requires verification 2 <<<');
	}

	/**
	 * Load list view conditions.
	 */
	public function loadCondition()
	{
		$relatedModuleName = $this->getRelatedModuleModel()->getName();
		$queryGenerator = $this->getRelationModel()->getQueryGenerator();
		if ($entityState = $this->get('entityState')) {
			$queryGenerator->setStateCondition($entityState);
		}
		if ($relatedModuleName === $this->get('src_module') && !$this->isEmpty('src_record')) {
			$queryGenerator->addCondition('id', $this->get('src_record'), 'n');
		}
		if ($searchParams = $this->getArray('search_params')) {
			$queryGenerator->parseAdvFilter($searchParams);
		}
		if (!$this->isEmpty('search_key')) {
			$queryGenerator->addBaseSearchConditions($this->get('search_key'), $this->get('search_value'), $this->get('operator'));
		}
	}

	/**
	 * Function to get the related list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return Vtiger_Record_Model[]
	 */
	public function getEntries(Vtiger_Paging_Model $pagingModel)
	{
		$relationModuleModel = $this->getRelationModel()->getRelationModuleModel();
		$pageLimit = $pagingModel->getPageLimit();
		$query = $this->getRelationQuery();
		if (0 !== $pagingModel->get('limit')) {
			$query->limit($pageLimit + 1)->offset($pagingModel->getStartIndex());
		}
		$rows = $query->all();
		$count = count($rows);
		$pagingModel->calculatePageRange($count);
		if ($count > $pageLimit) {
			array_pop($rows);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$relatedRecordList = [];
		foreach ($rows as $row) {
			$recordModel = $relationModuleModel->getRecordFromArray($row);
			$this->getEntryExtend($recordModel);
			$relatedRecordList[$row['id']] = $recordModel;
		}
		return $relatedRecordList;
	}

	/**
	 * Function extending recordModel object with additional information.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function getEntryExtend(Vtiger_Record_Model $recordModel)
	{
	}

	/**
	 * Set list view order by.
	 */
	public function loadOrderBy()
	{
		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy)) {
			$field = $this->getRelationModel()->getRelationModuleModel()->getFieldByColumn($orderBy);
			if ($field) {
				$orderBy = $field->getName();
			}
			if ($field || 'id' === $orderBy) {
				return $this->getRelationModel()->getQueryGenerator()->setOrder($orderBy, $this->getForSql('sortorder'));
			}
			\App\Log::warning("[RelationListView] Incorrect value of sorting: '$orderBy'");
		}
	}

	/**
	 * Get header fields.
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public function getHeaders()
	{
		$fields = $this->getRelationModel()->getQueryFields();
		unset($fields['id']);
		foreach ($fields as $fieldName => &$fieldModel) {
			if (!$fieldModel->isViewable()) {
				unset($fields[$fieldName]);
			}
		}
		return $fields;
	}

	/**
	 * Function to get Total number of record in this relation.
	 *
	 * @return int
	 */
	public function getRelatedEntriesCount()
	{
		return $this->getRelationQuery()->count();
	}

	/**
	 * Get tree view model.
	 *
	 * @return Vtiger_TreeCategoryModal_Model
	 */
	public function getTreeViewModel()
	{
		return Vtiger_TreeCategoryModal_Model::getInstance($this->getRelatedModuleModel());
	}

	/**
	 * Get tree headers.
	 *
	 * @return string[]
	 */
	public function getTreeHeaders()
	{
		$fields = $this->getTreeViewModel()->getTreeField();

		return [
			'name' => $fields['fieldlabel'],
		];
	}

	/**
	 * Get tree entries.
	 *
	 * @return array[]
	 */
	public function getTreeEntries()
	{
		$relModuleName = $this->getRelatedModuleModel()->getName();
		$relationModelInstance = $this->getRelationModel();
		$template = $this->getTreeViewModel()->getTemplate();
		$showCreatorDetail = $relationModelInstance->get('creator_detail');
		$showComment = $relationModelInstance->get('relation_comment');

		$rows = $relationModelInstance->getRelationTree();
		$trees = [];
		foreach ($rows as &$row) {
			$pieces = explode('::', $row['parentTree']);
			end($pieces);
			$parent = prev($pieces);
			$parentName = '';
			if ($row['depth'] > 0) {
				$treeDetail = App\Fields\Tree::getValueByTreeId($template, $parent);
				$parentName = '(' . App\Language::translate($treeDetail['name'], $relModuleName) . ') ';
			}
			$tree = [
				'id' => $row['tree'],
				'name' => $parentName . App\Language::translate($row['name'], $relModuleName),
				'parent' => 0 == $parent ? '#' : $parent,
			];
			if ($showCreatorDetail) {
				$tree['rel_created_user'] = \App\Fields\Owner::getLabel($row['rel_created_user']);
				$tree['rel_created_time'] = App\Fields\DateTime::formatToDisplay($row['rel_created_time']);
			}
			if ($showComment) {
				$tree['rel_comment'] = $row['rel_comment'];
			}
			if (!empty($row['icon'])) {
				$tree['icon'] = $row['icon'];
			}
			$trees[] = $tree;
		}
		return $trees;
	}

	/**
	 * Function to get Total number of record in this relation.
	 *
	 * @return int
	 */
	public function getRelatedTreeEntriesCount()
	{
		$recordId = $this->getParentRecordModel()->getId();
		$relModuleId = $this->getRelatedModuleModel()->getId();
		$treeViewModel = $this->getTreeViewModel();
		$template = $treeViewModel->getTemplate();

		return (new \App\Db\Query())->from('vtiger_trees_templates_data ttd')->innerJoin('u_#__crmentity_rel_tree rel', 'rel.tree = ttd.tree')
			->where(['ttd.templateid' => $template, 'rel.crmid' => $recordId, 'rel.relmodule' => $relModuleId])->count();
	}

	public function getCreateViewUrl()
	{
		$relationModelInstance = $this->getRelationModel();
		$relatedModel = $relationModelInstance->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateRecordUrl() . '&sourceModule=' . $parentModule->get('name') .
			'&sourceRecord=' . $parentRecordModule->getId() . '&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if ($relationModelInstance->isDirectRelation()) {
			$relationField = $relationModelInstance->getRelationField();
			$createViewUrl .= '&' . $relationField->getName() . '=' . $parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	/**
	 * Function to get the links for related list.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getLinks()
	{
		$relationModelInstance = $this->getRelationModel();
		$relatedModuleName = $relationModelInstance->getRelationModuleModel()->getName();
		$id = $this->getParentRecordModel()->getId();
		$selectLinks = $this->getSelectRelationLinks();
		foreach ($selectLinks as $selectLinkModel) {
			$selectLinkModel->set('_selectRelation', true)->set('_module', $relationModelInstance->getRelationModuleModel());
		}
		$relatedLink = [];
		$relatedLink['RELATEDLIST_VIEWS'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'RELATEDLIST_VIEWS',
			'linklabel' => 'LBL_RECORDS_LIST',
			'view' => 'List',
			'linkicon' => 'far fa-list-alt',
		]);
		$relatedLink['RELATEDLIST_VIEWS'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'RELATEDLIST_VIEWS',
			'linklabel' => 'LBL_RECORDS_PREVIEW_LIST',
			'view' => 'ListPreview',
			'linkicon' => 'fas fa-desktop',
		]);
		$relatedLink['LISTVIEWBASIC'] = array_merge($selectLinks, $this->getAddRelationLinks());
		$relatedLink['RELATEDLIST_MASSACTIONS'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'RELATEDLIST_MASSACTIONS',
			'linklabel' => 'LBL_MASS_DELETE',
			'linkurl' => "javascript:Vtiger_RelatedList_Js.triggerMassAction('index.php?module=Campaigns&action=RelationAjax&mode=massDeleteRelation&src_record={$id}&relatedModule={$relatedModuleName}')",
			'linkclass' => '',
			'linkicon' => 'fas fa-eraser',
		]);
		$relatedLink['RELATEDLIST_MASSACTIONS_ADV'][] = Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'RELATEDLIST_MASSACTIONS_ADV',
			'linklabel' => 'LBL_QUICK_EXPORT_TO_EXCEL',
			'linkurl' => "javascript:Vtiger_RelatedList_Js.triggerMassAction('index.php?module=Campaigns&action=RelationAjax&mode=exportToExcel&src_record={$id}&relatedModule={$relatedModuleName}','sendByForm')",
			'linkclass' => '',
			'linkicon' => 'fas fa-file-excel',
		]);

		return $relatedLink;
	}

	public function getSelectRelationLinks()
	{
		$relationModelInstance = $this->getRelationModel();
		$selectLinkModel = [];

		if (!$relationModelInstance->isSelectActionSupported()) {
			return $selectLinkModel;
		}

		$relatedModel = $relationModelInstance->getRelationModuleModel();
		if (!$relatedModel->isPermitted('DetailView')) {
			return $selectLinkModel;
		}

		$selectLinkList = [
			[
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => \App\Language::translate('LBL_SELECT_RELATION', $relatedModel->getName()),
				'linkurl' => '',
				'linkicon' => 'fas fa-level-up-alt',
			],
		];

		foreach ($selectLinkList as $selectLink) {
			$selectLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($selectLink);
		}
		return $selectLinkModel;
	}

	public function getAddRelationLinks()
	{
		$relationModelInstance = $this->getRelationModel();
		$addLinkModel = [];

		if (!$relationModelInstance->isAddActionSupported()) {
			return $addLinkModel;
		}
		$relatedModel = $relationModelInstance->getRelationModuleModel();
		if (!$relatedModel->isPermitted('CreateView')) {
			return $addLinkModel;
		}

		$addLinkList = [[
			'linktype' => 'LISTVIEWBASIC',
			// NOTE: $relatedModel->get('label') assuming it to be a module name - we need singular label for Add action.
			//'linklabel' => \App\Language::translate('LBL_ADD')." ".vtranslate'SINGLE_' . $relatedModel->getName(), $relatedModel->getName()),
			'linklabel' => App\Language::translate('LBL_ADD_RELATION', $relatedModel->getName()),
			'linkurl' => $this->getCreateViewUrl(),
			'linkqcs' => $relatedModel->isQuickCreateSupported(),
			'linkicon' => 'fas fa-plus',
		]];

		if ('Documents' === $relatedModel->get('label')) {
			$addLinkList[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => App\Language::translate('LBL_MASS_ADD', 'Documents'),
				'linkurl' => 'javascript:Vtiger_Index_Js.massAddDocuments("index.php?module=Documents&view=MassAddDocuments")',
				'linkicon' => 'adminIcon-document-templates',
			];
		}
		foreach ($addLinkList as &$addLink) {
			$addLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($addLink);
		}
		return $addLinkModel;
	}

	public function getFavoriteRecords()
	{
		return (new App\Db\Query())->select(['relcrmid'])->from('u_#__favorites')
			->where([
				'module' => $this->getParentRecordModel()->getModuleName(),
				'relmodule' => $this->getRelatedModuleModel()->getName(),
				'crmid' => $this->getParentRecordModel()->getId(),
				'userid' => App\User::getCurrentUserId(),
			])
			->column();
	}

	/**
	 * Set fileds.
	 *
	 * @param string|string[] $fields
	 */
	public function setFields($fields)
	{
		if (is_string($fields)) {
			$fields = explode(',', $fields);
		}
		$relatedListFields = [];
		foreach ($fields as $fieldName) {
			$fieldModel = $this->relatedModuleModel->getFieldByName($fieldName);
			if ($fieldModel) {
				$relatedListFields[$fieldName] = $fieldModel;
			}
		}
		$this->relationModel->set('QueryFields', $relatedListFields);
	}
}
