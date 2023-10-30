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
 * Class Vtiger_Detail_View.
 *
 * @package View
 */
class Vtiger_Detail_View extends Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	/** @var Vtiger_DetailView_Model Record model instance. */
	public $record;

	/** @var Vtiger_DetailRecordStructure_Model Detail record structure model instance. */
	protected $recordStructure;

	/** @var string Default request mode */
	public $defaultMode = '';

	/** @var string Page title. */
	protected $pageTitle = 'LBL_VIEW_DETAIL';

	/**
	 * Construct.
	 */
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
		$this->exposeMethod('showParentComments');
		$this->exposeMethod('showAllComments');
		$this->exposeMethod('showThreadComments');
		$this->exposeMethod('showSearchComments');
		$this->exposeMethod('getActivities');
		$this->exposeMethod('showRelatedProductsServices');
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showRelatedTree');
		$this->exposeMethod('showRecentRelation');
		$this->exposeMethod('showOpenStreetMap');
		$this->exposeMethod('showInventoryDetails');
		$this->exposeMethod('showChat');
		$this->exposeMethod('processWizard');
		$this->exposeMethod('showModTrackerByField');
		$this->exposeMethod('showCharts');
		$this->exposeMethod('showInventoryEntries');
		$this->exposeMethod('showPDF');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->record = Vtiger_DetailView_Model::getInstance($request->getModule(), $request->getInteger('record'));
		if (!$this->record->getRecord()->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);

		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$recordModel = $this->record->getRecord();
		$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$fieldsInHeader = $this->recordStructure->getFieldInHeader();

		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($recordModel);
		$eventHandler->setModuleName($moduleName);
		$viewLinks = $this->record->getDetailViewLinks([
			'MODULE' => $moduleName,
			'RECORD' => $recordId,
			'VIEW' => $request->getByType('view', 2),
		]);
		$eventHandler->setParams([
			'viewLinks' => $viewLinks,
		]);
		$eventHandler->trigger('DetailViewBefore');
		$viewLinks = $eventHandler->getParams()['viewLinks'];

		$this->record->getWidgets();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$moduleModel = $this->record->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$selectedTabLabel = $request->getByType('tab_label', 'Text');
		$requestMode = $request->getByType('requestMode');
		$mode = $request->getMode();
		if (empty($selectedTabLabel) && !empty($requestMode)) {
			if ('full' == $requestMode) {
				$selectedTabLabel = 'LBL_RECORD_DETAILS';
			} else {
				$selectedTabLabel = 'LBL_RECORD_SUMMARY';
			}
		} elseif (empty($requestMode) && empty($mode)) {
			$selectedTabLabel = App\Config::module($moduleName, 'DEFAULT_VIEW_RECORD');
			if (empty($selectedTabLabel)) {
				if ('Detail' === $currentUserModel->get('default_record_view')) {
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
		$detailViewLabel = [];
		if (isset($viewLinks['DETAILVIEWRELATED']) && \is_array($viewLinks['DETAILVIEWRELATED'])) {
			foreach ($viewLinks['DETAILVIEWRELATED']  as $link) {
				$detailViewLabel[] = $link->getLabel();
			}
		}
		if (isset($viewLinks['DETAILVIEWTAB']) && \is_array($viewLinks['DETAILVIEWTAB'])) {
			foreach ($viewLinks['DETAILVIEWTAB']  as $link) {
				$detailViewLabel[] = $link->getLabel();
				if ($link->getLabel() === $selectedTabLabel) {
					$params = vtlib\Functions::getQueryParams($link->getUrl());
					$this->defaultMode = $params['mode'];
				}
			}
			if (!\in_array($selectedTabLabel, $detailViewLabel)) {
				$selectedTabLabel = 'LBL_RECORD_SUMMARY';
			}
		}
		$viewer->assign('SELECTED_TAB_LABEL', $selectedTabLabel);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('DETAILVIEW_LINKS', $viewLinks);
		$viewer->assign('DETAILVIEW_WIDGETS', $this->record->widgets);
		$viewer->assign('FIELDS_HEADER', $fieldsInHeader);
		$viewer->assign('CUSTOM_FIELDS_HEADER', $this->record->getCustomHeaderFields());
		$viewer->assign('IS_EDITABLE', $this->record->getRecord()->isEditable());
		$viewer->assign('IS_DELETABLE', $this->record->getRecord()->privilegeToMoveToTrash());
		$viewer->assign('VIEW_MODEL', $this->record);
		$viewer->assign('QUICK_LINKS', $this->record->getSideBarLinks([
			'MODULE' => $moduleName,
			'ACTION' => $request->getByType('view', 1),
		]));
		$viewer->assign('DEFAULT_RECORD_VIEW', $currentUserModel->get('default_record_view'));
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		$viewer->assign('RECORD_ACTIVITY_NOTIFIER', $recordId && \App\Config::performance('recordActivityNotifier', false) && $moduleModel->isTrackingEnabled() && $moduleModel->isPermitted('RecordActivityNotifier'));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/** {@inheritdoc} */
	public function preProcessTplName(App\Request $request)
	{
		return 'Detail/PreProcess.tpl';
	}

	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		$defaultMode = $this->defaultMode;
		if ('showDetailViewByMode' === $defaultMode) {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$this->record->getWidgets();
			if (!('Summary' === $currentUserModel->get('default_record_view') && $this->record->widgetsList)) {
				$defaultMode = 'showModuleDetailView';
			}
		}
		if (!$defaultMode) {
			$defaultMode = 'showDetailViewByMode';
		}
		echo $this->{$defaultMode}($request);
	}

	public function postProcess(App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $this->record->getModule());
		$viewer->view('Detail/PostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	/**
	 * Function to get the list of Css models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_CssScript_Model[]
	 */
	public function getHeaderCss(App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/leaflet/dist/leaflet.css',
			'~libraries/leaflet.markercluster/dist/MarkerCluster.Default.css',
			'~libraries/leaflet.markercluster/dist/MarkerCluster.css',
			'~libraries/leaflet.awesome-markers/dist/leaflet.awesome-markers.css',
		]));
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			'~libraries/split.js/dist/split.js',
			'modules.Vtiger.resources.List',
			'modules.Vtiger.resources.ListSearch',
			"modules.$moduleName.resources.ListSearch",
			'modules.Vtiger.resources.RelatedList',
			"modules.$moduleName.resources.RelatedList",
			'modules.Vtiger.resources.Widgets',
			'~libraries/leaflet/dist/leaflet.js',
			'~libraries/leaflet.markercluster/dist/leaflet.markercluster.js',
			'~libraries/leaflet.awesome-markers/dist/leaflet.awesome-markers.js',
			'modules.OpenStreetMap.resources.Map',
			'modules.Vtiger.resources.dashboards.Widget',
			'~libraries/chart.js/dist/Chart.js',
		];
		if (\App\Privilege::isPermitted('Chat')) {
			$jsFileNames[] = '~layouts/basic/modules/Chat/resources/Chat.js';
		}
		return array_merge(
			parent::getFooterScripts($request),
			$this->checkAndConvertJsScripts($jsFileNames)
		);
	}

	public function showDetailViewByMode(App\Request $request)
	{
		if ('full' === $request->getByType('requestMode', 1)) {
			return $this->showModuleDetailView($request);
		}
		return $this->showModuleBasicView($request);
	}

	/**
	 * Function shows the entire detail for the record.
	 *
	 * @param \App\Request $request
	 *
	 * @return <type>
	 */
	public function showModuleDetailView(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = $this->record->getRecord();
		if (!$this->recordStructure) {
			$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		}
		$structuredValues = $this->recordStructure->getStructure();

		$moduleModel = $recordModel->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('VIEW_MODEL', $this->record);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('MODULE_TYPE', $moduleModel->getModuleType());
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		if ($request->getBoolean('toWidget')) {
			return $viewer->view('Detail/Widget/BlockView.tpl', $moduleName, true);
		}
		return $viewer->view('Detail/FullContents.tpl', $moduleName, true);
	}

	public function showModuleSummaryView(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = $this->record->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);

		$moduleModel = $recordModel->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('VIEW_MODEL', $this->record);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		if (\is_callable($moduleName . '_Record_Model', 'getStructure')) {
			$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		}
		return $viewer->view('Detail/Widget/GeneralInfo.tpl', $moduleName, true);
	}

	/**
	 * Function shows basic detail for the record.
	 *
	 * @param \App\Request $request
	 */
	public function showModuleBasicView(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$recordModel = $this->record->getRecord();
		$viewLinks = $this->record->getDetailViewLinks([
			'MODULE' => $moduleName,
			'RECORD' => $recordId,
			'VIEW' => $request->getByType('view', 2),
		]);
		$this->record->getWidgets();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_SUMMARY', $this->showModuleSummaryView($request));
		$viewer->assign('DETAILVIEW_WIDGETS', $this->record->widgets);
		$viewer->assign('DETAILVIEW_LINKS', $viewLinks);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));

		$viewer->assign('VIEW', $request->getByType('view', 1));
		if (!$this->recordStructure) {
			$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		}
		$structuredValues = $this->recordStructure->getStructure();
		$moduleModel = $recordModel->getModule();
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('VIEW_MODEL', $this->record);
		$viewer->assign('MODULE_TYPE', $moduleModel->getModuleType());
		$viewer->assign('HIERARCHY_VALUE', $this->getHierarchyValue($request));
		$viewer->assign('HIERARCHY', \App\ModuleHierarchy::getModuleLevel($moduleName));
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		if ($moduleModel->isSummaryViewSupported() && $this->record->widgetsList) {
			return $viewer->view('DetailViewSummaryView.tpl', $moduleName, true);
		}
		return $viewer->view('Detail/FullContents.tpl', $moduleName, true);
	}

	/**
	 * Function returns recent changes made on the record.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function showRecentActivities(App\Request $request)
	{
		$moduleName = $request->getModule();
		include_once 'modules/ModTracker/ModTracker.php';
		$type = 'changes';
		$parentRecordId = $request->getInteger('record');
		$pageNumber = $request->getInteger('page');
		$limit = $request->getInteger('limit');
		$whereCondition = $request->getArray('whereCondition', 'Standard');
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!empty($limit)) {
			$pagingModel->set('limit', $limit);
		} else {
			$limit = App\Config::module('ModTracker', 'NUMBER_RECORDS_ON_PAGE');
			$pagingModel->set('limit', $limit);
		}
		if (!empty($whereCondition)) {
			$type = \is_array($whereCondition) ? current($whereCondition) : $whereCondition;
		}
		$recentActivities = ModTracker_Record_Model::getUpdates($parentRecordId, $pagingModel, $type);
		$pagingModel->calculatePageRange(\count($recentActivities));

		if ($pagingModel->getCurrentPage() == ceil(ModTracker_Record_Model::getTotalRecordCount($parentRecordId, $type) / $pagingModel->getPageLimit())) {
			$pagingModel->set('nextPageExists', false);
		} else {
			$pagingModel->set('nextPageExists', true);
		}
		if ('changes' === $type) {
			$newChange = $request->has('newChange') ? $request->getBoolean('newChange') : ModTracker_Record_Model::isNewChange($parentRecordId);
		} else {
			$newChange = false;
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('TYPE', $type);
		$viewer->assign('NEW_CHANGE', $newChange ?? null);
		$viewer->assign('PARENT_RACORD_ID', $parentRecordId);
		$viewer->assign('RECENT_ACTIVITIES', $recentActivities);
		$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
		$viewer->assign('MODULE_BASE_NAME', 'ModTracker');
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('VIEW_MODEL', $this->record);
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		$defaultView = App\Config::module('ModTracker', 'DEFAULT_VIEW');
		if ('List' == $defaultView) {
			$tplName = 'RecentActivities.tpl';
		} else {
			$tplName = 'RecentActivitiesTimeLine.tpl';
		}
		if (!$request->getBoolean('skipHeader')) {
			$viewer->view('RecentActivitiesHeader.tpl', $moduleName);
		}
		return $viewer->view($tplName, $moduleName, true);
	}

	/**
	 * Function returns latest comments.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return string
	 */
	public function showRecentComments(App\Request $request)
	{
		if (!\App\Privilege::isPermitted('ModComments')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$parentId = $request->getInteger('record');
		$pageNumber = $request->getInteger('page');
		$limit = $request->getInteger('limit');
		$moduleName = $request->getModule();
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}
		$parentCommentModels = ModComments_Record_Model::getAllParentComments($parentId, $moduleName, $this->getHierarchy($request), $pagingModel);
		$pagingModel->calculatePageRange(\count($parentCommentModels));
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		$viewer = $this->getViewer($request);
		$viewer->assign('PARENT_RECORD', $parentId);
		$viewer->assign('PARENT_COMMENTS', $parentCommentModels);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		$viewer->assign('CURRENT_COMMENT', null);
		return $viewer->view('RecentComments.tpl', $moduleName, true);
	}

	/**
	 * Function returns related records.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return string
	 */
	public function showRelatedList(App\Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$targetControllerClass = null;
		if (!\App\Privilege::isPermitted($relatedModuleName)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		// Added to support related list view from the related module, rather than the base module.
		if (!($targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'In' . $moduleName . 'Relation', $relatedModuleName, false)) && !($targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'InRelation', $relatedModuleName, false))) {
			// Default related list
			$targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'RelatedList', $moduleName);
		}
		if ($targetControllerClass) {
			$targetController = new $targetControllerClass();
			return $targetController->process($request);
		}
	}

	/**
	 * Function sends the child comments for a comment.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return mixed
	 */
	public function showChildComments(App\Request $request)
	{
		if (!\App\Privilege::isPermitted('ModComments')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$parentCommentId = $request->getInteger('commentid');
		$parentCommentModel = ModComments_Record_Model::getInstanceById($parentCommentId);
		$childComments = $parentCommentModel->getChildComments();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		$viewer = $this->getViewer($request);
		$viewer->assign('PARENT_COMMENTS', $childComments);
		$viewer->assign('CHILD_COMMENTS', true);
		$viewer->assign('NO_COMMENT_FORM', true);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
		$viewer->assign('CURRENT_COMMENT', null);
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('CommentsList.tpl', $request->getModule(), true);
	}

	/**
	 * Function sends the parent comment for a comment.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return mixed
	 */
	public function showParentComments(App\Request $request)
	{
		if (!\App\Privilege::isPermitted('ModComments')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$parentCommentModel = ModComments_Record_Model::getInstanceById($request->getInteger('commentid'));
		$parentThreadComments = $parentCommentModel->getParentComments();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		$viewer = $this->getViewer($request);
		$viewer->assign('PARENT_COMMENTS', $parentThreadComments);
		$viewer->assign('SHOW_CHILD_COMMENTS', true);
		$viewer->assign('NO_COMMENT_FORM', true);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
		$viewer->assign('CURRENT_COMMENT', null);
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('CommentsList.tpl', $request->getModule(), true);
	}

	/**
	 * Function send all the comments in thead.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return mixed
	 */
	public function showThreadComments(App\Request $request)
	{
		if (!\App\Privilege::isPermitted('ModComments')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$parentRecordId = $request->getInteger('record');
		$commentRecordId = $request->getInteger('commentid');
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$parentCommentModels = ModComments_Record_Model::getAllParentComments($parentRecordId, $moduleName);
		$currentCommentModel = Vtiger_Record_Model::getInstanceById($commentRecordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('PARENT_COMMENTS', $parentCommentModels);
		$viewer->assign('CURRENT_COMMENT', $currentCommentModel);
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('ShowThreadComments.tpl', $moduleName, true);
	}

	/**
	 * Function sends all the comments for a parent(Accounts, Contacts etc).
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return mixed
	 */
	public function showAllComments(App\Request $request)
	{
		if (!\App\Privilege::isPermitted('ModComments')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$parentRecordId = $request->getInteger('record');
		$commentRecordId = $request->getInteger('commentid');
		$moduleName = $request->getModule();
		$hierarchy = $this->getHierarchy($request);
		$hierarchyValue = $this->getHierarchyValue($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		$parentCommentModels = ModComments_Record_Model::getAllParentComments($parentRecordId, $moduleName, $hierarchy);
		$currentCommentModel = [];
		if (!empty($commentRecordId)) {
			$currentCommentModel = Vtiger_Record_Model::getInstanceById($commentRecordId);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('PARENT_RECORD', $parentRecordId);
		$viewer->assign('HIERARCHY', \App\ModuleHierarchy::getModuleLevel($moduleName));
		$viewer->assign('HIERARCHY_VALUE', $hierarchyValue);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
		$viewer->assign('PARENT_COMMENTS', $parentCommentModels);
		$viewer->assign('CURRENT_COMMENT', $currentCommentModel);
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('ShowAllComments.tpl', $moduleName, true);
	}

	/**
	 * Function send all the comments with search value.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return mixed
	 */
	public function showSearchComments(App\Request $request)
	{
		if (!\App\Privilege::isPermitted('ModComments')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$isWidget = false;
		if (!$request->isEmpty('is_widget', true)) {
			$isWidget = $request->getBoolean('is_widget');
		}
		if ($request->isEmpty('search_key', true)) {
			return $this->showAllComments($request);
		}
		$searchValue = $request->getByType('search_key', 'Text');
		$parentCommentModels = ModComments_Record_Model::getSearchComments($recordId, $moduleName, $searchValue, $isWidget, $this->getHierarchy($request));

		$viewer = $this->getViewer($request);
		if (!empty($parentCommentModels)) {
			$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
			$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
			$viewer->assign('CURRENTUSER', $currentUserModel);
			$viewer->assign('PARENT_COMMENTS', $parentCommentModels);
			$viewer->assign('CURRENT_COMMENT', false);
			$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
			$viewer->assign('NO_COMMENT_FORM', true);
			$viewer->assign('MODULE', $moduleName);
			if (false === $isWidget) {
				$viewer->assign('SHOW_CHILD_COMMENTS', true);
			} else {
				$viewer->assign('BUTTON_SHOW_PARENT', true);
			}
			return $viewer->view('CommentsList.tpl', $moduleName, true);
		}
		return $viewer->view('NoComments.tpl', $moduleName, true);
	}

	/**
	 * Returns value source to display comments.
	 *
	 * @param \App\Request $request
	 *
	 * @return mixed
	 */
	private function getHierarchyValue(App\Request $request)
	{
		$moduleName = $request->getModule();
		$hierarchyValue = $request->getExploded('hierarchy', ',', 'Standard');
		$cacheName = 'DEFAULT_SOURCE_COMMENTS_' . $moduleName;
		if (empty($hierarchyValue)) {
			if (App\Session::has($cacheName)) {
				$hierarchyValue = App\Session::get($cacheName);
			} else {
				$hierarchyValue = \Config\Modules\ModComments::$defaultSource;
			}
		} else {
			App\Session::set($cacheName, $hierarchyValue);
		}
		return $hierarchyValue;
	}

	/**
	 * Get comments hierarchy.
	 *
	 * @param \App\Request $request
	 * @param array        $hierarchyValue
	 *
	 * @return array
	 */
	public function getHierarchy(App\Request $request, array $hierarchyValue = [])
	{
		if (empty($hierarchyValue)) {
			$hierarchyValue = $this->getHierarchyValue($request);
		}
		$moduleName = $request->getModule();
		$hierarchy = [];
		$level = \App\ModuleHierarchy::getModuleLevel($moduleName);
		if (0 === $level) {
			$hierarchy = \in_array('related', $hierarchyValue) ? [1, 2, 3, 4] : [];
		} elseif (1 === $level) {
			$hierarchy = \in_array('related', $hierarchyValue) ? [2, 3] : [];
		}
		if (\in_array('current', $hierarchyValue)) {
			$hierarchy[] = $level;
		}
		return $hierarchy;
	}

	/**
	 * Function to get Ajax is enabled or not.
	 *
	 * @param Vtiger_Record_Model record model
	 * @param mixed $recordModel
	 *
	 * @return bool true/false
	 */
	public function isAjaxEnabled($recordModel)
	{
		return $recordModel->isEditable();
	}

	/**
	 * Function to get activities.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return <List of activity models>
	 */
	public function getActivities(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission('Calendar')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$moduleName = $request->getModule();
		$pageNumber = $request->getInteger('page');
		$pageLimit = $request->getInteger('limit');
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!empty($pageLimit)) {
			$pagingModel->set('limit', $pageLimit);
		} else {
			$pagingModel->set('limit', 10);
		}
		$recordModel = $this->record->getRecord();
		$relatedActivities = [];
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'Calendar');
		if ($relationListView) {
			$moduleModel = $relationListView->getQueryGenerator()->getModuleModel();
			$relationListView->getRelationModel()->set('QueryFields', $moduleModel->getFields());
			$searchParams = App\Condition::validSearchParams($moduleModel->getName(), $request->getArray('search_params'));
			if (!empty($searchParams) && \is_array($searchParams)) {
				$transformedSearchParams = $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
				$relationListView->set('search_params', $transformedSearchParams);
			}
			if (!$request->has('orderby')) {
				$relationListView->set('orderby', ['date_start' => \App\Db::ASC, 'time_start' => \App\Db::ASC]);
			} else {
				$relationListView->set('orderby', $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL));
			}
			if (App\Config::relation('SHOW_RECORDS_COUNT') && $pagingModel->isEmpty('totalCount')) {
				$pagingModel->set('totalCount', $relationListView->getRelationQuery()->count());
			}

			$relatedActivities = $relationListView->getEntries($pagingModel);
			foreach ($relatedActivities as $recordModel) {
				$recordModel->set('selectedusers', (new \App\Db\Query())->select(['inviteesid'])->from('u_#__activity_invitation')->where(['activityid' => $recordModel->getId()])->column());
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('ACTIVITIES', $relatedActivities);
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('RelatedActivities.tpl', $moduleName, true);
	}

	/**
	 * Function returns related records based on related moduleName.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return string
	 */
	public function showRelatedRecords(App\Request $request)
	{
		$parentId = $request->getInteger('record');
		$pageNumber = $request->getInteger('page');
		$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
		$limit = 10;
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$columns = 0;
		$moduleName = $request->getModule();
		$searchParams = App\Condition::validSearchParams($relatedModuleName, $request->getArray('search_params'));
		$totalCount = $request->getInteger('totalCount');
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!$request->isEmpty('limit', true)) {
			$limit = $request->getInteger('limit');
		}
		$pagingModel->set('limit', $limit);
		if (is_numeric($relatedModuleName)) {
			$relatedModuleName = \App\Module::getModuleName($relatedModuleName);
		}
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!\App\Privilege::isPermitted($relatedModuleName)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $relationId, $cvId);
		if ($fieldRelation = $request->getArray('fromRelation', \App\Purifier::ALNUM, [], \App\Purifier::STANDARD)) {
			if (($parentId = $parentRecordModel->get($fieldRelation['relatedField'])) && \App\Record::isExists($parentId)) {
				$moduleName = \App\Record::getType($parentId);
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
				$relationId = $fieldRelation['relationId'];
				$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $relationId);
			} else {
				$relationListView->getQueryGenerator()->addNativeCondition((new \yii\db\Expression('0 > 1')));
			}
		}
		$relationModel = $relationListView->getRelationModel();
		if ($relationModel->isFavorites() && \App\Privilege::isPermitted($moduleName, 'FavoriteRecords')) {
			$favorites = $relationListView->getFavoriteRecords();
			if (!empty($favorites)) {
				$relationListView->getQueryGenerator()->addNativeCondition(['vtiger_crmentity.crmid' => $favorites]);
			}
		}
		if (!empty($searchParams)) {
			$searchParams = $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
			$relationListView->set('search_params', $searchParams);
		}
		$orderBy = $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL);
		if (empty($orderBy)) {
			$moduleInstance = CRMEntity::getInstance($relatedModuleName);
			if ($moduleInstance->default_order_by && $moduleInstance->default_sort_order) {
				$orderBy = [];
				foreach ((array) $moduleInstance->default_order_by as $value) {
					$orderBy[$value] = $moduleInstance->default_sort_order;
				}
			}
		}
		if (!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
		}
		$viewer = $this->getViewer($request);
		$viewType = !$request->isEmpty('viewType') ? $request->getByType('viewType') : '';
		if ('ListWithSummary' === $viewType) {
			$header = $relationListView->getHeaders();
			if ($summaryHeaders = $relationListView->getRelatedModuleModel()->getSummaryViewFieldsList()) {
				$relationListView->setFields(array_keys($summaryHeaders));
				$summaryHeaders = $relationListView->getHeaders();
			}
			$viewer->assign('RELATED_SUMMARY_HEADERS', $summaryHeaders);
			if ($request->has('fields')) {
				$relationListView->setFields($request->getExploded('fields'));
				$header = $relationListView->getHeaders();
				$relationListView->setFields(array_keys(array_merge($summaryHeaders, $header)));
			}
		} else {
			if ($request->has('fields')) {
				$relationListView->setFields($request->getExploded('fields'));
			}
			$header = $relationListView->getHeaders();
		}
		$models = $relationListView->getEntries($pagingModel);
		$links = $relationListView->getLinks();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relationField = $relationModel->getRelationField();
		$noOfEntries = \count($models);

		if ($request->has('col')) {
			$columns = $request->getInteger('col');
			$header = array_splice($header, 0, $columns);
		}
		$viewer->assign('TYPE_VIEW', $viewType);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('LIMIT', $limit);
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
		if (App\Config::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			$totalCount = $relationListView->getRelatedEntriesCount();
		}
		if (empty($totalCount)) {
			$totalCount = 0;
		}
		$pagingModel->set('totalCount', (int) $totalCount);
		$viewer->assign('TOTAL_ENTRIES', (int) $totalCount);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();
		$viewer->assign('VIEW_MODEL', $relationListView);
		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('COLUMNS', $columns);
		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('INVENTORY_FIELDS', $relationModel->getRelationInventoryFields());
		$viewer->assign('SHOW_CREATOR_DETAIL', $relationModel->showCreatorDetail());
		$viewer->assign('SHOW_COMMENT', $relationModel->showComment());
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		$viewer->assign('NO_RESULT_TEXT', $request->getBoolean('no_result_text'));
		$viewer->assign('RELATION_ID', $relationId);
		return $viewer->view('SummaryWidgets.tpl', $moduleName, true);
	}

	public function showRelatedTree(App\Request $request)
	{
		$moduleName = $request->getModule();
		$parentId = $request->getInteger('record');
		$relatedModuleName = $request->getByType('relatedModule', 2);
		if (!\App\Privilege::isPermitted($relatedModuleName)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		$relationModel = $relationListView->getRelationModel();

		$header = $relationListView->getTreeHeaders();
		$entries = $relationListView->getTreeEntries();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORDID', $parentId);
		$viewer->assign('VIEW_MODEL', $relationListView);
		$viewer->assign('RELATED_MODULE_NAME', $relatedModuleName);
		$viewer->assign('RELATED_RECORDS', $entries);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('SHOW_CREATOR_DETAIL', (bool) $relationModel->get('creator_detail'));
		$viewer->assign('SHOW_COMMENT', (bool) $relationModel->get('relation_comment'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('RelatedTreeContent.tpl', $moduleName, true);
	}

	public function showRelatedProductsServices(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$recordModel = $this->record->getRecord();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDID', $recordId);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('VIEW_MODEL', $this->record);
		$viewer->assign('DETAILVIEW_LINKS', $this->record->getDetailViewLinks([
			'MODULE' => $moduleName,
			'RECORD' => $recordId,
			'VIEW' => $request->getByType('view', 2),
		]));
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('LIMIT', 0);
		if (!$this->recordStructure) {
			$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		}
		$structuredValues = $this->recordStructure->getStructure();
		$moduleModel = $recordModel->getModule();
		$relations = \Vtiger_Relation_Model::getAllRelations($moduleModel, false, true, true, 'modulename');
		foreach ($relations as $relation) {
			$relation->set('parentRecord', $recordModel);
		}
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('RELATIONS', $relations);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('DetailViewProductsServicesContents.tpl', $moduleName, true);
	}

	/**
	 * Function returns related records based on related moduleName.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function showInventoryEntries(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$pageNumber = $request->getInteger('page');
		$moduleName = $request->getModule();
		if (empty($pageNumber)) {
			$pageNumber = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$limit = $request->isEmpty('limit', true) ? 10 : $request->getInteger('limit');
		$pagingModel->set('limit', $limit);
		$entries = \Vtiger_Inventory_Model::getInventoryDataById($recordId, $moduleName, $pagingModel);
		$inventoryModel = \Vtiger_Inventory_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);
		$columns = $request->getExploded('fields');
		$header = [];
		foreach ($columns as $fieldName) {
			$fieldModel = $inventoryModel->getField($fieldName);
			if ($fieldModel && $fieldModel->isVisibleInDetail()) {
				$header[$fieldName] = $fieldModel;
			}
		}
		$viewer->assign('LIMIT', $limit);
		$viewer->assign('ENTRIES', $entries);
		$viewer->assign('HEADER_FIELD', $header);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		return $viewer->view('Detail/Widget/InventoryBlock.tpl', $moduleName, true);
	}

	/**
	 * Function shows PDF.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function showPDF(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$templateId = $request->getInteger('template');
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('TEMPLATE', $templateId);
		$viewer->assign('MODULE_NAME', $moduleName);
		return $viewer->view('Detail/Widget/PDFViewer.tpl', $moduleName, true);
	}

	/**
	 * Show recent relation.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function showRecentRelation(App\Request $request)
	{
		$pageNumber = $request->getInteger('page');
		$limitPage = $request->getInteger('limit');
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
		$viewer->assign('VIEW_MODEL', $this->record);
		$viewer->assign('RECORD_ID', $request->getInteger('record'));
		$viewer->assign('HISTORIES', $histories);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('POPUP', $config['popup']);
		$viewer->assign('NO_MORE', $request->getBoolean('noMore'));
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		$viewer->assign('IS_FULLSCREEN', $request->getBoolean('isFullscreen'));
		return $viewer->view('HistoryRelation.tpl', $moduleName, true);
	}

	/**
	 * Show open street map.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermittedToRecord
	 *
	 * @return \html
	 */
	public function showOpenStreetMap(App\Request $request)
	{
		if (!\App\Privilege::isPermitted('OpenStreetMap')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$coordinateModel = OpenStreetMap_Coordinate_Model::getInstance();
		$coordinates = $coordinateModel->readCoordinates($recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('COORRDINATES', $coordinates);
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('DetailViewMap.tpl', $moduleName, true);
	}

	/**
	 * Show chat for record.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function showChat(App\Request $request)
	{
		if (\App\User::getCurrentUserId() !== \App\User::getCurrentUserRealId()) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		$viewer->view('Detail/Chat.tpl', 'Chat');
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		return \App\Language::translate($moduleName, $moduleName) . ' ' . $this->record->getRecord()->getDisplayName();
	}

	/**
	 * Show inventory details from record for specified module.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return string
	 */
	public function showInventoryDetails(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('VIEW', 'Detail');
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('Detail/InventoryView.tpl', $moduleName, true);
	}

	/**
	 * Show process wizard for record.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function processWizard(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = $this->record->getRecord();
		$processWizardModel = Vtiger_ProcessWizard_Model::getInstance($recordModel);
		if ($request->has('step')) {
			$processWizardModel->setStep($request->getInteger('step'));
		}
		$step = $processWizardModel->getStep();
		$viewer = $this->getViewer($request);
		$viewer->assign('PROCESS_WIZARD', $processWizardModel);
		$viewer->assign('STEP', $step);
		$viewer->assign('STEP_URL', "index.php?module={$moduleName}&view=Detail&record={$recordModel->getId()}&mode=processWizard&tab_label=LBL_RECORD_PROCESS_WIZARD&step=");
		$viewer->assign('RECORD', $recordModel);
		if (!$this->recordStructure) {
			$this->recordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		}
		$structuredValues = $this->recordStructure->getStructure();
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		if (!empty($step['quickEdit'])) {
			$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		} else {
			$viewer->assign('IS_AJAX_ENABLED', false);
		}
		$viewer->assign('BLOCK_LIST', $recordModel->getModule()->getBlocks());
		$viewer->assign('MODULE_MODEL', $recordModel->getModule());
		$viewer->assign('VIEW_MODEL', $this->record);
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('Detail/ProcessWizard.tpl', $moduleName, true);
	}

	/**
	 * Show history by field.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function showModTrackerByField(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordModel = $this->record->getRecord();
		$fieldName = $request->getByType('field', 'Alnum');
		$value = $recordModel->get($fieldName);
		$fieldModel = $recordModel->getModule()->getFieldByName($fieldName);
		$history = [];
		$dataEnd = null;
		foreach (ModTracker_Record_Model::getFieldHistory($request->getInteger('record'), $fieldName) as $row) {
			$dataStart = $row['changedon'];
			$label = $row['postvalue'];
			if (!isset($history[$label])) {
				$history[$label] = [
					'time' => 0,
					'value' => $fieldModel->getDisplayValue($label, $recordModel->getId(), $recordModel),
				];
			}
			$history[$label]['date'] = $dataStart;
			if (isset($history[$row['prevalue']]) && $row['prevalue'] !== $label) {
				$history[$row['prevalue']]['time'] += \App\Fields\DateTime::getDiff($dataStart, $dataEnd, 'minutes');
			}
			$dataEnd = $dataStart;
		}
		$locks = array_merge_recursive(\App\RecordStatus::getLockStatus($moduleName), $recordModel->getEntity()->getLockFields());
		if (isset($history[$value]) && (!isset($locks[$fieldName]) || !\in_array($value, $locks[$fieldName]))) {
			$history[$value]['time'] += \App\Fields\DateTime::getDiff($history[$value]['date'], date('Y-m-d H:i:s'), 'minutes');
		}
		uasort($history, fn ($a, $b) => strnatcmp($b['date'], $a['date']));
		$viewer = $this->getViewer($request);
		$viewer->assign('VIEW', 'Detail');
		$viewer->assign('FIELD_HISTORY', $history);
		$viewer->assign('FIELD_LABEL', $recordModel->getField($fieldName)->getFieldLabel());
		$viewer->assign('IS_READ_ONLY', $request->getBoolean('isReadOnly') || $this->record->getRecord()->isReadOnly());
		return $viewer->view('Detail/Widget/UpdatesListContent.tpl', $moduleName, true);
	}

	/**
	 * Show Chart.
	 *
	 * @param App\Request $request
	 */
	public function showCharts(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$widgetId = $request->getByType('widgetId', \App\Purifier::INTEGER);
		$widgetData = (new App\Db\Query())->from('vtiger_widgets')->where(['id' => $widgetId, 'tabid' => \App\Module::getModuleId($moduleName)])->one();
		$data = App\Json::decode($widgetData['data']);
		$data['module'] = \App\Module::getModuleName($data['relatedmodule']);
		$data['recordId'] = $this->record->getRecord()->getId();
		$widgetData['data'] = \App\Json::encode($data);
		$widget = Vtiger_Widget_Model::getInstanceFromValues($widgetData);
		$chartFilterWidgetModel = Vtiger_ChartFilter_Model::getInstance();
		$chartFilterWidgetModel->setWidgetModel($widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CHART_MODEL', $chartFilterWidgetModel);
		$viewer->assign('CHART_DATA', $chartFilterWidgetModel->getChartData());
		$viewer->view('Detail/Widget/ShowChart.tpl', $moduleName);
	}
}
