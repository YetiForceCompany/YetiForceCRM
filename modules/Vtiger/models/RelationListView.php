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
		return $this->getRelationModel()->getQueryGenerator();
	}

	/**
	 * Function to identify if the module supports quick search or not.
	 */
	public function isQuickSearchEnabled(): bool
	{
		return $this->has('quickSearchEnabled') ? $this->get('quickSearchEnabled') : true;
	}

	/**
	 * Get relation list view model instance.
	 *
	 * @param Vtiger_Record_Model $parentRecordModel
	 * @param string              $relationModuleName
	 * @param bool|int            $relationId
	 * @param int|string          $cvId
	 *
	 * @return self
	 */
	public static function getInstance(Vtiger_Record_Model $parentRecordModel, string $relationModuleName, $relationId = false, $cvId = 0)
	{
		$parentModuleModel = $parentRecordModel->getModule();
		$className = Vtiger_Loader::getComponentClassName('Model', 'RelationListView', $parentModuleModel->getName());
		$instance = new $className();
		if ($relationId) {
			$relationModelInstance = Vtiger_Relation_Model::getInstanceById($relationId);
		} else {
			$relationModelInstance = Vtiger_Relation_Model::getInstance($parentModuleModel, Vtiger_Module_Model::getInstance($relationModuleName), $relationId);
		}
		if (!$relationModelInstance) {
			return false;
		}
		$instance->setParentRecordModel($parentRecordModel);
		$instance->setRelatedModuleModel($relationModelInstance->getRelationModuleModel());
		$queryGenerator = new \App\QueryGenerator($relationModelInstance->getRelationModuleModel()->getName());
		if (is_numeric($cvId)) {
			$instance->set('viewId', $cvId);
		}
		$relationModelInstance->set('query_generator', $queryGenerator);
		$relationModelInstance->set('parentRecord', $parentRecordModel);
		$instance->setRelationModel($relationModelInstance);
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
		$this->loadCustomView();
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
			$queryGenerator->addCondition($this->get('search_key'), $this->get('search_value'), $this->get('operator'));
		}
		if ($searchParams = $this->getArray('search_rel_params')) {
			$this->getRelationModel()->setRelationConditions($searchParams);
		}
	}

	/**
	 * Load custom view.
	 */
	public function loadCustomView()
	{
		if ($this->has('viewId')) {
			$cvId = $this->get('viewId');
		} else {
			$cvId = array_key_first($this->getRelationModel()->getCustomViewList());
		}
		if ('relation' !== $cvId) {
			$this->getRelationModel()->getQueryGenerator()->initForCustomViewById($cvId);
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
		$pageLimit = $pagingModel->getPageLimit();
		$query = $this->getRelationQuery();
		$query->limit($pageLimit + 1)->offset($pagingModel->getStartIndex());
		$rows = $query->all();

		$count = \count($rows);
		if ($count > $pageLimit) {
			array_pop($rows);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$relatedRecordList = $this->getRecordsFromArray($rows);
		$pagingModel->calculatePageRange(\count($relatedRecordList));
		return $relatedRecordList;
	}

	/**
	 * Gets all entries.
	 *
	 * @return \Vtiger_Record_Model[]
	 */
	public function getAllEntries(): array
	{
		return $this->getRecordsFromArray($this->getRelationQuery()->all());
	}

	/**
	 * Get models of records from array.
	 *
	 * @param array $rows
	 *
	 * @return \Vtiger_Record_Model[]
	 */
	public function getRecordsFromArray(array $rows)
	{
		$listViewRecordModels = $relatedFields = [];
		$moduleModel = $this->getRelationModel()->getRelationModuleModel();
		$recordId = $this->getParentRecordModel()->getId();
		foreach ($this->getQueryGenerator()->getRelatedFields() as $fieldInfo) {
			$relatedFields[$fieldInfo['relatedModule']][$fieldInfo['sourceField']][] = $fieldInfo['relatedField'];
		}
		foreach ($rows as $row) {
			if ($recordId == $row['id']) {
				continue;
			}
			$extRecordModel = [];
			foreach ($relatedFields as $relatedModuleName => $fields) {
				foreach ($fields as $sourceField => $field) {
					$recordData = [
						'id' => $row[$sourceField . $relatedModuleName . 'id'] ?? 0,
					];
					foreach ($field as $relatedFieldName) {
						$recordData[$relatedFieldName] = $row[$sourceField . $relatedModuleName . $relatedFieldName];
						unset($row[$sourceField . $relatedModuleName . $relatedFieldName]);
					}
					$extRecordModel[$sourceField][$relatedModuleName] = Vtiger_Module_Model::getInstance($relatedModuleName)->getRecordFromArray($recordData);
				}
			}
			$recordModel = $moduleModel->getRecordFromArray($row);
			$recordModel->ext = $extRecordModel;
			$this->getEntryExtend($recordModel);
			$listViewRecordModels[$row['id']] = $recordModel;
		}
		return $listViewRecordModels;
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
		$orderBy = $this->get('orderby');
		if (!empty($orderBy) && \is_array($orderBy)) {
			foreach ($orderBy as $fieldName => $sortFlag) {
				$field = $this->getRelationModel()->getRelationModuleModel()->getFieldByName($fieldName);
				if ($field || 'id' === $fieldName) {
					$this->getRelationModel()->getQueryGenerator()->setOrder($fieldName, $sortFlag);
				} else {
					\App\Log::warning("[RelationListView] Incorrect value of sorting: '$fieldName'");
				}
			}
		}
	}

	/**
	 * Get header fields.
	 *
	 * @return Vtiger_Field_Model[]
	 */
	public function getHeaders()
	{
		$fields = [];
		if ($this->get('viewId')) {
			$moduleModel = $this->getRelationModel()->getRelationModuleModel();
			$customView = App\CustomView::getInstance($moduleModel->getName());
			foreach ($customView->getColumnsListByCvid($this->get('viewId')) as $fieldInfo) {
				$fieldName = $fieldInfo['field_name'];
				$sourceFieldName = $fieldInfo['source_field_name'] ?? '';
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, Vtiger_Module_Model::getInstance($fieldInfo['module_name']));
				if (!$fieldModel) {
					\App\Log::warning("The field does not exist: '$fieldName' | Module: " . $this->getRelationModel()->getRelationModuleModel()->getName(), __METHOD__);
					continue;
				}
				if (!$fieldModel->isActiveField() || ($sourceFieldName && !$moduleModel->getFieldByName($sourceFieldName)->isActiveField())) {
					continue;
				}
				if ($sourceFieldName) {
					$fieldModel->set('source_field_name', $sourceFieldName);
				}
				$fields[$fieldModel->getFullName()] = $fieldModel;
			}
		}
		if (empty($fields)) {
			$fields = $this->getRelationModel()->getQueryFields();
		}
		unset($fields['id']);
		foreach ($fields as $fieldName => $fieldModel) {
			if (!$fieldModel) {
				\App\Log::warning("The field does not exist: '$fieldName' | Module: " . $this->getRelationModel()->getRelationModuleModel()->getName(), __METHOD__);
				unset($fields[$fieldName]);
			} elseif (!$fieldModel->isViewable() && !$fieldModel->get('fromOutsideList')) {
				unset($fields[$fieldName]);
			}
		}
		if ($relFields = $this->getRelationModel()->getRelationFields()) {
			foreach ($relFields as $fieldName => $fieldModel) {
				if (!$fieldModel) {
					\App\Log::warning("The field does not exist: '$fieldName' | Module: " . $this->getRelationModel()->getRelationModuleModel()->getName(), __METHOD__);
					unset($relFields[$fieldName]);
				}
			}
			$fields = array_merge($fields, $relFields);
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

	/**
	 * Get create url from parent record.
	 *
	 * @param bool $fullView
	 *
	 * @return string
	 */
	public function getCreateViewUrl(bool $fullView = false)
	{
		$createViewUrl = $this->getRelationModel()->getCreateViewUrl($fullView);
		if (!empty(Config\Relation::$addSearchParamsToCreateView) && ($searchParams = $this->getArray('search_params')) && isset($searchParams['and']) && \is_array($searchParams['and'])) {
			foreach ($searchParams['and'] as $row) {
				if ('e' === $row['comparator']) {
					$createViewUrl .= "&{$row['field_name']}={$row['value']}";
				}
			}
		}
		return $createViewUrl;
	}

	/**
	 * Function to get the links for related list.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getLinks(): array
	{
		$parentRecordModel = $this->getParentRecordModel();
		$relationModelInstance = $this->getRelationModel();
		$relatedModuleModel = $relationModelInstance->getRelationModuleModel();
		$relatedLink = [
			'RELATEDLIST_VIEWS' => [
				Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'RELATEDLIST_VIEWS',
					'linklabel' => 'LBL_RECORDS_LIST',
					'view' => 'List',
					'linkicon' => 'far fa-list-alt',
				]),
				Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'RELATEDLIST_VIEWS',
					'linklabel' => 'LBL_RECORDS_PREVIEW_LIST',
					'view' => 'ListPreview',
					'linkicon' => 'fas fa-desktop',
				]),
			],
		];
		if (!$parentRecordModel->isReadOnly()) {
			$selectLinks = $this->getSelectRelationLinks();
			foreach ($selectLinks as $selectLinkModel) {
				$selectLinkModel->set('_selectRelation', true)->set('_module', $relatedModuleModel);
			}
			$relatedLink['LISTVIEWBASIC'] = $selectLinks;
			$relatedLink = array_merge_recursive($relatedLink, $this->getAddRelationLinks());
			if ('Documents' === $relatedModuleModel->getName()) {
				$relatedLink['RELATEDLIST_MASSACTIONS'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'RELATEDLIST_MASSACTIONS',
					'linklabel' => 'LBL_MASS_DOWNLOAD',
					'linkurl' => "javascript:Vtiger_RelatedList_Js.triggerMassDownload('index.php?module={$parentRecordModel->getModuleName()}&action=RelationAjax&mode=massDownload&src_record={$parentRecordModel->getId()}&relatedModule=Documents&mode=multiple','sendByForm')",
					'linkclass' => '',
					'linkicon' => 'fas fa-download',
				]);
			}
			if ($relatedModuleModel->isPermitted('MassSendSMS') && ($smsNotifierModel = \Vtiger_Module_Model::getInstance('SMSNotifier'))->isSMSActiveForModule($relatedModuleModel->getName())) {
				$relatedLink['RELATEDLIST_MASSACTIONS'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'RELATEDLIST_MASSACTIONS',
					'linklabel' => 'LBL_MASS_SEND_SMS',
					'linkdata' => ['url' => $smsNotifierModel->getMassSMSUrlForModule($relatedModuleModel->getName()), 'type' => 'modal'],
					'linkicon' => 'fas fa-comment-sms',
					'linkclass' => 'js-mass-record-event',
				]);
			}
			if ($relatedModuleModel->isPermitted('QuickExportToExcel')) {
				$relatedLink['RELATEDLIST_MASSACTIONS'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'RELATEDLIST_MASSACTIONS',
					'linklabel' => 'LBL_QUICK_EXPORT',
					'linkurl' => "javascript:Vtiger_RelatedList_Js.triggerMassAction('index.php?module={$parentRecordModel->getModuleName()}&action=RelationAjax&mode=exportToExcel&src_record={$parentRecordModel->getId()}&relatedModule={$relatedModuleModel->getName()}&relationId={$this->getRelationModel()->getId()}&isSortActive=true','sendByForm')",
					'linkclass' => '',
					'linkicon' => 'fas fa-file-export',
				]);
			}
			if ($relatedModuleModel->isPermitted('ExportPdf')) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $relatedModuleModel->getName());
				$pdfModel = new $handlerClass();
				if ($pdfModel->getActiveTemplatesForModule($relatedModuleModel->getName(), 'RelatedList')) {
					$relatedLink['RELATEDLIST_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
						'linktype' => 'RELATEDLIST_BASIC',
						'linkdata' => [
							'type' => 'modal',
							'url' => "index.php?module={$parentRecordModel->getModuleName()}&view=PDF&fromview=RelatedList",
						],
						'linkclass' => 'btn-light js-mass-record-event',
						'linkicon' => 'fas fa-file-pdf',
						'linkhint' => \App\Language::translate('LBL_EXPORT_PDF'),
					]);
				}
			}
		}
		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($parentRecordModel);
		$eventHandler->setModuleName($relatedModuleModel->getName());
		$eventHandler->setParams([
			'relatedLink' => $relatedLink,
			'viewInstance' => $this,
		]);
		$eventHandler->trigger('RelationListLinks');
		return $eventHandler->getParam('relatedLink');
	}

	/**
	 * Function to get the select links for related list.
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getSelectRelationLinks(): array
	{
		if (!$this->getRelationModel()->isSelectActionSupported() || $this->getParentRecordModel()->isReadOnly()) {
			return [];
		}
		$relatedModel = $this->getRelationModel()->getRelationModuleModel();
		if (!$relatedModel->isPermitted('DetailView')) {
			return [];
		}
		return [
			Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => \App\Language::translate('LBL_SELECT_RELATION', $relatedModel->getName()),
				'linkurl' => '',
				'linkicon' => 'fas fa-level-up-alt',
			]),
		];
	}

	/**
	 * Function to get the add links for related list.
	 *
	 * @return Vtiger_Link_Model[][]
	 */
	public function getAddRelationLinks(): array
	{
		$relationModelInstance = $this->getRelationModel();
		if (!$relationModelInstance->isAddActionSupported() || $this->getParentRecordModel()->isReadOnly()) {
			return [];
		}
		$relatedModel = $relationModelInstance->getRelationModuleModel();
		$addLinkModel = [
			'LISTVIEWBASIC' => [
				Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => App\Language::translate('LBL_ADD_RELATION', $relatedModel->getName()),
					'linkurl' => $this->getCreateViewUrl(),
					'linkqcs' => $relatedModel->isQuickCreateSupported(),
					'linkicon' => 'fas fa-plus',
				]),
			]
		];
		if ('Documents' === $relatedModel->getName()) {
			$addLinkModel['RELATEDLIST_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => App\Language::translate('LBL_MASS_ADD', 'Documents'),
				'linkdata' => [
					'url' => 'index.php?module=Documents&view=MassAddDocuments&sourceView=Detail',
					'cb' => 'Documents_MassAddDocuments_Js.register',
					'view' => 'Detail',
				],
				'linkclass' => 'btn-light js-show-modal',
				'linkicon' => 'yfi-document-templates',
				'showLabel' => 1,
			]);
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
		if (\is_string($fields)) {
			$fields = explode(',', $fields);
		}
		$relatedListFields = [];
		$relFields = $this->getRelationModel()->getRelationFields();
		foreach ($fields as $fieldName) {
			$fieldModel = $this->relatedModuleModel->getFieldByName($fieldName);
			if ($fieldModel) {
				$relatedListFields[$fieldName] = $fieldModel;
			} elseif (isset($relFields[$fieldName])) {
				$relatedListFields[$fieldName] = $relFields[$fieldName];
			}
		}
		$this->relationModel->set('QueryFields', $relatedListFields);
	}

	/**
	 * Get widgets instances.
	 *
	 * @param int $recordId
	 *
	 * @return array
	 */
	public function getWidgets(int $recordId): array
	{
		$widgets = [];
		$moduleModel = $this->getRelatedModuleModel();
		foreach ($this->getWidgetsList() as $widgetCol) {
			foreach ($widgetCol as $widget) {
				$widgetName = Vtiger_Loader::getComponentClassName('Widget', $widget['type'], $moduleModel->getName());
				if (class_exists($widgetName)) {
					$widgetInstance = new $widgetName($moduleModel->getName(), $moduleModel, $recordId, $widget);
					$widgetObject = $widgetInstance->getWidget();
					if (\count($widgetObject) > 0) {
						$widgets[$widgetObject['wcol']][] = $widgetObject;
					}
				}
			}
		}
		return $widgets;
	}

	/**
	 * Get widgets list.
	 *
	 * @return array
	 */
	public function getWidgetsList(): array
	{
		$relationId = $this->getRelationModel()->getId();
		if (\App\Cache::has('RelatedModuleWidgets', $relationId)) {
			return \App\Cache::get('RelatedModuleWidgets', $relationId);
		}
		$query = (new App\Db\Query())->from('a_#__relatedlists_widgets')->where(['relation_id' => $relationId]);
		$dataReader = $query->orderBy(['sequence' => SORT_ASC])->createCommand()->query();
		$widgets = [1 => [], 2 => [], 3 => []];
		while ($row = $dataReader->read()) {
			$row['data'] = \App\Json::decode($row['data']);
			$widgets[$row['wcol']][$row['id']] = $row;
		}
		$dataReader->close();
		App\Cache::save('RelatedModuleWidgets', $relationId, $widgets);
		return $widgets;
	}

	/**
	 * Check if widgets exist.
	 *
	 * @return bool
	 */
	public function isWidgetsList(): bool
	{
		$widgets = $this->getWidgetsList();
		return !empty($widgets[1]) || !empty($widgets[2]) || !empty($widgets[3]);
	}

	/**
	 * Locked fields according to parameters passed.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function loadSearchLockedFields(App\Request $request): void
	{
		$moduleModel = $this->getRelationModel()->getRelationModuleModel();
		if (!$request->isEmpty('lockedFields')) {
			foreach ($request->getArray('lockedFields') as $value) {
				$moduleModel->getFieldByName($value)->set('searchLockedFields', true);
			}
		}
		if (!$request->isEmpty('lockedEmptyFields')) {
			foreach ($request->getArray('lockedEmptyFields') as $value) {
				if (strpos($value, ':')) {
					[$fieldName, $moduleName] = explode(':', $value);
					$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
					$value = $fieldName;
				}
				$moduleModel->getFieldByName($value)->set('searchLockedEmptyFields', true);
			}
		}
		if (!$request->isEmpty('search_params')) {
			foreach ($request->getArray('search_params') as $values) {
				foreach ($values as $value) {
					if ('y' === $value[1]) {
						$moduleModel->getFieldByName($value[0])->set('searchLockedEmptyFields', true);
					}
				}
			}
		}
	}
}
