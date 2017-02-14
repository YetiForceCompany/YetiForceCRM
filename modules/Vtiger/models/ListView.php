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
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param string $moduleName - Module Name
	 * @param int $viewId - Custom View Id
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
		$queryGenerator = new \App\QueryGenerator($moduleModel->get('name'));
		if ($viewId) {
			$queryGenerator->initForCustomViewById($viewId);
		} else {
			if (!$queryGenerator->initForDefaultCustomView()) {
				$queryGenerator->loadListFields();
			}
		}
		$instance->set('module', $moduleModel)->set('query_generator', $queryGenerator);
		\App\Cache::staticGet('ListView_Model', $cacheName, $instance);
		return $instance;
	}

	/**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param string $value - Module Name
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstanceForPopup($value, $sourceModule = false)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $value);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($value);
		$queryGenerator = new \App\QueryGenerator($moduleModel->get('name'));
		if (!$sourceModule && !empty($sourceModule)) {
			$moduleModel->set('sourceModule', $sourceModule);
		}
		$listFields = $moduleModel->getPopupViewFieldsList($sourceModule);
		$listFields[] = 'id';
		$queryGenerator->setFields($listFields);
		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator);
	}

	/**
	 * Function to get the Quick Links for the List view of the module
	 * @param array $linkParams
	 * @return array List of Vtiger_Link_Model instances
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
				'active' => !$watchdog->isLock()
			];
		}
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModuleActionPermission('Notification', 'CreateView')) {
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
		$currentUser = Users_Record_Model::getCurrentUserModel();
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
		if ($moduleModel->isTrackingEnabled() && AppConfig::module('ModTracker', 'UNREVIEWED_COUNT') && $moduleModel->isPermitted('ReviewingUpdates') && $currentUser->getId() === $currentUser->getRealId()) {
			$massActionLinks[] = [
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_REVIEW_CHANGES',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerReviewChanges("index.php?module=ModTracker&sourceModule=' . $moduleModel->getName() . '&action=ChangesReviewedOn&mode=reviewChanges")',
				'linkicon' => ''
			];
		}
		foreach ($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		return $links;
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
	 * Function to get the list view header
	 * @return array - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders()
	{
		$headerFieldModels = [];
		$headerFields = $this->getQueryGenerator()->getListViewFields();
		foreach ($headerFields as $fieldName => &$fieldsModel) {
			if ($fieldsModel && (!$fieldsModel->isViewable() || !$fieldsModel->getPermissions())) {
				continue;
			}
			$headerFieldModels[$fieldName] = $fieldsModel;
		}
		return $headerFieldModels;
	}

	/**
	 * Set list view order by
	 */
	public function loadListViewOrderBy()
	{
		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy)) {
			$orderByFieldName = $this->getModule()->getFieldByColumn($orderBy);
			$this->getQueryGenerator()->setOrder($orderByFieldName->getName(), $this->getForSql('sortorder'));
		}
	}

	/**
	 * Load list view conditions
	 */
	public function loadListViewCondition()
	{
		$queryGenerator = $this->getQueryGenerator();
		$srcRecord = $this->get('src_record');
		if ($this->getModule()->get('name') === $this->get('src_module') && !empty($srcRecord)) {
			$queryGenerator->addCondition('id', $srcRecord, 'n');
		}
		$searchParams = $this->get('search_params');
		if ($searchParams) {
			$queryGenerator->parseAdvFilter($searchParams);
		}
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if ($searchKey) {
			if ($operator === 's' && strlen($searchValue) === 1) {
				$searchValue = [$searchValue, strtolower($searchValue)];
			}
			$queryGenerator->addBaseSearchConditions($searchKey, $searchValue, $operator);
		}
		$searchResult = $this->get('searchResult');
		if ($searchResult && is_array($searchResult)) {
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
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return Vtiger_Record_Model[] - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		$moduleModel = $this->getModule();
		$this->loadListViewCondition();
		$this->loadListViewOrderBy();
		$pageLimit = $pagingModel->getPageLimit();
		$query = $this->getQueryGenerator()->createQuery();
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
		$listViewRecordModels = [];
		foreach ($rows as &$row) {
			$recordModel = $moduleModel->getRecordFromArray($row);
			$recordModel->colorList = Settings_DataAccess_Module_Model::executeColorListHandlers($moduleModel->get('name'), $row['id'], $recordModel);
			$listViewRecordModels[$row['id']] = $recordModel;
		}
		unset($rows);

		return $listViewRecordModels;
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return array - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewCount()
	{
		$this->loadListViewCondition();
		return $this->getQueryGenerator()->createQuery()->count();
	}

	/**
	 * Function to give advance links of a module
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

	/**
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
		$listFields = $moduleModel->getPopupViewFieldsList();
		$this->getQueryGenerator()->setFields($listFields);
	}
}
