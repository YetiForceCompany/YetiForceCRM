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

/**
 * Vtiger ListView Model Class
 */
class Vtiger_ListView_Model extends Vtiger_Base_Model
{

	/**
	 * Function to get the Module Model
	 * @return Vtiger_Module_Model instance
	 */
	public function getModule()
	{
		return $this->get('module');
	}

	/**
	 * Function to get the Quick Links for the List view of the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getHederLinks($linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['LIST_VIEW_HEADER'], $linkParams);

		$headerLinks = [];
		$moduleModel = $this->getModule();
		if (AppConfig::module('ModTracker', 'WATCHDOG') && $moduleModel->isPermitted('WatchingModule')) {
			$watchdog = Vtiger_Watchdog_Model::getInstance($moduleModel->getName());
			$class = 'btn-default';
			if ($watchdog->isWatchingModule()) {
				$class = 'btn-info';
			}
			$headerLinks[] = [
				'linktype' => 'LIST_VIEW_HEADER',
				'linkhint' => 'BTN_WATCHING_MODULE',
				'linkurl' => 'javascript:Vtiger_Index_Js.changeWatching(this)',
				'linkclass' => $class,
				'linkicon' => 'glyphicon glyphicon-eye-open',
				'linkdata' => ['off' => 'btn-default', 'on' => 'btn-info', 'value' => $watchdog->isWatchingModule() ? 0 : 1],
			];
		}
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModulePermission('Dashboard') && $userPrivilegesModel->hasModuleActionPermission('Dashboard', 'NotificationCreateMessage')) {
			$headerLinks[] = [
				'linktype' => 'LIST_VIEW_HEADER',
				'linkhint' => 'LBL_SEND_NOTIFICATION',
				'linkurl' => 'javascript:Vtiger_Index_Js.sendNotification(this)',
				'linkicon' => 'glyphicon glyphicon-send'
			];
		}
		$openStreetMapModuleModel = Vtiger_Module_Model::getInstance('OpenStreetMap');
		if ($userPrivilegesModel->hasModulePermission($openStreetMapModuleModel->getId()) && $openStreetMapModuleModel->isAllowModules($moduleModel->getName())) {
			$headerLinks[] = [
				'linktype' => 'LIST_VIEW_HEADER',
				'linkhint' => 'LBL_SHOW_MAP',
				'linkurl' => 'javascript:Vtiger_List_Js.showMap()',
				'linkicon' => 'fa fa-globe'
			];
		}
		foreach ($headerLinks as $headerLink) {
			$links['LIST_VIEW_HEADER'][] = Vtiger_Link_Model::getInstanceFromValues($headerLink);
		}
		return $links;
	}

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams)
	{
		$moduleModel = $this->getModule();
		$links = [];

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
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams)
	{
		$moduleModel = $this->getModule();
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['LISTVIEWMASSACTION'], $linkParams);

		$massActionLinks = [];
		if ($moduleModel->isPermitted('MassEdit')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_EDIT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerMassEdit("index.php?module=' . $moduleModel->get('name') . '&view=MassActionAjax&mode=showMassEditForm");',
				'linkicon' => ''
			);
		}
		if ($moduleModel->isPermitted('MassDelete')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_DELETE',
				'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleModel->get('name') . '&action=MassDelete");',
				'linkicon' => ''
			);
		}

		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		if ($moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('EditView') && $moduleModel->isPermitted('MassAddComment')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_MASS_ADD_COMMENT',
				'linkurl' => 'index.php?module=' . $moduleModel->get('name') . '&view=MassActionAjax&mode=showAddCommentForm',
				'linkicon' => ''
			);
		}

		if ($moduleModel->isPermitted('MassTransferOwnership')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerTransferOwnership("index.php?module=' . $moduleModel->getName() . '&view=MassActionAjax&mode=transferOwnership")',
				'linkicon' => ''
			);
		}

		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		return $links;
	}

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders()
	{
		$listViewContoller = $this->get('listview_controller');
		$module = $this->getModule();
		$headerFieldModels = [];
		$headerFields = $listViewContoller->getListViewHeaderFields();
		foreach ($headerFields as $fieldName => $webserviceField) {
			if ($webserviceField && !in_array($webserviceField->getPresence(), [0, 2]))
				continue;
			$headerFieldModels[$fieldName] = Vtiger_Field_Model::getInstance($fieldName, $module);
		}
		return $headerFieldModels;
	}

	public function getListViewOrderBy()
	{
		$moduleModel = $this->getModule();
		$query = '';
		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');
		if (!empty($orderBy)) {
			$columnFieldMapping = $moduleModel->getColumnFieldMapping();
			$orderByFieldName = $columnFieldMapping[$orderBy];
			$orderByFieldModel = $moduleModel->getField($orderByFieldName);
			if ($orderByFieldModel && $orderByFieldModel->isReferenceField()) {
				//IF it is reference add it in the where fields so that from clause will be having join of the table
				$this->get('query_generator')->setConditionField($orderByFieldName);

				$referenceModules = $orderByFieldModel->getReferenceList();
				$referenceNameFieldOrderBy = [];
				foreach ($referenceModules as $referenceModuleName) {
					$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModuleName);
					$referenceNameFields = $referenceModuleModel->getNameFields();

					$columnList = [];
					foreach ($referenceNameFields as $nameField) {
						$fieldModel = $referenceModuleModel->getField($nameField);
						$columnList[] = $fieldModel->get('table') . $orderByFieldModel->getName() . '.' . $fieldModel->get('column');
					}
					if (count($columnList) > 1) {
						$referenceNameFieldOrderBy[] = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => $columnList[0], 'last_name' => $columnList[1]), 'Users', '') . ' ' . $sortOrder;
					} else {
						$referenceNameFieldOrderBy[] = implode('', $columnList) . ' ' . $sortOrder;
					}
				}
				$query = sprintf(' ORDER BY %s', implode(',', $referenceNameFieldOrderBy));
			} else if ($orderBy === 'smownerid') {
				$this->get('query_generator')->setConditionField($orderByFieldName);

				$fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
				if ($fieldModel->getFieldDataType() == 'owner') {
					$orderBy = 'COALESCE(' . \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . ',vtiger_groups.groupname)';
				}
				$query = sprintf(' ORDER BY %s %s', $orderBy, $sortOrder);
			} else {
				$query = sprintf(' ORDER BY %s %s', $orderBy, $sortOrder);
			}
		}
		return $query;
	}

	public function loadListViewCondition($moduleName)
	{
		$queryGenerator = $this->get('query_generator');
		$srcRecord = $this->get('src_record');
		if ($moduleName == $this->get('src_module') && !empty($srcRecord)) {
			$queryGenerator->addCondition('id', $srcRecord, 'n');
		}

		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}
		$glue = '';
		if (count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
			$glue = QueryGenerator::$AND;
		}
		$queryGenerator->parseAdvFilterList($searchParams, $glue);

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if (!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(
				[
					'search_field' => $searchKey,
					'search_text' => $searchValue,
					'operator' => $operator
				]
			);
		}
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel, $searchResult = false)
	{
		$db = PearDatabase::getInstance();
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);

		$listViewContoller = $this->get('listview_controller');

		$this->loadListViewCondition($moduleName);
		$listOrder = $this->getListViewOrderBy();

		$listQuery = $this->getQuery();
		if ($searchResult && $searchResult != '' && is_array($searchResult)) {
			$listQuery .= ' && vtiger_crmentity.crmid IN (' . implode(',', $searchResult) . ') ';
		}
		unset($searchResult);
		$sourceModule = $this->get('src_module');
		if (!empty($sourceModule)) {
			if (method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if (!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}
		if (!empty($sourceModule)) {
			if (method_exists($moduleModel, 'getQueryByRelatedField')) {
				$overrideQuery = $moduleModel->getQueryByRelatedField($this, $listQuery);
				if (!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}
		$listQuery .= $listOrder;
		$pageLimit = $pagingModel->getPageLimit();
		$startIndex = $pagingModel->getStartIndex();

		$viewid = ListViewSession::getCurrentView($moduleName);
		if (empty($viewid)) {
			$viewid = $pagingModel->get('viewid');
		}
		$_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');

		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		$listQuery .= " LIMIT $startIndex," . ($pageLimit + 1);
		$listResult = $db->query($listQuery);

		$listViewRecordModels = [];
		$listViewEntries = $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);

		$pagingModel->calculatePageRange($listViewEntries);

		if ($db->num_rows($listResult) > $pageLimit) {
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		$index = 0;
		foreach ($listViewEntries as $recordId => $record) {
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record);
			$listViewRecordModels[$recordId]->colorList = Settings_DataAccess_Module_Model::executeColorListHandlers($moduleName, $recordId, $moduleModel->getRecordFromArray($listViewContoller->rawData[$recordId]));
		}
		return $listViewRecordModels;
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewCount()
	{
		$db = PearDatabase::getInstance();

		$queryGenerator = $this->get('query_generator');
		$searchParams = $this->get('search_params');
		if (empty($searchParams)) {
			$searchParams = [];
		}

		$glue = '';
		if (count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
			$glue = QueryGenerator::$AND;
		}
		$queryGenerator->parseAdvFilterList($searchParams, $glue);

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if (!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}
		$moduleName = $this->getModule()->get('name');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$listQuery = $this->getQuery();

		$sourceModule = $this->get('src_module');
		if (!empty($sourceModule)) {
			$moduleModel = $this->getModule();
			if (method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if (!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}
		$position = stripos($listQuery, ' from ');
		if ($position) {
			$split = preg_split('/ from /i', $listQuery, 2);
			$listQuery = 'SELECT count(*) AS count ';
			for ($i = 1; $i < count($split); $i++) {
				$listQuery .= sprintf(' FROM %s', $split[$i]);
			}
		}

		if ($this->getModule()->get('name') == 'Calendar') {
			$listQuery .= ' && activitytype <> "Emails"';
		}

		$listResult = $db->query($listQuery);
		return $db->getSingleValue($listResult);
	}

	public function getQuery()
	{
		return $this->get('query_generator')->getQuery();
	}

	/**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param <String> $moduleName - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstance($moduleName, $viewId = '0')
	{
		$cacheName = $viewId . ':' . $moduleName;
		$instance = Vtiger_Cache::get('ListView_Model', $cacheName);
		if ($instance) {
			return $instance;
		}

		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);
		$customView = new CustomView();
		if (!empty($viewId) && $viewId != 0) {
			$queryGenerator->initForCustomViewById($viewId);
		} else {
			$viewId = $customView->getViewId($moduleName);
			if (!empty($viewId) && $viewId != 0) {
				$queryGenerator->initForDefaultCustomView();
			} else {
				$entityInstance = CRMEntity::getInstance($moduleName);
				$listFields = $entityInstance->list_fields_name;
				$listFields[] = 'id';
				$queryGenerator->setFields($listFields);
			}
		}
		$controller = new ListViewController($db, $currentUser, $queryGenerator);
		$instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
		Vtiger_Cache::set('ListView_Model', $cacheName, $instance);
		return $instance;
	}

	/**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param <String> $value - Module Name
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstanceForPopup($value, $sourceModule = false)
	{
		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $value);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($value);

		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);

		if (!$sourceModule && !empty($sourceModule)) {
			$moduleModel->set('sourceModule', $sourceModule);
		}

		$listFields = $moduleModel->getPopupViewFieldsList($sourceModule);
		$listFields[] = 'id';
		$queryGenerator->setFields($listFields);

		$controller = new ListViewController($db, $currentUser, $queryGenerator);

		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
	}
	/*
	 * Function to give advance links of a module
	 * 	@RETURN array of advanced links
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
				'linkicon' => ''
			];
		}
		if ($moduleModel->isPermitted('Export')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEW',
				'linklabel' => 'LBL_EXPORT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("' . $this->getModule()->getExportUrl() . '")',
				'linkicon' => ''
			];
		}
		if (!Settings_ModuleManager_Library_Model::checkLibrary('mPDF') && $moduleModel->isPermitted('ExportPdf')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleModel->getName());
			$pdfModel = new $handlerClass();
			$templates = $pdfModel->getActiveTemplatesForModule($moduleModel->getName(), 'List');
			if (count($templates) > 0) {
				$advancedLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => vtranslate('LBL_EXPORT_PDF'),
					'linkurl' => 'javascript:Vtiger_Header_Js.getInstance().showPdfModal("index.php?module=' . $moduleModel->getName() . '&view=PDF&fromview=List");',
					'linkicon' => 'glyphicon glyphicon-save-file',
					'title' => vtranslate('LBL_EXPORT_PDF')
				];
			}
		}
		if ($moduleModel->isPermitted('DuplicatesHandling')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_FIND_DUPLICATES',
				'linkurl' => 'Javascript:Vtiger_List_Js.showDuplicateSearchForm("index.php?module=' . $moduleModel->getName() .
				'&view=MassActionAjax&mode=showDuplicatesSearchForm")',
				'linkicon' => ''
			];
		}
		if ($moduleModel->isPermitted('QuickExportToExcel') && !Settings_ModuleManager_Library_Model::checkLibrary('PHPExcel')) {
			$advancedLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_QUICK_EXPORT_TO_EXCEL',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerQuickExportToExcel("' . $moduleModel->getName() . '")',
				'linkicon' => ''
			];
		}
		if ($moduleModel->isPermitted('RecordMappingList')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleModel->getName());
			$mfModel = new $handlerClass();
			$templates = $mfModel->getActiveTemplatesForModule($moduleModel->getName(), 'List');
			if (count($templates) > 0) {
				$advancedLinks[] = [
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_GENERATE_RECORDS',
					'linkurl' => 'javascript:Vtiger_List_Js.triggerGenerateRecords("index.php?module=' . $moduleModel->getName() . '&view=GenerateModal&fromview=List");',
				];
			}
		}
		return $advancedLinks;
	}
	/*
	 * Function to get Basic links
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
				'linkclass' => 'addButton moduleColor_' . $moduleModel->getName(),
				'linkicon' => 'glyphicon glyphicon-plus',
				'showLabel' => 1,
				'linkhref' => true
			];
		}

		if (!Settings_ModuleManager_Library_Model::checkLibrary('mPDF') && $moduleModel->isPermitted('ExportPdf')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleModel->getName());
			$pdfModel = new $handlerClass();
			$templates = $pdfModel->getActiveTemplatesForModule($moduleModel->getName(), 'List');
			if (count($templates) > 0) {
				$basicLinks[] = [
					'linktype' => 'LISTVIEWBASIC',
					'linkurl' => 'javascript:Vtiger_Header_Js.getInstance().showPdfModal("index.php?module=' . $moduleModel->getName() . '&view=PDF&fromview=List");',
					'linkicon' => 'glyphicon glyphicon-save-file',
					'linkhint' => vtranslate('LBL_EXPORT_PDF')
				];
			}
		}
		return $basicLinks;
	}

	public function extendPopupFields($fieldsList)
	{
		$moduleModel = $this->get('module');
		$queryGenerator = $this->get('query_generator');

		$listFields = $moduleModel->getPopupViewFieldsList();

		$listFields[] = 'id';
		$listFields = array_merge($listFields, $fieldsList);
		$queryGenerator->setFields($listFields);
	}
}
