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

	public function getRelationModel()
	{
		return $this->relationModel;
	}

	public function setParentRecordModel($parentRecord)
	{
		$this->parentRecordModel = $parentRecord;
		return $this;
	}

	public function getParentRecordModel()
	{
		return $this->parentRecordModel;
	}

	public function setRelatedModuleModel($relatedModuleModel)
	{
		$this->relatedModuleModel = $relatedModuleModel;
		return $this;
	}

	public function getRelatedModuleModel()
	{
		return $this->relatedModuleModel;
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

		if (!$relationModel) {
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
		$queryGenerator = new \App\QueryGenerator($relatedModuleModel->getName());
		$instance->setRelationModel($relationModel ? $relationModel : false)->set('query_generator', $queryGenerator);
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
			$relationModel->set('query_generator', $this->get('query_generator'));
			$relationModel->set('parentRecord', $this->getParentRecordModel());
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
		die(">>> No relationModel instance, requires verification <<<");
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
		$queryGenerator = $this->get('query_generator');
		$srcRecord = $this->get('src_record');
		if ($relatedModuleName === $this->get('src_module') && !empty($srcRecord)) {
			$queryGenerator->addCondition('id', $srcRecord, 'n');
		}
		$searchParams = $this->get('search_params');
		if (!$searchParams) {
			$queryGenerator->parseAdvFilter($searchParams);
		}
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if (!empty($searchKey)) {
			$queryGenerator->addBaseSearchConditions($searchKey, $searchValue, $operator);
		}
	}

	public function getEntries($pagingModel)
	{
		$relationModel = $this->getRelationModel();
		$relationModule = $relationModel->getRelationModuleModel();
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
			$relatedRecordList[$row['id']] = $relationModule->getRecordFromArray($row);
		}
		$sql = $query->createCommand()->getRawSql();
		echo "<code>";
		var_dump($sql);
		echo "</code>";
		return $relatedRecordList;
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
				$this->get('query_generator')->setOrder($field->getName(), $this->getForSql('sortorder'));
			}
		}
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
				'linklabel' => vtranslate('LBL_ADD_EVENT'),
				'linkurl' => $this->getCreateEventRecordUrl(),
				'linkqcs' => $relatedModel->isQuickCreateSupported(),
				'linkicon' => ''
			];
			$addLinkList[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_ADD_TASK'),
				'linkurl' => $this->getCreateTaskRecordUrl(),
				'linkqcs' => $relatedModel->isQuickCreateSupported(),
				'linkicon' => ''
			];
		} else {
			$addLinkList = [[
				'linktype' => 'LISTVIEWBASIC',
				// NOTE: $relatedModel->get('label') assuming it to be a module name - we need singular label for Add action.
				//'linklabel' => vtranslate('LBL_ADD')." ".vtranslate('SINGLE_' . $relatedModel->getName(), $relatedModel->getName()),
				'linklabel' => vtranslate('LBL_ADD_RELATION'),
				'linkurl' => $this->getCreateViewUrl(),
				'linkqcs' => $relatedModel->isQuickCreateSupported(),
				'linkicon' => ''
			]];
		}

		foreach ($addLinkList as &$addLink) {
			$addLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($addLink);
		}
		return $addLinkModel;
	}

	public function getHeaders()
	{
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relationFields = $relationModel->getRelationFields(true);

		$headerFields = [];
		if (count($relationFields) > 0) {
			foreach ($relationFields as $fieldName) {
				$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
			}
			return $headerFields;
		}
		$summaryFieldsList = $relatedModuleModel->getSummaryViewFieldsList();
		if (count($summaryFieldsList) > 0) {
			foreach ($summaryFieldsList as $fieldName => $fieldModel) {
				$headerFields[$fieldName] = $fieldModel;
			}
		} else {
			$headerFieldNames = $relatedModuleModel->getRelatedListFields();
			foreach ($headerFieldNames as $fieldName) {
				$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
			}
		}
		return $headerFields;
	}

	/**
	 * Function to get Total number of record in this relation
	 * @return <Integer>
	 */
	public function getRelatedEntriesCount()
	{
		$db = PearDatabase::getInstance();
		$relationQuery = $this->getRelationQuery();
		$relationQuery = preg_replace("/[ \t\n\r]+/", ' ', $relationQuery);
		$position = stripos($relationQuery, ' FROM ');
		if ($position) {
			$split = preg_split('/FROM/i', $relationQuery, 2);
			$splitCount = count($split);
			$relationQuery = 'SELECT COUNT(DISTINCT vtiger_crmentity.crmid) AS count';
			for ($i = 1; $i < $splitCount; $i++) {
				$relationQuery = $relationQuery . ' FROM ' . $split[$i];
			}
		}
		if (strpos($relationQuery, ' GROUP BY ') !== false) {
			$parts = explode(' GROUP BY ', $relationQuery);
			$relationQuery = $parts[0];
		}
		$result = $db->query($relationQuery);
		return $db->getSingleValue($result);
	}

	/**
	 * Function to get Total number of record in this relation
	 * @return <Integer>
	 */
	public function getRelatedTreeEntriesCount()
	{
		$db = PearDatabase::getInstance();
		$recordId = $this->getParentRecordModel()->getId();
		$relModuleId = $this->getRelatedModuleModel()->getId();
		$treeViewModel = $this->getTreeViewModel();
		$template = $treeViewModel->getTemplate();
		$result = $db->pquery('SELECT count(1) FROM vtiger_trees_templates_data tr '
			. 'INNER JOIN u_yf_crmentity_rel_tree rel ON rel.tree = tr.tree '
			. 'WHERE tr.templateid = ? && rel.crmid = ? && rel.relmodule = ?', [$template, $recordId, $relModuleId]);
		return $db->getSingleValue($result);
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
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$recordId = $this->getParentRecordModel()->getId();
		$relModuleName = $this->getRelatedModuleModel()->getName();
		$moduleName = $this->getParentRecordModel()->getModuleName();

		$query = 'SELECT `relcrmid` FROM `u_yf_favorites` WHERE u_yf_favorites.module = ? 
		AND u_yf_favorites.relmodule = ? 
		AND u_yf_favorites.crmid = ? 
		AND u_yf_favorites.userid = ?';
		$result = $db->pquery($query, [$moduleName, $relModuleName, $recordId, $currentUser->getId()]);
		return $db->getArrayColumn($result, 'relcrmid');
	}

	public function getTreeViewModel()
	{
		return Vtiger_TreeCategoryModal_Model::getInstance($this->getRelatedModuleModel());
	}

	public function getTreeHeaders()
	{
		$fields = $this->getTreeViewModel()->getTreeField();
		return [
			'name' => $fields['fieldlabel']
		];
	}

	public function getTreeEntries()
	{
		$db = PearDatabase::getInstance();
		$recordId = $this->getParentRecordModel()->getId();
		$relModuleId = $this->getRelatedModuleModel()->getId();
		$relModuleName = $this->getRelatedModuleModel()->getName();
		$treeViewModel = $this->getTreeViewModel();
		$relationModel = $this->getRelationModel();
		$template = $treeViewModel->getTemplate();

		$result = $db->pquery('SELECT tr.*,rel.crmid,rel.rel_created_time,rel.rel_created_user,rel.rel_comment FROM vtiger_trees_templates_data tr '
			. 'INNER JOIN u_yf_crmentity_rel_tree rel ON rel.tree = tr.tree '
			. 'WHERE tr.templateid = ? AND rel.crmid = ? AND rel.relmodule = ?', [$template, $recordId, $relModuleId]);
		$trees = [];
		while ($row = $db->getRow($result)) {
			$treeID = $row['tree'];
			$pieces = explode('::', $row['parenttrre']);
			end($pieces);
			$parent = prev($pieces);
			$parentName = '';
			if ($row['depth'] > 0) {
				$result2 = $db->pquery('SELECT name FROM vtiger_trees_templates_data WHERE templateid = ? AND tree = ?', [$template, $parent]);
				$parentName = $db->getSingleValue($result2);
				$parentName = '(' . vtranslate($parentName, $relModuleName) . ') ';
			}
			$tree = [
				'id' => $treeID,
				'name' => $parentName . vtranslate($row['name'], $relModuleName),
				'parent' => $parent == 0 ? '#' : $parent
			];

			if ($relationModel->showCreatorDetail()) {
				$tree['relCreatedUser'] = \App\Fields\Owner::getLabel($row['rel_created_user']);
				$tree['relCreatedTime'] = Vtiger_Datetime_UIType::getDisplayDateTimeValue($row['rel_created_time']);
			}
			if ($relationModel->showComment()) {
				if (strlen($row['rel_comment']) > AppConfig::relation('COMMENT_MAX_LENGTH')) {
					$tree['relCommentFull'] = $row['rel_comment'];
				}
				$tree['relComment'] = vtlib\Functions::textLength($row['rel_comment'], AppConfig::relation('COMMENT_MAX_LENGTH'));
			}

			if (!empty($row['icon'])) {
				$tree['icon'] = $row['icon'];
			}
			$trees[] = $tree;
		}

		return $trees;
	}
}
