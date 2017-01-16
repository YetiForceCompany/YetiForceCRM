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

class Vtiger_DetailView_Model extends Vtiger_Base_Model
{

	protected $module = false;
	protected $record = false;
	public $widgetsList = [];
	public $widgets = [];

	/**
	 * Function to get Module instance
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set the module instance
	 * @param Vtiger_Module_Model $moduleInstance - module model
	 * @return Vtiger_DetailView_Model>
	 */
	public function setModule($moduleInstance)
	{
		$this->module = $moduleInstance;
		return $this;
	}

	/**
	 * Function to get the Record model
	 * @return <Vtiger_Record_Model>
	 */
	public function getRecord()
	{
		return $this->record;
	}

	/**
	 * Function to set the record instance3
	 * @param <type> $recordModuleInstance - record model
	 * @return Vtiger_DetailView_Model
	 */
	public function setRecord($recordModuleInstance)
	{
		$this->record = $recordModuleInstance;
		return $this;
	}

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams)
	{
		if ($this->has('Links')) {
			return $this->get('Links');
		}
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();
		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();
		$linkModelList = $detailViewLinks = [];

		if ($moduleModel->isPermitted('WorkflowTrigger')) {
			$adb = PearDatabase::getInstance();
			vimport('~~modules/com_vtiger_workflow/include.php');
			vimport('~~modules/com_vtiger_workflow/VTEntityMethodManager.php');
			$wfs = new VTWorkflowManager($adb);
			$workflows = $wfs->getWorkflowsForModule($moduleName, VTWorkflowManager::$TRIGGER);
			if (count($workflows) > 0) {
				$detailViewLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => '',
					'linkurl' => 'javascript:Vtiger_Detail_Js.showWorkflowTriggerView(this)',
					'linkicon' => 'glyphicon glyphicon-plus-sign',
					'linkhint' => 'BTN_WORKFLOW_TRIGGER',
					'linkclass' => 'btn-warning',
				];
			}
		}
		if ($moduleModel->isPermitted('RecordMapping')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleName);
			$mfModel = new $handlerClass();
			if ($mfModel && $mfModel->checkActiveTemplates($recordId, $moduleName, 'Detail')) {
				$detailViewLinks[] = [
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => '',
					'linkdata' => ['url' => 'index.php?module=' . $moduleName . '&view=GenerateModal&fromview=Detail&record=' . $recordId],
					'linkicon' => 'glyphicon glyphicon-new-window',
					'linkclass' => 'btn showModal',
					'linkhint' => 'BTN_GENERATE_RECORD',
				];
			}
		}
		if (AppConfig::module('ModTracker', 'WATCHDOG') && $moduleModel->isPermitted('WatchingRecords')) {
			$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordId, $moduleName);
			$class = 'btn-default';
			if ($watchdog->isWatchingRecord()) {
				$class = 'btn-info';
			}
			$detailViewLinks[] = [
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => '',
				'linkurl' => 'javascript:Vtiger_Index_Js.changeWatching(this)',
				'linkicon' => 'glyphicon glyphicon-eye-open',
				'linkhint' => 'BTN_WATCHING_RECORD',
				'linkclass' => $class,
				'linkdata' => ['off' => 'btn-default', 'on' => 'btn-info', 'value' => $watchdog->isWatchingRecord() ? 0 : 1, 'record' => $recordId],
			];
		}
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModulePermission('Notification') && $userPrivilegesModel->hasModuleActionPermission('Notification', 'CreateView')) {
			$detailViewLinks[] = [
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => '',
				'linkurl' => 'javascript:Vtiger_Index_Js.sendNotification(this)',
				'linkicon' => 'glyphicon glyphicon-send',
				'linkhint' => 'LBL_SEND_NOTIFICATION'
			];
		}
		foreach ($detailViewLinks as $detailViewLink) {
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
		}
		$fieldToupdate = AppConfig::module($moduleName, 'FIELD_TO_UPDATE_BY_BUTTON');
		if ($recordModel->isEditable() && !empty($fieldToupdate)) {
			foreach ($fieldToupdate as $fieldLabel => $fieldName) {
				if (App\Field::getFieldPermission($moduleName, $fieldName)) {
					$editViewLinks = [
						'linktype' => 'DETAILVIEW',
						'linklabel' => '',
						'linkurl' => 'javascript:Vtiger_Detail_Js.updateField(\'' . $fieldName . '\')',
						'linkicon' => 'glyphicon glyphicon-time',
						'linkhint' => App\Language::translate('LBL_UPDATE_FIELD', $moduleName) . ' ' . App\Language::translate($fieldLabel, $moduleName),
						'linkclass' => 'btn-warning',
					];
					$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($editViewLinks);
				}
			}
		}
		if ($recordModel->isEditable()) {
			$editViewLinks = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'BTN_RECORD_EDIT',
				'linkurl' => $recordModel->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil',
				'linkclass' => 'btn',
				'linkhint' => 'BTN_RECORD_EDIT',
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($editViewLinks);
		}

		if (($recordModel->isEditable() && $recordModel->editFieldByModalPermission() ) || $recordModel->editFieldByModalPermission(true)) {
			$fieldByEditData = $recordModel->getFieldToEditByModal();
			$basicActionLink = [
				'linktype' => 'DETAILVIEW',
				'linklabel' => $fieldByEditData['titleTag'],
				'linkurl' => '#',
				'linkdata' => ['url' => $recordModel->getEditFieldByModalUrl()],
				'linkicon' => 'glyphicon ' . $fieldByEditData['iconClass'],
				'linkclass' => 'showModal ' . $fieldByEditData['addClass']
			];
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		if ($recordModel->isDeletable()) {
			$deletelinkModel = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("' . $recordModel->getDeleteUrl() . '")',
				'linkicon' => 'glyphicon glyphicon-trash',
				'title' => vtranslate('LBL_DELETE_RECORD')
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);
		}
		if ($moduleModel->isPermitted('DuplicateRecord')) {
			$duplicateLinkModel = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_DUPLICATE',
				'linkurl' => $recordModel->getDuplicateRecordUrl(),
				'linkicon' => 'glyphicon glyphicon-duplicate',
				'title' => vtranslate('LBL_DUPLICATE_RECORD')
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);
		}
		if (!Settings_ModuleManager_Library_Model::checkLibrary('mPDF') && $moduleModel->isPermitted('ExportPdf')) {
			$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
			$pdfModel = new $handlerClass();
			if ($pdfModel->checkActiveTemplates($recordId, $moduleName, 'Detail')) {
				$pdfLink = [
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => vtranslate('LBL_EXPORT_PDF'),
					'linkurl' => 'javascript:Vtiger_Header_Js.getInstance().showPdfModal("index.php?module=' . $moduleName . '&view=PDF&fromview=Detail&record=' . $recordId . '");',
					'linkicon' => 'glyphicon glyphicon-save-file',
					'title' => vtranslate('LBL_EXPORT_PDF')
				];
				$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($pdfLink);
			}
		}

		$relatedLinks = $this->getDetailViewRelatedLinks();
		foreach ($relatedLinks as &$relatedLinkEntry) {
			$relatedLink = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
			$linkModelList[$relatedLink->getType()][] = $relatedLink;
		}

		$allLinks = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['DETAILVIEWBASIC', 'DETAILVIEW', 'DETAIL_VIEW_HEADER_WIDGET', 'DETAILVIEWTAB'], $linkParams);
		if (!empty($allLinks)) {
			foreach ($allLinks as $type => &$allLinksByType) {
				foreach ($allLinksByType as &$linkModel) {
					$linkModelList[$type][] = $linkModel;
				}
			}
		}
		$this->set('Links', $linkModelList);
		return $linkModelList;
	}

	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$parentModuleModel = $this->getModule();
		$this->getWidgets();
		$relatedLinks = [];

		if ($parentModuleModel->isSummaryViewSupported() && $this->widgetsList) {
			$relatedLinks = array(array(
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => 'LBL_RECORD_SUMMARY',
					'linkKey' => 'LBL_RECORD_SUMMARY',
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=summary',
					'linkicon' => '',
					'related' => 'Summary'
			));
		}
		//link which shows the summary information(generally detail of record)
		$relatedLinks[] = array(
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => 'LBL_RECORD_DETAILS',
			'linkKey' => 'LBL_RECORD_DETAILS',
			'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=full',
			'linkicon' => '',
			'related' => 'Details'
		);

		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		if ($parentModuleModel->isCommentEnabled() && $modCommentsModel->isPermitted('DetailView')) {
			$relatedLinks[] = array(
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'ModComments',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showAllComments',
				'linkicon' => '',
				'related' => 'Comments',
				'countRelated' => AppConfig::relation('SHOW_RECORDS_COUNT')
			);
		}

		if ($parentModuleModel->isTrackingEnabled()) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_UPDATES',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRecentActivities&page=1',
				'linkicon' => '',
				'related' => 'Updates',
				'countRelated' => AppConfig::module('ModTracker', 'UNREVIEWED_COUNT') && $parentModuleModel->isPermitted('ReviewingUpdates'),
				'badgeClass' => 'bgDanger'
			];
		}

		$relationModels = $parentModuleModel->getRelations();

		foreach ($relationModels as $relation) {
			$link = array(
				'linktype' => 'DETAILVIEWRELATED',
				'linklabel' => $relation->get('label'),
				'linkurl' => $relation->getListUrl($recordModel),
				'linkicon' => '',
				'relatedModuleName' => $relation->get('relatedModuleName')
			);
			$relatedLinks[] = $link;
		}

		return $relatedLinks;
	}

	/**
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
	 */
	public function getWidgets()
	{
		if (count($this->widgetsList) > 0)
			return;
		$moduleModel = $this->getModule();
		$module = $this->getModuleName();
		$record = $this->getRecord()->getId();
		$modelWidgets = $moduleModel->getWidgets($module);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		foreach ($modelWidgets as $widgetCol) {
			foreach ($widgetCol as $widget) {
				$widgetName = 'Vtiger_' . $widget['type'] . '_Widget';
				if (class_exists($widgetName)) {
					$this->widgetsList[] = $widget['type'];
					$widgetInstance = new $widgetName($module, $moduleModel, $record, $widget);
					$widgetObject = $widgetInstance->getWidget();
					if (count($widgetObject) > 0) {
						$this->widgets[$widgetObject['wcol']][] = $widgetObject;
					}
				}
			}
		}
	}

	/**
	 * Function to get the Quick Links for the Detail view of the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$moduleLinks = $this->getModule()->getSideBarLinks($linkTypes);

		$listLinkTypes = array('DETAILVIEWSIDEBARLINK', 'DETAILVIEWSIDEBARWIDGET');
		$listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

		if ($listLinks['DETAILVIEWSIDEBARLINK']) {
			foreach ($listLinks['DETAILVIEWSIDEBARLINK'] as $link) {
				$link->linkurl = $link->linkurl . '&record=' . $this->getRecord()->getId() . '&source_module=' . $this->getModule()->getName();
				$moduleLinks['SIDEBARLINK'][] = $link;
			}
		}

		if ($listLinks['DETAILVIEWSIDEBARWIDGET']) {
			foreach ($listLinks['DETAILVIEWSIDEBARWIDGET'] as $link) {
				$link->linkurl = $link->linkurl . '&record=' . $this->getRecord()->getId() . '&source_module=' . $this->getModule()->getName();
				$moduleLinks['SIDEBARWIDGET'][] = $link;
			}
		}

		return $moduleLinks;
	}

	/**
	 * Function to get the module label
	 * @return string - label
	 */
	public function getModuleLabel()
	{
		return $this->getModule()->get('label');
	}

	/**
	 *  Function to get the module name
	 *  @return string - name of the module
	 */
	public function getModuleName()
	{
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the instance
	 * @param string $moduleName - module name
	 * @param string $recordId - record id
	 * @return <Vtiger_DetailView_Model>
	 */
	public static function getInstance($moduleName, $recordId)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DetailView', $moduleName);
		$instance = new $modelClassName();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		return $instance->setModule($moduleModel)->setRecord($recordModel);
	}

	public function getCustomHeaderFields()
	{
		$moduleName = $this->getModuleName();
		$path = 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'headerfields';
		if (!is_dir($path)) {
			return [];
		}
		$headerFields = [];
		foreach (new DirectoryIterator($path) as $fileinfo) {
			if (!$fileinfo->isDot()) {
				$filename = explode('.', $fileinfo->getFilename());
				$name = reset($filename);

				$modelClassName = Vtiger_Loader::getComponentClassName('HeaderField', $name, $moduleName);
				$instance = new $modelClassName;
				if (method_exists($instance, 'checkPermission') && !$instance->checkPermission()) {
					continue;
				}
				if ($result = $instance->process($this)) {
					$headerFields[$name] = $result;
				}
			}
		}
		ksort($headerFields);
		return $headerFields;
	}
}
