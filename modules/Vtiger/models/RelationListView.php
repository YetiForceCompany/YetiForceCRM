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

class Vtiger_RelationListView_Model extends Vtiger_Base_Model
{

	protected $relationModel = false;
	protected $parentRecordModel = false;
	protected $relatedModuleModel = false;
	protected $mandatoryColumns = [];

	public function setRelationModel($relation)
	{
		$this->relationModel = $relation;
		return $this;
	}

	/**
	 * Get relation model
	 * @return Vtiger_Relation_Model
	 */
	public function getRelationModel()
	{
		return $this->relationModel;
	}

	public function setParentRecordModel($parentRecord)
	{
		$this->parentRecordModel = $parentRecord;
		return $this;
	}

	/**
	 * Get parent record model
	 * @return Vtiger_Record_Model
	 */
	public function getParentRecordModel()
	{
		return $this->parentRecordModel;
	}

	public function setRelatedModuleModel($relatedModuleModel)
	{
		$this->relatedModuleModel = $relatedModuleModel;
		return $this;
	}

	/**
	 * Function that returns the relation's related module model
	 * @return Vtiger_Module_Model
	 */
	public function getRelatedModuleModel()
	{
		return $this->relatedModuleModel;
	}

	/**
	 * Get query generator instance
	 * @return \App\QueryGenerator
	 */
	public function getQueryGenerator()
	{
		return $this->get('query_generator');
	}

	/**
	 * Get relation list view model instance
	 * @param Vtiger_Module_Model $parentRecordModel
	 * @param Vtiger_Module_Model $relationModuleName
	 * @param string|boolean $label
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

		$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModuleModel, $label);
		$instance->setParentRecordModel($parentRecordModel);
		$queryGenerator = new \App\QueryGenerator($relatedModuleModel->getName());

		if (!$relationModel) {
			throw new \App\Exceptions\AppException(">>> No relationModel instance, requires verification  1 <<<");
			$relatedModuleName = $relatedModuleModel->getName();
			$parentModuleModel = $instance->getParentRecordModel()->getModule();
			$referenceFieldOfParentModule = $parentModuleModel->getFieldsByType('reference');
			foreach ($referenceFieldOfParentModule as $fieldName => $fieldModel) {
				$refredModulesOfReferenceField = $fieldModel->getReferenceList();
				if (in_array($relatedModuleName, $refredModulesOfReferenceField)) {
					$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->getName());
					$relationModel = new $relationModelClassName();
					$relationModel->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
					$parentModuleModel->set('directRelatedFieldName', $fieldModel->get('column'));
				}
			}
		}
		$relationModel->set('query_generator', $queryGenerator);
		$relationModel->set('parentRecord', $parentRecordModel);
		$instance->setRelationModel($relationModel)->set('query_generator', $queryGenerator);
		return $instance;
	}

	/**
	 * Function to get Relation query
	 * @return \App\Db\Query
	 */
	public function getRelationQuery()
	{
		if ($this->has('Query')) {
			return $this->get('Query');
		}
		$this->loadCondition();
		$this->loadOrderBy();
		$relationModel = $this->getRelationModel();
		if (!empty($relationModel) && $relationModel->get('name')) {
			$queryGenerator = $relationModel->getQuery();
			$relationModuleName = $queryGenerator->getModule();
			if (isset($this->mandatoryColumns[$relationModuleName])) {
				foreach ($this->mandatoryColumns[$relationModuleName] as &$columnName) {
					$queryGenerator->setCustomColumn($columnName);
				}
			}
			$query = $queryGenerator->createQuery();
			$this->set('Query', $query);
			return $query;
		}
		throw new \App\Exceptions\AppException(">>> No relationModel instance, requires verification 2 <<<");
		/*
		  $relatedModuleModel = $this->getRelatedModuleModel();

		  $relatedModuleBaseTable = $relatedModuleModel->basetable;
		  $relatedModuleEntityIdField = $relatedModuleModel->basetableid;

		  $parentModuleModel = $relationModel->getParentModuleModel();
		  $parentModuleBaseTable = $parentModuleModel->basetable;
		  $parentModuleEntityIdField = $parentModuleModel->basetableid;
		  $parentRecordId = $this->getParentRecordModel()->getId();
		  $parentModuleDirectRelatedField = $parentModuleModel->get('directRelatedFieldName');

		  $relatedModuleFields = array_keys($this->getHeaders());

		  $queryGenerator->setFields($relatedModuleFields);

		  $joinQuery = ' INNER JOIN ' . $parentModuleBaseTable . ' ON ' . $parentModuleBaseTable . '.' . $parentModuleDirectRelatedField . " = " . $relatedModuleBaseTable . '.' . $relatedModuleEntityIdField;

		  $query = $queryGenerator->getQuery();
		  $queryComponents = preg_split('/FROM/i', $query);
		  foreach ($queryComponents as $key => $val) {
		  if ($key == 0) {
		  $query = sprintf('%s ,vtiger_crmentity.crmid', $queryComponents[0]);
		  } else {
		  $query .= sprintf('FROM %s', $val);
		  }
		  }
		  $whereSplitQueryComponents = preg_split('/WHERE/i', $query);
		  $query = $whereSplitQueryComponents[0] . $joinQuery;
		  foreach ($whereSplitQueryComponents as $key => $val) {
		  if ($key == 0) {
		  $query .= "WHERE $parentModuleBaseTable.$parentModuleEntityIdField = $parentRecordId && ";
		  } else {
		  $query .= $val . ' WHERE ';
		  }
		  }
		  $this->query = trim($query, ' WHERE ');
		  return $this->query;
		 */
	}

	/**
	 * Load list view conditions
	 */
	public function loadCondition()
	{
		$relatedModuleName = $this->getRelatedModuleModel()->getName();
		$queryGenerator = $this->getRelationModel()->getQueryGenerator();
		$srcRecord = $this->get('src_record');
		if ($relatedModuleName === $this->get('src_module') && !empty($srcRecord)) {
			$queryGenerator->addCondition('id', $srcRecord, 'n');
		}
		$searchParams = $this->get('search_params');
		if ($searchParams) {
			$queryGenerator->parseAdvFilter($searchParams);
		}
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if (!empty($searchKey)) {
			$queryGenerator->addBaseSearchConditions($searchKey, $searchValue, $operator);
		}
	}

	/**
	 * Function to get the related list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return Vtiger_Record_Model[]
	 */
	public function getEntries(Vtiger_Paging_Model $pagingModel)
	{
		$relationModel = $this->getRelationModel();
		$relationModuleModel = $relationModel->getRelationModuleModel();
		$pageLimit = $pagingModel->getPageLimit();
		$query = $this->getRelationQuery();
		if ($pagingModel->get('limit') !== 'no_limit') {
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
		foreach ($rows as &$row) {
			$recordModel = $relationModuleModel->getRecordFromArray($row);
			$this->getEntryExtend($recordModel);
			$relatedRecordList[$row['id']] = $recordModel;
		}
		return $relatedRecordList;
	}

	/**
	 * Function extending recordModel object with additional information
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function getEntryExtend(Vtiger_Record_Model $recordModel)
	{
		
	}

	/**
	 * Set list view order by
	 */
	public function loadOrderBy()
	{
		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy)) {
			$relationModule = $this->getRelationModel()->getRelationModuleModel();
			$field = $relationModule->getFieldByColumn($orderBy);
			if ($field) {
				$this->getRelationModel()->getQueryGenerator()->setOrder($field->getName(), $this->getForSql('sortorder'));
			}
		}
	}

	/**
	 * Get header fields
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
	 * Function to get Total number of record in this relation
	 * @return int
	 */
	public function getRelatedEntriesCount()
	{
		return $this->getRelationQuery()->count();
	}

	/**
	 * Get tree view model
	 * @return Vtiger_TreeCategoryModal_Model
	 */
	public function getTreeViewModel()
	{
		return Vtiger_TreeCategoryModal_Model::getInstance($this->getRelatedModuleModel());
	}

	/**
	 * Get tree headers
	 * @return string[]
	 */
	public function getTreeHeaders()
	{
		$fields = $this->getTreeViewModel()->getTreeField();
		return [
			'name' => $fields['fieldlabel']
		];
	}

	/**
	 * Get tree entries
	 * @return array[]
	 */
	public function getTreeEntries()
	{
		$recordId = $this->getParentRecordModel()->getId();
		$relModuleId = $this->getRelatedModuleModel()->getId();
		$relModuleName = $this->getRelatedModuleModel()->getName();
		$relationModel = $this->getRelationModel();
		$template = $this->getTreeViewModel()->getTemplate();
		$showCreatorDetail = $relationModel->showCreatorDetail();
		$showComment = $relationModel->showComment();

		$rows = (new \App\Db\Query())
				->select(['ttd.*', 'rel.crmid', 'rel.rel_created_time', 'rel.rel_created_user', 'rel.rel_comment'])
				->from('vtiger_trees_templates_data ttd')
				->innerJoin('u_#__crmentity_rel_tree rel', 'rel.tree = ttd.tree')
				->where(['ttd.templateid' => $template, 'rel.crmid' => $recordId, 'rel.relmodule' => $relModuleId])->all();
		$trees = [];
		foreach ($rows as &$row) {
			$pieces = explode('::', $row['parenttrre']);
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
				'parent' => $parent == 0 ? '#' : $parent
			];
			if ($showCreatorDetail) {
				$tree['rel_created_user'] = \App\Fields\Owner::getLabel($row['rel_created_user']);
				$tree['rel_created_time'] = Vtiger_Datetime_UIType::getDisplayDateTimeValue($row['rel_created_time']);
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
	 * Function to get Total number of record in this relation
	 * @return int
	 */
	public function getRelatedTreeEntriesCount()
	{
		$db = PearDatabase::getInstance();
		$recordId = $this->getParentRecordModel()->getId();
		$relModuleId = $this->getRelatedModuleModel()->getId();
		$treeViewModel = $this->getTreeViewModel();
		$template = $treeViewModel->getTemplate();
		return (new \App\Db\Query())->from('vtiger_trees_templates_data ttd')->innerJoin('u_#__crmentity_rel_tree rel', 'rel.tree = ttd.tree')
				->where(['ttd.templateid' => $template, 'rel.crmid' => $recordId, 'rel.relmodule' => $relModuleId])->count();
	}

	public function getCreateViewUrl()
	{
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateRecordUrl() . '&sourceModule=' . $parentModule->get('name') .
			'&sourceRecord=' . $parentRecordModule->getId() . '&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if ($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .= '&' . $relationField->getName() . '=' . $parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getCreateEventRecordUrl()
	{
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateEventRecordUrl() . '&sourceModule=' . $parentModule->get('name') .
			'&sourceRecord=' . $parentRecordModule->getId() . '&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if ($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .= '&' . $relationField->getName() . '=' . $parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getCreateTaskRecordUrl()
	{
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateTaskRecordUrl() . '&sourceModule=' . $parentModule->get('name') .
			'&sourceRecord=' . $parentRecordModule->getId() . '&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if ($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .= '&' . $relationField->getName() . '=' . $parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getLinks()
	{
		$relationModel = $this->getRelationModel();
		$actions = $relationModel->getActions();

		$selectLinks = $this->getSelectRelationLinks();
		foreach ($selectLinks as $selectLinkModel) {
			$selectLinkModel->set('_selectRelation', true)->set('_module', $relationModel->getRelationModuleModel());
		}
		$addLinks = $this->getAddRelationLinks();

		$links = array_merge($selectLinks, $addLinks);
		$relatedLink = [];
		$relatedLink['LISTVIEWBASIC'] = $links;
		return $relatedLink;
	}

	public function getSelectRelationLinks()
	{
		$relationModel = $this->getRelationModel();
		$selectLinkModel = [];

		if (!$relationModel->isSelectActionSupported()) {
			return $selectLinkModel;
		}

		$relatedModel = $relationModel->getRelationModuleModel();
		if (!$relatedModel->isPermitted('DetailView')) {
			return $selectLinkModel;
		}

		$selectLinkList = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_SELECT_RELATION', $relatedModel->getName()),
				'linkurl' => '',
				'linkicon' => '',
			)
		);

		foreach ($selectLinkList as $selectLink) {
			$selectLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($selectLink);
		}
		return $selectLinkModel;
	}

	public function getAddRelationLinks()
	{
		$relationModel = $this->getRelationModel();
		$addLinkModel = [];

		if (!$relationModel->isAddActionSupported()) {
			return $addLinkModel;
		}
		$relatedModel = $relationModel->getRelationModuleModel();
		if (!$relatedModel->isPermitted('CreateView')) {
			return $addLinkModel;
		}

		if ($relatedModel->get('label') == 'Calendar') {
			$addLinkList[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => App\Language::translate('LBL_ADD_EVENT'),
				'linkurl' => $this->getCreateEventRecordUrl(),
				'linkqcs' => $relatedModel->isQuickCreateSupported(),
				'linkicon' => ''
			];
			$addLinkList[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => App\Language::translate('LBL_ADD_TASK'),
				'linkurl' => $this->getCreateTaskRecordUrl(),
				'linkqcs' => $relatedModel->isQuickCreateSupported(),
				'linkicon' => ''
			];
		} else {
			$addLinkList = [[
				'linktype' => 'LISTVIEWBASIC',
				// NOTE: $relatedModel->get('label') assuming it to be a module name - we need singular label for Add action.
				//'linklabel' => vtranslate('LBL_ADD')." ".vtranslate('SINGLE_' . $relatedModel->getName(), $relatedModel->getName()),
				'linklabel' => App\Language::translate('LBL_ADD_RELATION'),
				'linkurl' => $this->getCreateViewUrl(),
				'linkqcs' => $relatedModel->isQuickCreateSupported(),
				'linkicon' => ''
			]];
		}
		if ($relatedModel->get('label') === 'Documents') {
			$addLinkList[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => App\Language::translate('LBL_MASS_ADD', 'Documents'),
				'linkurl' => 'javascript:Vtiger_Index_Js.massAddDocuments("index.php?module=Documents&view=MassAddDocuments")',
				'linkicon' => 'glyphicon glyphicon-plus',
			];
		}
		foreach ($addLinkList as &$addLink) {
			$addLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($addLink);
		}
		return $addLinkModel;
	}

	public function getCurrencySymbol($recordId, $fieldModel)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $fieldModel->getModuleName();
		$fieldName = $fieldModel->get('name');
		$tableName = $fieldModel->get('table');
		$columnName = $fieldModel->get('column');

		if (($fieldName == 'currency_id') && ($moduleName == 'Products' || $moduleName == 'Services')) {
			$query = "SELECT currency_symbol FROM vtiger_currency_info WHERE id = (";
			if ($moduleName == 'Products')
				$query .= "SELECT currency_id FROM vtiger_products WHERE productid = ?)";
			else if ($moduleName == 'Services')
				$query .= "SELECT currency_id FROM vtiger_service WHERE serviceid = ?)";

			$result = $db->pquery($query, array($recordId));
			return $db->query_result($result, 0, 'currency_symbol');
		} else {
			$fieldInfo = $fieldModel->getFieldInfo();
			return $fieldInfo['currency_symbol'];
		}
	}

	public function getFavoriteRecords()
	{
		return (new App\Db\Query())->select(['relcrmid'])->from('u_#__favorites')
				->where([
					'module' => $this->getParentRecordModel()->getModuleName(),
					'relmodule' => $this->getRelatedModuleModel()->getName(),
					'crmid' => $this->getParentRecordModel()->getId(),
					'userid' => App\User::getCurrentUserId()])
				->column();
	}
}
