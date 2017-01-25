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

class Vtiger_Detail_View extends Vtiger_Index_View
{

	protected $record = false;
	protected $recordStructure = false;
	public $defaultMode = false;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showDetailViewByMode');
		$this->exposeMethod('showModuleDetailView');
		$this->exposeMethod('showModuleSummaryView');
		$this->exposeMethod('showModuleBasicView');
		$this->exposeMethod('showRecentActivities');
		$this->exposeMethod('showRecentComments');
		$this->exposeMethod('showRelatedList');
		$this->exposeMethod('showChildComments');
		$this->exposeMethod('showAllComments');
		$this->exposeMethod('showThreadComments');
		$this->exposeMethod('getActivities');
		$this->exposeMethod('showRelatedProductsServices');
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showRelatedTree');
		$this->exposeMethod('showRecentRelation');
		$this->exposeMethod('showOpenStreetMap');
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		if (!is_numeric($recordId)) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function getBreadcrumbTitle(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		return vtranslate('LBL_VIEW_DETAIL', $moduleName);
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);

		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$summaryInfo = [];
		// Take first block information as summary information
		$stucturedValues = $this->recordStructure->getStructure();
		$fieldsInHeader = $this->recordStructure->getFieldInHeader();
		foreach ($stucturedValues as $blockLabel => $fieldList) {
			$summaryInfo[$blockLabel] = $fieldList;
			break;
		}

		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($recordModel);
		$eventHandler->setModuleName($moduleName);
		$eventHandler->trigger('DetailViewBefore');

		$detailViewLinkParams = array('MODULE' => $moduleName, 'RECORD' => $recordId);

		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
		$this->record->getWidgets($detailViewLinkParams);
		$navigationInfo = false; //ListViewSession::getListViewNavigation($recordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('NAVIGATION', $navigationInfo);
		$viewer->assign('NO_PAGINATION', true);
		$viewer->assign('COLORLISTHANDLERS', Settings_DataAccess_Module_Model::executeColorListHandlers($moduleName, $recordId, $recordModel));

		//Intially make the prev and next records as null
		$prevRecordId = null;
		$nextRecordId = null;
		$found = false;
		if ($navigationInfo) {
			foreach ($navigationInfo as $page => $pageInfo) {
				foreach ($pageInfo as $index => $record) {
					//If record found then next record in the interation
					//will be next record
					if ($found) {
						$nextRecordId = $record;
						break;
					}
					if ($record == $recordId) {
						$found = true;
					}
					//If record not found then we are assiging previousRecordId
					//assuming next record will get matched
					if (!$found) {
						$prevRecordId = $record;
					}
				}
				//if record is found and next record is not calculated we need to perform iteration
				if ($found && !empty($nextRecordId)) {
					break;
				}
			}
		}

		$moduleModel = $this->record->getModule();
		if (!empty($prevRecordId)) {
			$viewer->assign('PREVIOUS_RECORD_URL', $moduleModel->getDetailViewUrl($prevRecordId));
		}
		if (!empty($nextRecordId)) {
			$viewer->assign('NEXT_RECORD_URL', $moduleModel->getDetailViewUrl($nextRecordId));
		}
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$selectedTabLabel = $request->get('tab_label');
		$requestMode = $request->get('requestMode');
		$mode = $request->getMode();
		if (empty($selectedTabLabel) && !empty($requestMode)) {
			if ($requestMode == 'full') {
				$selectedTabLabel = 'LBL_RECORD_DETAILS';
			} else {
				$selectedTabLabel = 'LBL_RECORD_SUMMARY';
			}
		} elseif (empty($requestMode) && empty($mode)) {
			$selectedTabLabel = AppConfig::module($moduleName, 'DEFAULT_VIEW_RECORD');
			if (empty($selectedTabLabel)) {
				if ($currentUserModel->get('default_record_view') === 'Detail') {
					$selectedTabLabel = 'LBL_RECORD_DETAILS';
				} else {
					if ($moduleModel->isSummaryViewSupported() && $this->record->widgetsList) {
						$selectedTabLabel = 'LBL_RECORD_SUMMARY';
					} else {
						$selectedTabLabel = 'LBL_RECORD_DETAILS';
					}
				}
			}
		}
		if (is_array($detailViewLinks['DETAILVIEWTAB'])) {
			foreach ($detailViewLinks['DETAILVIEWTAB'] as $link) {
				if ($link->getLabel() == $selectedTabLabel) {
					$params = vtlib\Functions::getQueryParams($link->getUrl());
					$this->defaultMode = $params['mode'];
					break;
				}
			}
		}

		$viewer->assign('SELECTED_TAB_LABEL', $selectedTabLabel);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
		$viewer->assign('DETAILVIEW_WIDGETS', $this->record->widgets);
		$viewer->assign('FIELDS_HEADER', $fieldsInHeader);
		$viewer->assign('CUSTOM_FIELDS_HEADER', $this->record->getCustomHeaderFields());
		$viewer->assign('IS_EDITABLE', $this->record->getRecord()->isEditable($moduleName));
		$viewer->assign('IS_DELETABLE', $this->record->getRecord()->isDeletable($moduleName));

		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
		$linkModels = $this->record->getSideBarLinks($linkParams);
		$viewer->assign('QUICK_LINKS', $linkModels);
		$viewer->assign('DEFAULT_RECORD_VIEW', $currentUserModel->get('default_record_view'));

		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
		$viewer->assign('PICKLIST_DEPENDENCY_DATASOURCE', \App\Json::encode($picklistDependencyDatasource));

		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	public function preProcessTplName(Vtiger_Request $request)
	{
		return 'DetailViewPreProcess.tpl';
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$defaultMode = $this->defaultMode;
		if ($defaultMode == 'showDetailViewByMode') {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$this->record->getWidgets(['MODULE' => $moduleName, 'RECORD' => $recordId]);
			if (!($currentUserModel->get('default_record_view') === 'Summary' && $this->record->widgetsList)) {
				$defaultMode = 'showModuleDetailView';
			}
		} else if ($defaultMode === false) {
			$defaultMode = 'showDetailViewByMode';
		}
		echo $this->$defaultMode($request);
	}

	public function postProcess(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $this->record->getModule());
		$viewer->view('DetailViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$parentCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/leaflet/leaflet.css',
			'~libraries/leaflet/plugins/markercluster/MarkerCluster.Default.css',
			'~libraries/leaflet/plugins/markercluster/MarkerCluster.css',
			'~libraries/leaflet/plugins/awesome-markers/leaflet.awesome-markers.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($parentCssInstances, $modalInstances);
		return $cssInstances;
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.RelatedList',
			"modules.$moduleName.resources.RelatedList",
			'modules.Vtiger.resources.Widgets',
			'modules.Vtiger.resources.ListSearch',
			"modules.$moduleName.resources.ListSearch",
			'~libraries/leaflet/leaflet.js',
			'~libraries/leaflet/plugins/markercluster/leaflet.markercluster.js',
			'~libraries/leaflet/plugins/awesome-markers/leaflet.awesome-markers.js',
			"modules.OpenStreetMap.resources.Map",
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function showDetailViewByMode($request)
	{
		$requestMode = $request->get('requestMode');
		if ($requestMode == 'full') {
			return $this->showModuleDetailView($request);
		}
		return $this->showModuleBasicView($request);
	}

	/**
	 * Function shows the entire detail for the record
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	public function showModuleDetailView(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		if (!$this->recordStructure) {
			$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		}
		$structuredValues = $this->recordStructure->getStructure();

		$moduleModel = $recordModel->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('MODULE_TYPE', $moduleModel->getModuleType());
		return $viewer->view('DetailViewFullContents.tpl', $moduleName, true);
	}

	public function showModuleSummaryView($request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);

		$moduleModel = $recordModel->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		if (is_callable($moduleName . "_Record_Model", 'getStructure')) {
			$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		}
		return $viewer->view('ModuleSummaryBlockView.tpl', $moduleName, true);
	}

	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	 */
	public function showModuleBasicView(Vtiger_Request $request)
	{

		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$detailViewLinkParams = array('MODULE' => $moduleName, 'RECORD' => $recordId);
		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
		$this->record->getWidgets($detailViewLinkParams);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_SUMMARY', $this->showModuleSummaryView($request));
		$viewer->assign('DETAILVIEW_WIDGETS', $this->record->widgets);
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('VIEW', $request->get('view'));

		if (!$this->recordStructure) {
			$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		}
		$structuredValues = $this->recordStructure->getStructure();

		$moduleModel = $recordModel->getModule();

		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		if ($moduleModel->isSummaryViewSupported() && $this->record->widgetsList) {
			echo $viewer->view('SummaryViewWidgets.tpl', $moduleName, true);
		} else {
			echo $viewer->view('DetailViewSummaryContents.tpl', $moduleName, true);
		}
	}

	/**
	 * Function returns recent changes made on the record
	 * @param Vtiger_Request $request
	 */
	public function showRecentActivities(Vtiger_Request $request)
	{
		$type = 'changes';
		$parentRecordId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$whereCondition = $request->get('whereCondition');
		$moduleName = $request->getModule();

		if (empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!empty($limit)) {
			$pagingModel->set('limit', $limit);
		} else {
			$limit = AppConfig::module('ModTracker', 'NUMBER_RECORDS_ON_PAGE');
			$pagingModel->set('limit', $limit);
		}
		if (!empty($whereCondition)) {
			$type = is_array($whereCondition) ? current($whereCondition) : $whereCondition;
		}
		$recentActivities = ModTracker_Record_Model::getUpdates($parentRecordId, $pagingModel, $type);
		$pagingModel->calculatePageRange(count($recentActivities));

		if ($pagingModel->getCurrentPage() == ceil(ModTracker_Record_Model::getTotalRecordCount($parentRecordId, $type) / $pagingModel->getPageLimit())) {
			$pagingModel->set('nextPageExists', false);
		} else {
			$pagingModel->set('nextPageExists', true);
		}
		if ($type == 'changes') {
			$newChange = $request->has('newChange') ? $request->get('newChange') : ModTracker_Record_Model::isNewChange($parentRecordId);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('TYPE', $type);
		$viewer->assign('NEW_CHANGE', $newChange);
		$viewer->assign('PARENT_RACORD_ID', $parentRecordId);
		$viewer->assign('RECENT_ACTIVITIES', $recentActivities);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
		$viewer->assign('MODULE_BASE_NAME', 'ModTracker');
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$defaultView = AppConfig::module('ModTracker', 'DEFAULT_VIEW');
		if ($defaultView == 'List') {
			$tplName = 'RecentActivities.tpl';
		} else {
			$tplName = 'RecentActivitiesTimeLine.tpl';
		}
		if (!$request->get('skipHeader')) {
			echo $viewer->view('RecentActivitiesHeader.tpl', $moduleName, 'true');
		}
		echo $viewer->view($tplName, $moduleName, 'true');
	}

	/**
	 * Function returns latest comments
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	public function showRecentComments(Vtiger_Request $request)
	{
		$parentId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$moduleName = $request->getModule();

		if (empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}

		$recentComments = ModComments_Record_Model::getRecentComments($parentId, $pagingModel);
		$pagingModel->calculatePageRange(count($recentComments));
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');

		$viewer = $this->getViewer($request);
		$viewer->assign('COMMENTS', $recentComments);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);

		return $viewer->view('RecentComments.tpl', $moduleName, 'true');
	}

	/**
	 * Function returns related records
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	public function showRelatedList(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$targetControllerClass = null;

		// Added to support related list view from the related module, rather than the base module.
		if (!$targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'In' . $moduleName . 'Relation', $relatedModuleName, false)) {
			// If any module wants to have same view for all the relation, then invoke this.
			if (!$targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'InRelation', $relatedModuleName, false)) {
				// Default related list
				$targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'RelatedList', $moduleName);
			}
		}
		if ($targetControllerClass) {
			$targetController = new $targetControllerClass();
			return $targetController->process($request);
		}
	}

	/**
	 * Function sends the child comments for a comment
	 * @param Vtiger_Request $request
	 * @return mixed
	 */
	public function showChildComments(Vtiger_Request $request)
	{
		$parentCommentId = $request->get('commentid');
		$parentCommentModel = Vtiger_Record_Model::getInstanceById($parentCommentId);
		$childComments = $parentCommentModel->getChildComments();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');

		$viewer = $this->getViewer($request);
		$viewer->assign('PARENT_COMMENTS', $childComments);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
		$viewer->assign('TYPE_VIEW', "List");
		return $viewer->view('CommentsList.tpl', $moduleName, 'true');
	}

	/**
	 * Function send all the comments in thead
	 * @param Vtiger_Request $request
	 * @return mixed
	 */
	public function showThreadComments(Vtiger_Request $request)
	{
		$parentRecordId = $request->get('record');
		$commentRecordId = $request->get('commentid');
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$parentCommentModels = ModComments_Record_Model::getAllParentComments($parentRecordId);
		$currentCommentModel = Vtiger_Record_Model::getInstanceById($commentRecordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('PARENT_COMMENTS', $parentCommentModels);
		$viewer->assign('CURRENT_COMMENT', $currentCommentModel);
		return $viewer->view('ShowThreadComments.tpl', $moduleName, 'true');
	}

	/**
	 * Function sends all the comments for a parent(Accounts, Contacts etc)
	 * @param Vtiger_Request $request
	 * @return mixed
	 */
	public function showAllComments(Vtiger_Request $request)
	{
		$parentRecordId = $request->get('record');
		$commentRecordId = $request->get('commentid');
		$hierarchy = [];
		if ($request->has('hierarchy')) {
			$hierarchy = explode(',', $request->get('hierarchy'));
		}
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		$parentCommentModels = ModComments_Record_Model::getAllParentComments($parentRecordId, $hierarchy);
		$currentCommentModel = [];
		if (!empty($commentRecordId)) {
			$currentCommentModel = Vtiger_Record_Model::getInstanceById($commentRecordId);
		}

		$hierarchyList = ['LBL_COMMENTS_0', 'LBL_COMMENTS_1', 'LBL_COMMENTS_2'];
		$level = \App\ModuleHierarchy::getModuleLevel($request->getModule());
		if ($level > 0) {
			unset($hierarchyList[1]);
			if ($level > 1) {
				unset($hierarchyList[2]);
			}
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('PARENT_RECORD', $parentRecordId);
		$viewer->assign('HIERARCHY', $hierarchy);
		$viewer->assign('HIERARCHY_LIST', $hierarchyList);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
		$viewer->assign('PARENT_COMMENTS', $parentCommentModels);
		$viewer->assign('CURRENT_COMMENT', $currentCommentModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		return $viewer->view('ShowAllComments.tpl', $moduleName, 'true');
	}

	/**
	 * Function to get Ajax is enabled or not
	 * @param Vtiger_Record_Model record model
	 * @return <boolean> true/false
	 */
	public function isAjaxEnabled($recordModel)
	{
		return $recordModel->isEditable();
	}

	/**
	 * Function to get activities
	 * @param Vtiger_Request $request
	 * @return <List of activity models>
	 */
	public function getActivities(Vtiger_Request $request)
	{
		$moduleName = 'Calendar';
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($currentUserPriviligesModel->hasModulePermission($moduleName)) {
			$moduleName = $request->getModule();
			$recordId = $request->get('record');
			$pageNumber = $request->get('page');
			$pageLimit = $request->get('limit');
			$sortOrder = $request->get('sortorder');
			$orderBy = $request->get('orderby');
			$type = $request->get('type');
			if (empty($pageNumber)) {
				$pageNumber = 1;
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $pageNumber);
			$pagingModel->set('orderby', $orderBy);
			$pagingModel->set('sortorder', $sortOrder);

			if (!empty($pageLimit)) {
				$pagingModel->set('limit', $pageLimit);
			} else {
				$pagingModel->set('limit', 10);
			}
			if (!$this->record) {
				$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
			}
			$recordModel = $this->record->getRecord();
			$moduleModel = $recordModel->getModule();

			$relatedActivities = $moduleModel->getCalendarActivities($type, $pagingModel, 'all', $recordId);

			$colorList = [];
			foreach ($relatedActivities as $activityModel) {
				$colorList[$activityModel->getId()] = Settings_DataAccess_Module_Model::executeColorListHandlers('Calendar', $activityModel->getId(), $activityModel);
			}
			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('COLOR_LIST', $colorList);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('PAGING_MODEL', $pagingModel);
			$viewer->assign('PAGE_NUMBER', $pageNumber);
			$viewer->assign('ACTIVITIES', $relatedActivities);
			$viewer->assign('DATA_TYPE', $type);
			return $viewer->view('RelatedActivities.tpl', $moduleName, true);
		}
	}

	/**
	 * Function returns related records based on related moduleName
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	public function showRelatedRecords(Vtiger_Request $request)
	{
		$parentId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$searchParams = $request->get('search_params');
		$relatedModuleName = $request->get('relatedModule');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$columns = $request->get('col');
		$moduleName = $request->getModule();
		$totalCount = $request->get('totalCount');
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!empty($limit)) {
			$pagingModel->set('limit', $limit);
		} else {
			$pagingModel->set('limit', 10);
		}
		if ($sortOrder == 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'glyphicon glyphicon-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'glyphicon glyphicon-chevron-up';
		}
		if (empty($orderBy) && empty($sortOrder)) {
			if (is_numeric($relatedModuleName))
				$relatedModuleName = vtlib\Functions::getModuleName($relatedModuleName);
			$relatedInstance = CRMEntity::getInstance($relatedModuleName);
			$orderBy = $relatedInstance->default_order_by;
			$sortOrder = $relatedInstance->default_sort_order;
		}
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		$relationModel = $relationListView->getRelationModel();
		if ($relationModel->isFavorites() && Users_Privileges_Model::isPermitted($moduleName, 'FavoriteRecords')) {
			$favorites = $relationListView->getFavoriteRecords();
			if (!empty($favorites)) {
				$relationListView->get('query_generator')->addNativeCondition(['vtiger_crmentity.crmid' => $favorites]);
			}
		}
		if (!empty($searchParams)) {
			$searchParams = $relationListView->get('query_generator')->parseBaseSearchParamsToCondition($searchParams);
			$relationListView->set('search_params', $searchParams);
		}
		if (!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder', $sortOrder);
		}
		$models = $relationListView->getEntries($pagingModel);
		$header = $relationListView->getHeaders();
		$links = $relationListView->getLinks();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relationField = $relationModel->getRelationField();
		$noOfEntries = count($models);

		if ($columns) {
			$header = array_splice($header, 0, $columns);
		}
		foreach ($models as $record) {
			$colorList[$record->getId()] = Settings_DataAccess_Module_Model::executeColorListHandlers($relatedModuleName, $record->getId(), $record);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('COLOR_LIST', $colorList);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('LIMIT', $request->get('limit'));
		$viewer->assign('RELATED_RECORDS', $models);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_MODULE_NAME', $relatedModuleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);
		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			$totalCount = $relationListView->getRelatedEntriesCount();
		}
		if (!empty($totalCount)) {
			$pagingModel->set('totalCount', (int) $totalCount);
			$viewer->assign('TOTAL_ENTRIES', (int) $totalCount);
		}
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('COLUMN_NAME', $orderBy);
		$viewer->assign('COLUMNS', $columns);
		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('IS_DELETABLE', $relationModel->isDeletable());
		$viewer->assign('SHOW_CREATOR_DETAIL', $relationModel->showCreatorDetail());
		$viewer->assign('SHOW_COMMENT', $relationModel->showComment());
		return $viewer->view('SummaryWidgets.tpl', $moduleName, 'true');
	}

	public function showRelatedTree(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$parentId = $request->get('record');
		$relatedModuleName = $request->get('relatedModule');

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		$relationModel = $relationListView->getRelationModel();

		$header = $relationListView->getTreeHeaders();
		$entries = $relationListView->getTreeEntries();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORDID', $parentId);
		$viewer->assign('RELATED_MODULE_NAME', $relatedModuleName);
		$viewer->assign('RELATED_RECORDS', $entries);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('SHOW_CREATOR_DETAIL', $relationModel->showCreatorDetail());
		$viewer->assign('SHOW_COMMENT', $relationModel->showComment());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		return $viewer->view('RelatedTreeContent.tpl', $moduleName, 'true');
	}

	public function showRelatedProductsServices(Vtiger_Request $request)
	{

		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();

		$detailViewLinkParams = array('MODULE' => $moduleName, 'RECORD' => $recordId);
		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDID', $recordId);
		$viewer->assign('RECORD', $recordModel);

		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LIMIT', 'no_limit');
		if (!$this->recordStructure) {
			$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		}
		$structuredValues = $this->recordStructure->getStructure();

		$moduleModel = $recordModel->getModule();

		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());

		echo $viewer->view('DetailViewProductsServicesContents.tpl', $moduleName, true);
	}

	/**
	 * Show recent relation
	 * @param Vtiger_Request $request
	 * @return string
	 */
	public function showRecentRelation(Vtiger_Request $request)
	{
		$pageNumber = $request->get('page');
		$limitPage = $request->get('limit');
		$moduleName = $request->getModule();

		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		if (empty($limitPage)) {
			$limitPage = 10;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('limit', $limitPage);
		$config = OSSMail_Module_Model::getComposeParameters();
		$histories = Vtiger_HistoryRelation_Widget::getHistory($request, $pagingModel);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD_ID', $request->get('record'));
		$viewer->assign('HISTORIES', $histories);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('POPUP', $config['popup']);
		$viewer->assign('NO_MORE', $request->get('noMore'));
		return $viewer->view('HistoryRelation.tpl', $moduleName, true);
	}

	public function showOpenStreetMap($request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$coordinateModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinates = $coordinateModel->readCoordinates($recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('COORRDINATES', $coordinates);
		return $viewer->view('DetailViewMap.tpl', $moduleName, true);
	}
}
