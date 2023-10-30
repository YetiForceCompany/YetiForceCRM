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
 * Vtiger ListView Model Class.
 */
class Vtiger_ListView_Model extends \App\Base
{
	/**
	 * Function to get the Module Model.
	 *
	 * @return Vtiger_Module_Model instance
	 */
	public function getModule()
	{
		return $this->get('module');
	}

	/**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view.
	 *
	 * @param string $moduleName - Module Name
	 * @param int    $viewId     - Custom View Id
	 *
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstance($moduleName, $viewId = 0)
	{
		$cacheName = $viewId . ':' . $moduleName;
		if (\App\Cache::staticHas('ListView_Model', $cacheName)) {
			return \App\Cache::staticGet('ListView_Model', $cacheName);
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$queryGenerator = new \App\QueryGenerator($moduleModel->getName());
		if ($viewId) {
			$instance->set('viewId', $viewId);
			$queryGenerator->initForCustomViewById($viewId);
		} else {
			if ($viewId = $queryGenerator->initForDefaultCustomView(true, true)) {
				$instance->set('viewId', $viewId);
			} else {
				$queryGenerator->loadListFields();
			}
		}
		$instance->set('module', $moduleModel)->set('query_generator', $queryGenerator);
		if (($customView = \App\CustomView::getCustomViewById($viewId)) && $customView['advanced_conditions']) {
			$instance->set('advancedConditionsRaw', $customView['advanced_conditions']);
			$instance->set('advancedConditions', \App\Condition::validAdvancedConditions($customView['advanced_conditions']));
		}
		\App\Cache::staticSave('ListView_Model', $cacheName, $instance);
		return $instance;
	}

	/**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view.
	 *
	 * @param string $value        - Module Name
	 * @param mixed  $sourceModule
	 * @param int    $cvId
	 *
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstanceForPopup($value, $sourceModule = false, int $cvId = 0)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $value);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($value);
		$queryGenerator = new \App\QueryGenerator($moduleModel->getName());
		if (!$sourceModule && !empty($sourceModule)) {
			$moduleModel->set('sourceModule', $sourceModule);
		}
		if ($cvId) {
			$instance->set('viewId', $cvId);
			$queryGenerator->initForCustomViewById($cvId);
		} else {
			$moduleModel->getModalRecordsListFields($queryGenerator, $sourceModule);
		}
		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator);
	}

	/**
	 * Function to get the Quick Links for the List view of the module.
	 *
	 * @param array $linkParams
	 *
	 * @return array List of Vtiger_Link_Model instances
	 */
	public function getHederLinks($linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['LIST_VIEW_HEADER'], $linkParams);

		$headerLinks = [];
		$moduleModel = $this->getModule();
		if (App\Config::module('ModTracker', 'WATCHDOG') && $moduleModel->isPermitted('WatchingModule')) {
			$watchdog = Vtiger_Watchdog_Model::getInstance($moduleModel->getName());
			$class = 'btn-outline-dark';
			$iconclass = 'fa-eye-slash';
			if ($watchdog->isWatchingModule()) {
				$class = 'btn-dark';
				$iconclass = 'fa-eye';
			}
			$headerLinks[] = [
				'linktype' => 'LIST_VIEW_HEADER',
				'linkhint' => 'BTN_WATCHING_MODULE',
				'linkurl' => 'javascript:Vtiger_Index_Js.changeWatching(this)',
				'linkclass' => $class,
				'linkicon' => 'fas ' . $iconclass,
				'linkdata' => ['off' => 'btn-outline-dark', 'on' => 'btn-dark', 'value' => $watchdog->isWatchingModule() ? 0 : 1],
				'active' => !$watchdog->isLock(),
			];
		}
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModuleActionPermission('Notification', 'CreateView')) {
			$headerLinks[] = [
				'linktype' => 'LIST_VIEW_HEADER',
				'linkhint' => 'LBL_SEND_NOTIFICATION',
				'linkurl' => 'javascript:Vtiger_Index_Js.sendNotification(this)',
				'linkicon' => 'fas fa-paper-plane',
			];
		}
		$openStreetMapModuleModel = Vtiger_Module_Model::getInstance('OpenStreetMap');
		if ($userPrivilegesModel->hasModulePermission($openStreetMapModuleModel->getId()) && $openStreetMapModuleModel->isAllowModules($moduleModel->getName())) {
			$headerLinks[] = [
				'linktype' => 'LIST_VIEW_HEADER',
				'linkhint' => 'LBL_SHOW_MAP',
				'linkurl' => 'javascript:Vtiger_List_Js.showMap()',
				'linkicon' => 'fas fa-globe',
			];
		}
		if ($userPrivilegesModel->hasModulePermission('PermissionInspector')) {
			$headerLinks[] = [
				'linktype' => 'LIST_VIEW_HEADER',
				'linkhint' => 'BTN_PERMISSION_INSPECTOR',
				'linkdata' => ['url' => 'index.php?module=PermissionInspector&view=UserListModal&srcModule=' . $moduleModel->getName()],
				'linkicon' => 'fas fa-user-secret',
				'modalView' => true,
			];
		}
		foreach ($headerLinks as $headerLink) {
			$links['LIST_VIEW_HEADER'][] = Vtiger_Link_Model::getInstanceFromValues($headerLink);
		}
		return $links;
	}

	/**
	 * Function to give advance links of a module.
	 *
	 * @return array of advanced links
	 */
	public function getAdvancedLinks()
	{
		$moduleModel = $this->getModule();
		$advancedLinks = [];

		if ($moduleModel->isPermitted('CreateView') && $moduleModel->isPermitted('Import')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_IMPORT',
				'linkurl' => $moduleModel->getImportUrl(),
				'linkicon' => 'fas fa-download',
			];
		}
		if ($moduleModel->isPermitted('Export')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_EXPORT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("' . $this->getModule()->getExportUrl() . '")',
				'linkicon' => 'fas fa-upload',
			];
		}
		if ($moduleModel->isPermitted('Merge')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_MERGING',
				'linkicon' => 'yfi yfi-merging-records',
				'linkdata' => ['url' => "index.php?module={$moduleModel->getName()}&view=MergeRecords"],
				'linkclass' => 'js-mass-action--merge',
			];
		}
		if ($moduleModel->isPermitted('ExportPdf')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleModel->getName());
			$pdfModel = new $handlerClass();
			$templates = $pdfModel->getActiveTemplatesForModule($moduleModel->getName(), 'List');
			if (\count($templates) > 0) {
				$advancedLinks[] = [
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => \App\Language::translate('LBL_EXPORT_PDF'),
					'linkdata' => ['url' => 'index.php?module=' . $moduleModel->getName() . '&view=PDF&fromview=List', 'type' => 'modal'],
					'linkclass' => 'js-mass-action',
					'linkicon' => 'fas fa-file-pdf',
					'title' => \App\Language::translate('LBL_EXPORT_PDF'),
				];
			}
		}
		if ($moduleModel->isPermitted('QuickExportToExcel')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_QUICK_EXPORT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerQuickExport("' . $moduleModel->getName() . '")',
				'linkicon' => 'fas fa-file-export',
			];
		}
		if ($moduleModel->isPermitted('RecordMappingList')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleModel->getName());
			$mfModel = new $handlerClass();
			$templates = $mfModel->getActiveTemplatesForModule($moduleModel->getName(), 'List');
			if (\count($templates) > 0) {
				$advancedLinks[] = [
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_GENERATE_RECORDS',
					'linkurl' => 'javascript:Vtiger_List_Js.triggerGenerateRecords();',
					'linkicon' => 'fas fa-plus-circle',
				];
			}
		}
		return $advancedLinks;
	}

	/**
	 * Function to get the list of Mass actions for the module.
	 *
	 * @param array $linkParams
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getListViewMassActions($linkParams)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['LISTVIEWMASSACTION'], $linkParams);
		$massActionLinks = [];
		if ($moduleModel->isPermitted('MassEdit')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_EDIT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerMassEdit("index.php?module=' . $moduleModel->getName() . '&view=MassActionAjax&mode=showMassEditForm");',
				'linkicon' => 'yfi yfi-full-editing-view',
			];
		}
		if ($moduleModel->isPermitted('MassActive')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ACTIVATE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Active&sourceView=List',
				'linkdata' => ['confirm' => \App\Language::translate('LBL_ACTIVATE_RECORD_DESC')],
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-undo-alt',
			];
		}
		if ($moduleModel->isPermitted('MassArchived')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ARCHIVE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Archived&sourceView=List',
				'linkdata' => ['confirm' => \App\Language::translate('LBL_ARCHIVE_RECORD_DESC')],
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-archive',
			];
		}
		if ($moduleModel->isPermitted('MassTrash')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_MOVE_TO_TRASH',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassState&state=Trash&sourceView=List',
				'linkdata' => ['confirm' => \App\Language::translate('LBL_MOVE_TO_TRASH_DESC')],
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-trash-alt',
			];
		}
		if ($moduleModel->isPermitted('MassDelete')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_DELETE',
				'linkurl' => 'javascript:',
				'dataUrl' => 'index.php?module=' . $moduleModel->getName() . '&action=MassDelete&sourceView=List',
				'linkdata' => ['confirm' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_DESC')],
				'linkclass' => 'js-mass-record-event',
				'linkicon' => 'fas fa-eraser',
			];
		}
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		if ($moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('CreateView') && $moduleModel->isPermitted('MassAddComment')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ADD_COMMENT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerMassComment("index.php?module=' . $moduleModel->getName() . '&view=MassActionAjax&mode=showAddCommentForm")',
				'linkicon' => 'fas fa-comments',
			];
		}
		if ($moduleModel->isPermitted('MassTransferOwnership')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerTransferOwnership("index.php?module=' . $moduleModel->getName() . '&view=TransferOwnership")',
				'linkicon' => 'yfi yfi-change-of-owner',
			];
		}
		if ($moduleModel->isTrackingEnabled() && App\Config::module('ModTracker', 'UNREVIEWED_COUNT') && $moduleModel->isPermitted('ReviewingUpdates') && $currentUser->getId() === $currentUser->getRealId()) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_REVIEW_CHANGES',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerReviewChanges("index.php?module=ModTracker&sourceModule=' . $moduleModel->getName() . '&action=ChangesReviewedOn&mode=reviewChanges")',
				'linkicon' => 'fas fa-check-circle',
			];
		}
		if ($moduleModel->isPermitted('RecordConventer') && \App\RecordConverter::isActive($moduleModel->getName(), 'List')) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_RECORD_CONVERTER',
				'linkdata' => ['url' => "index.php?module={$moduleModel->getName()}&view=RecordConverter&sourceView=List", 'type' => 'modal'],
				'linkicon' => 'fas fa-exchange-alt',
				'linkclass' => 'u-cursor-pointer js-mass-action',
			];
		}
		if ($moduleModel->isPermitted('MassSendSMS') && ($smsNotifierModel = \Vtiger_Module_Model::getInstance('SMSNotifier'))->isSMSActiveForModule($moduleModel->getName())) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_SEND_SMS',
				'linkdata' => ['url' => $smsNotifierModel->getMassSMSUrlForModule($moduleModel->getName()), 'type' => 'modal'],
				'linkicon' => 'fas fa-comment-sms',
				'linkclass' => 'js-mass-action',
			];
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		return $links;
	}

	/**
	 * Function to get Basic links.
	 *
	 * @return array of Basic links
	 */
	public function getBasicLinks()
	{
		$basicLinks = [];
		$moduleModel = $this->getModule();

		if ($moduleModel->isPermitted('CreateView')) {
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_RECORD',
				'linkurl' => $moduleModel->getCreateRecordUrl(),
				'linkclass' => 'js-add-record btn-light addButton modCT_' . $moduleModel->getName(),
				'linkicon' => 'fas fa-plus',
				'showLabel' => 1,
				'linkhref' => true,
			];
		}

		if ($moduleModel->isPermitted('ExportPdf')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleModel->getName());
			$pdfModel = new $handlerClass();
			$templates = $pdfModel->getActiveTemplatesForModule($moduleModel->getName(), 'List');
			if (\count($templates) > 0) {
				$basicLinks[] = [
					'linktype' => 'LISTVIEWBASIC',
					'linkdata' => ['url' => 'index.php?module=' . $moduleModel->getName() . '&view=PDF&fromview=List', 'type' => 'modal'],
					'linkclass' => 'btn-light js-mass-record-event',
					'linkicon' => 'fas fa-file-pdf',
					'linkhint' => \App\Language::translate('LBL_EXPORT_PDF'),
				];
			}
		}
		return $basicLinks;
	}

	/**
	 * Function to get the list of listview links for the module.
	 *
	 * @param <Array> $linkParams
	 *
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams)
	{
		$moduleModel = $this->getModule();
		$links = ['LISTVIEWBASIC' => []];

		$basicLinks = $this->getBasicLinks();
		foreach ($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}

		$allLinks = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['LISTVIEWBASIC', 'LISTVIEW'], $linkParams);
		if (!empty($allLinks)) {
			foreach ($allLinks as $type => $allLinksByType) {
				foreach ($allLinksByType as $linkModel) {
					$links[$type][] = $linkModel;
				}
			}
		}

		$advancedLinks = $this->getAdvancedLinks();
		foreach ($advancedLinks as $advancedLink) {
			$links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($advancedLink);
		}
		return $links;
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
	 * Function to get the list view header.
	 *
	 * @return Vtiger_Field_Model[] - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders()
	{
		$headerFieldModels = [];
		if ($this->isEmpty('viewId')) {
			$queryGenerator = $this->getQueryGenerator();
			$queryGenerator->setFields(array_values($this->getModule()->getPopupFields()));
			$queryGenerator->setField('id');
			$headerFields = $queryGenerator->getListViewFields();
		} else {
			$headerFields = [];
			if (!$this->isEmpty('header_fields')) {
				$fields = $this->get('header_fields');
			} else {
				$customView = App\CustomView::getInstance($this->getModule()->getName());
				$fields = $customView->getColumnsListByCvid($this->get('viewId'));
			}
			foreach ($fields as $fieldInfo) {
				$fieldName = $fieldInfo['field_name'];
				$fieldModel = clone Vtiger_Module_Model::getInstance($fieldInfo['module_name'])->getFieldByName($fieldName);
				if (!empty($fieldInfo['label'])) {
					$fieldModel->set('label', $fieldInfo['label']);
					$fieldModel->set('isLabelCustomized', true);
				}
				if (!empty($fieldInfo['source_field_name'])) {
					if (!$this->getModule()->getFieldByName($fieldInfo['source_field_name'])->isActiveField()) {
						continue;
					}
					$fieldModel->set('source_field_name', $fieldInfo['source_field_name']);
					$fieldModel->set('isCalculateField', false);
				} else {
					$queryGenerator = $this->getQueryGenerator();
					if ($field = $queryGenerator->getQueryField($fieldName)->getListViewFields()) {
						$queryGenerator->setField($field->getName());
						$headerFields[] = $field;
					}
				}
				$headerFields[] = $fieldModel;
			}
			if (($advancedConditions = $this->get('advancedConditions')) && !empty($advancedConditions['relationColumns'])) {
				foreach ($advancedConditions['relationColumns'] as $relationId) {
					if (($row = \App\Relation::getById($relationId)) && \App\Privilege::isPermitted($row['related_modulename'])) {
						$headerFields[] = Vtiger_Field_Model::init($row['related_modulename'], [
							'uitype' => 10,
							'label' => $row['label'],
							'referenceList' => [$row['related_modulename']],
							'searchByAjax' => true,
							'relationId' => $relationId,
							'permissions' => true,
						], 'relationColumn_' . $relationId);
					}
				}
			}
		}
		foreach ($headerFields as $fieldModel) {
			if ($fieldModel && (!$fieldModel->isViewable() || !$fieldModel->getPermissions())) {
				continue;
			}
			$name = $fieldModel->get('source_field_name') ? "{$fieldModel->getName()}:{$fieldModel->getModuleName()}:{$fieldModel->get('source_field_name')}" : $fieldModel->getName();
			$headerFieldModels[$name] = $fieldModel;
		}
		return $headerFieldModels;
	}

	/**
	 * Set list view order by.
	 */
	public function loadListViewOrderBy()
	{
		$orderBy = $this->get('orderby');
		if (!empty($orderBy) && \is_array($orderBy)) {
			$fields = $this->getModule()->getFields();
			foreach ($orderBy as $fieldName => $sortFlag) {
				[$fieldName, $moduleName, $sourceFieldName] = array_pad(explode(':', $fieldName), 3, false);
				if ($sourceFieldName && isset($fields[$sourceFieldName])) {
					$this->getQueryGenerator()->setRelatedOrder([
						'sourceField' => $sourceFieldName,
						'relatedModule' => $moduleName,
						'relatedField' => $fieldName,
						'relatedSortOrder' => $sortFlag,
					]);
				} elseif (isset($fields[$fieldName])) {
					$this->getQueryGenerator()->setOrder($fieldName, $sortFlag);
				}
			}
		}
	}

	/**
	 * Load list view conditions.
	 */
	public function loadListViewCondition()
	{
		$queryGenerator = $this->getQueryGenerator();
		if ($entityState = $this->get('entityState')) {
			$queryGenerator->setStateCondition($entityState);
		}
		$srcRecord = $this->get('src_record');
		if ($this->getModule()->get('name') === $this->get('src_module') && !empty($srcRecord)) {
			$queryGenerator->addCondition('id', $srcRecord, 'n');
		}
		if ($advancedConditions = $this->get('advancedConditions')) {
			$queryGenerator->setAdvancedConditions($advancedConditions);
		}
		if ($searchParams = $this->get('search_params')) {
			$queryGenerator->parseAdvFilter($searchParams);
		}
		if ($operator = $this->get('operator')) {
			$searchKey = $this->get('search_key');
			$searchValue = $this->get('search_value');
			if ('s' === $operator && 1 === \strlen($searchValue)) {
				$searchValue = [$searchValue, strtolower($searchValue)];
			}
			$queryGenerator->addCondition($searchKey, $searchValue, $operator);
		}
		$searchResult = $this->get('searchResult');
		if ($searchResult && \is_array($searchResult)) {
			$queryGenerator->addNativeCondition(['vtiger_crmentity.crmid' => $searchResult]);
		}
		$sourceModule = $this->get('src_module');
		if ($sourceModule) {
			$moduleModel = $this->getModule();

			if (method_exists($moduleModel, 'getQueryByModuleField')) {
				$moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $queryGenerator);
			}
			if (method_exists($moduleModel, 'getQueryByRelatedField')) {
				$moduleModel->getQueryByRelatedField($this, $queryGenerator);
			}
		}
	}

	/**
	 * Function to get the list view entries.
	 *
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return Vtiger_Record_Model[] - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		$this->loadListViewCondition();
		$this->loadListViewOrderBy();
		$pageLimit = $pagingModel->getPageLimit();
		$query = $this->getQueryGenerator()->createQuery();
		$query->limit($pageLimit + 1)->offset($pagingModel->getStartIndex());
		$rows = $query->all();
		$count = \count($rows);
		$pagingModel->calculatePageRange($count);
		if ($count > $pageLimit) {
			array_pop($rows);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$listViewRecordModels = $this->getRecordsFromArray($rows);
		unset($rows);
		return $listViewRecordModels;
	}

	/**
	 * Function to get the list view all entries.
	 *
	 * @return Vtiger_Record_Model[] - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getAllEntries()
	{
		$this->loadListViewCondition();
		$this->loadListViewOrderBy();
		return $this->getRecordsFromArray($this->getQueryGenerator()->createQuery()->all());
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
		$moduleModel = $this->getModule();
		foreach ($this->getQueryGenerator()->getRelatedFields() as $fieldInfo) {
			$relatedFields[$fieldInfo['relatedModule']][$fieldInfo['sourceField']][] = $fieldInfo['relatedField'];
		}
		foreach ($rows as $row) {
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
			$listViewRecordModels[$row['id']] = $recordModel;
		}
		return $listViewRecordModels;
	}

	/**
	 * Function to get the list view entries count.
	 *
	 * @return int|string|null number of records. The result may be a string depending on the
	 *                         underlying database engine and to support integer values higher than a 32bit PHP integer can handle.
	 */
	public function getListViewCount()
	{
		$this->loadListViewCondition();
		return $this->getQueryGenerator()->setDistinct('id')->createQuery()->count();
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
		$moduleModel = $this->getModule();
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
	}
}
