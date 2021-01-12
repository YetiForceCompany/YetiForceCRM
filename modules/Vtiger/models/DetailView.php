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

class Vtiger_DetailView_Model extends \App\Base
{
	protected $module = false;
	protected $record = false;
	public $widgetsList = [];
	public $widgets = [];

	/**
	 * Function to get Module instance.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set the module instance.
	 *
	 * @param Vtiger_Module_Model $moduleInstance - module model
	 *
	 * @return Vtiger_DetailView_Model>
	 */
	public function setModule($moduleInstance)
	{
		$this->module = $moduleInstance;

		return $this;
	}

	/**
	 * Function to get the Record model.
	 *
	 * @return Vtiger_Record_Model
	 */
	public function getRecord()
	{
		return $this->record;
	}

	/**
	 * Function to set the record instance3.
	 *
	 * @param <type> $recordModuleInstance - record model
	 *
	 * @return Vtiger_DetailView_Model
	 */
	public function setRecord($recordModuleInstance)
	{
		$this->record = $recordModuleInstance;

		return $this;
	}

	/**
	 * Function to get the detail view links (links, widgets, button).
	 *
	 * @param array $linkParams array of link models
	 *
	 * @return array
	 */
	public function getDetailViewLinks(array $linkParams): array
	{
		if ($this->has('Links')) {
			return $this->get('Links');
		}
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();
		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();
		$linkModelList = [];
		if ($recordModel->isReadOnly()) {
			if (\Config\Components\InterestsConflict::$isActive && \App\Components\InterestsConflict::getParent($recordId, $moduleName)) {
				$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linkdata' => ['url' => "index.php?module={$moduleName}&view=InterestsConflictModal&mode=unlock&fromView=Detail&record={$recordId}"],
					'linkicon' => 'fas fa-lock-open',
					'linkhint' => 'LBL_INTERESTS_CONFLICT_UNLOCK',
					'linkclass' => 'btn-outline-primary btn-sm js-show-modal',
				]);
			}
		} else {
			$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if (\Config\Components\InterestsConflict::$isActive && \App\Components\InterestsConflict::getParent($recordId, $moduleName)) {
				$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linkdata' => ['url' => "index.php?module={$moduleName}&view=InterestsConflictModal&mode=confirmation&fromView=Detail&record={$recordId}"],
					'linkicon' => 'yfi yfi-confirm-conflict',
					'linkhint' => 'LBL_INTERESTS_CONFLICT_CONFIRMATION',
					'linkclass' => 'btn-outline-primary btn-sm js-show-modal',
				]);
				if ($moduleModel->isPermitted('InterestsConflictUsers')) {
					$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
						'linktype' => 'DETAIL_VIEW_ADDITIONAL',
						'linkdata' => ['url' => "index.php?module={$moduleName}&view=InterestsConflictModal&mode=users&fromView=Detail&record={$recordId}"],
						'linkicon' => 'yfi yfi-conflict-list',
						'linkhint' => 'LBL_INTERESTS_CONFLICT_USERS',
						'linkclass' => 'btn-outline-primary btn-sm',
						'modalView' => true,
					]);
				}
			}
			if ($moduleModel->isPermitted('WorkflowTrigger') && $recordModel->isEditable()) {
				Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
				Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTEntityMethodManager.php');
				$wfs = new VTWorkflowManager();
				$workflows = $wfs->getWorkflowsForModule($moduleName, VTWorkflowManager::$TRIGGER);
				if (\count($workflows) > 0) {
					$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
						'linktype' => 'DETAIL_VIEW_ADDITIONAL',
						'linklabel' => '',
						'linkurl' => 'javascript:Vtiger_Detail_Js.showWorkflowTriggerView(this)',
						'linkicon' => 'fas fa-plus-circle',
						'linkhint' => 'BTN_WORKFLOW_TRIGGER',
						'linkclass' => 'btn-outline-warning btn-sm',
					]);
				}
			}
			if ($moduleModel->isPermitted('RecordMapping')) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $moduleName);
				$mfModel = new $handlerClass();
				if ($mfModel && $mfModel->checkActiveTemplates($recordId, $moduleName, 'Detail')) {
					$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
						'linktype' => 'DETAIL_VIEW_ADDITIONAL',
						'linklabel' => '',
						'linkdata' => ['url' => "index.php?module={$moduleName}&view=GenerateModal&fromview=Detail&record={$recordId}"],
						'linkicon' => 'fas fa-external-link-alt',
						'linkclass' => 'btn js-show-modal btn-outline-dark btn-sm',
						'linkhint' => 'BTN_GENERATE_RECORD',
					]);
				}
			}
			if (App\Config::module('ModTracker', 'WATCHDOG') && $moduleModel->isPermitted('WatchingRecords')) {
				$watchdog = Vtiger_Watchdog_Model::getInstanceById($recordId, $moduleName);
				$class = 'btn-outline-dark btn-sm';
				$iconClass = 'fa-eye-slash';
				if ($watchdog->isWatchingRecord()) {
					$class = 'btn-dark btn-sm';
					$iconClass = 'fa-eye';
				}
				$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => '',
					'linkurl' => 'javascript:Vtiger_Index_Js.changeWatching(this)',
					'linkicon' => 'fas ' . $iconClass,
					'linkhint' => 'BTN_WATCHING_RECORD',
					'linkclass' => $class,
					'linkdata' => ['off' => 'btn-outline-dark', 'on' => 'btn-dark', 'value' => $watchdog->isWatchingRecord() ? 0 : 1, 'record' => $recordId],
				]);
			}
			if ($userPrivilegesModel->hasModulePermission('Notification') && $userPrivilegesModel->hasModuleActionPermission('Notification', 'CreateView')) {
				$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => '',
					'linkurl' => 'javascript:Vtiger_Index_Js.sendNotification(this)',
					'linkicon' => 'fas fa-paper-plane',
					'linkhint' => 'LBL_SEND_NOTIFICATION',
					'linkclass' => 'btn-outline-dark btn-sm',
				]);
			}
			if ($userPrivilegesModel->hasModulePermission('PermissionInspector')) {
				$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linkhint' => 'BTN_PERMISSION_INSPECTOR',
					'linkdata' => ['url' => "index.php?module=PermissionInspector&view=UserListModal&srcModule=$moduleName&srcRecord=$recordId"],
					'linkicon' => 'fas fa-user-secret',
					'linkclass' => 'btn-outline-dark btn-sm',
					'modalView' => true,
				]);
			}
			if ($moduleModel->isPermitted('RecordConventer') && \App\RecordConverter::isAvailable($recordModel, 'Detail')) {
				$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_ADDITIONAL',
					'linklabel' => 'LBL_RECORD_CONVERTER',
					'linkdata' => ['url' => "index.php?module={$moduleModel->getName()}&view=RecordConverter&sourceView=Detail&selected_ids=[{$recordModel->getId()}]"],
					'linkicon' => 'fas fa-exchange-alt',
					'linkclass' => 'btn-outline-dark btn-sm',
					'modalView' => true,
				]);
			}
			if ($fields = App\Field::getQuickChangerFields($moduleModel->getId())) {
				foreach ($fields as $field) {
					if (App\Field::checkQuickChangerConditions($field, $recordModel)) {
						$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
							'linktype' => 'DETAIL_VIEW_BASIC',
							'linklabel' => $field['btn_name'],
							'linkurl' => "javascript:Vtiger_Detail_Js.runRecordChanger({$field['id']})",
							'linkicon' => $field['icon'] ?? 'mdi mdi-nfc-tap',
							'linkhint' => $field['btn_name'],
							'linkclass' => 'btn-sm ' . $field['class'],
						]);
					}
				}
			}
			if ($recordModel->isEditable()) {
				$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_BASIC',
					'linklabel' => 'BTN_RECORD_EDIT',
					'linkurl' => $recordModel->getEditViewUrl(),
					'linkicon' => 'yfi yfi-full-editing-view',
					'linkclass' => 'btn btn-outline-dark btn-sm',
					'linkhint' => 'BTN_RECORD_EDIT',
				]);
			} elseif ($recordModel->isUnlockByFields()) {
				$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_BASIC',
					'linklabel' => 'BTN_RECORD_OPEN',
					'linkdata' => ['url' => 'index.php?module=' . $recordModel->getModuleName() . '&view=RecordUnlock&record=' . $recordModel->getId()],
					'linkicon' => 'fas fa-lock-open',
					'linkclass' => 'js-show-modal btn-outline-dark btn-sm',
					'linkhint' => 'BTN_RECORD_OPEN'
				]);
			}
			$stateColors = App\Config::search('LIST_ENTITY_STATE_COLOR');
			if ($recordModel->privilegeToActivate()) {
				$linkModelList['DETAIL_VIEW_EXTENDED'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_EXTENDED',
					'linklabel' => 'LBL_ACTIVATE_RECORD',
					'title' => \App\Language::translate('LBL_ACTIVATE_RECORD'),
					'linkurl' => 'javascript:app.showConfirmation({type: "href"},this)',
					'linkdata' => [
						'url' => 'index.php?module=' . $recordModel->getModuleName() . '&action=State&state=Active&record=' . $recordModel->getId(),
						'confirm' => \App\Language::translate('LBL_ACTIVATE_RECORD_DESC'),
					],
					'linkicon' => 'fas fa-undo-alt',
					'linkclass' => 'entityStateBtn btn-outline-dark btn-sm',
					'style' => empty($stateColors['Active']) ? '' : "background: {$stateColors['Active']};",
				]);
			}
			if ($recordModel->privilegeToArchive()) {
				$linkModelList['DETAIL_VIEW_EXTENDED'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_EXTENDED',
					'linklabel' => 'LBL_ARCHIVE_RECORD',
					'title' => \App\Language::translate('LBL_ARCHIVE_RECORD'),
					'linkurl' => 'javascript:app.showConfirmation({type: "href"},this)',
					'linkdata' => [
						'url' => 'index.php?module=' . $recordModel->getModuleName() . '&action=State&state=Archived&record=' . $recordModel->getId(),
						'confirm' => \App\Language::translate('LBL_ARCHIVE_RECORD_DESC'),
					],
					'linkicon' => 'fas fa-archive',
					'linkclass' => 'entityStateBtn btn-outline-dark btn-sm',
					'style' => empty($stateColors['Archived']) ? '' : "background: {$stateColors['Archived']};",
				]);
			}
			if ($recordModel->privilegeToMoveToTrash()) {
				$linkModelList['DETAIL_VIEW_EXTENDED'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_EXTENDED',
					'linklabel' => 'LBL_MOVE_TO_TRASH',
					'title' => \App\Language::translate('LBL_MOVE_TO_TRASH'),
					'linkurl' => 'javascript:app.showConfirmation({type: "href"},this)',
					'linkdata' => [
						'url' => 'index.php?module=' . $recordModel->getModuleName() . '&action=State&state=Trash&record=' . $recordModel->getId(),
						'confirm' => \App\Language::translate('LBL_MOVE_TO_TRASH_DESC'),
					],
					'linkicon' => 'fas fa-trash-alt',
					'linkclass' => 'entityStateBtn btn-outline-dark btn-sm',
					'style' => empty($stateColors['Trash']) ? '' : "background: {$stateColors['Trash']};",
				]);
			}
			if ($recordModel->privilegeToDelete()) {
				$linkModelList['DETAIL_VIEW_EXTENDED'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_EXTENDED',
					'linklabel' => 'LBL_DELETE_RECORD_COMPLETELY',
					'title' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY'),
					'linkurl' => 'javascript:app.showConfirmation({type: "href"},this)',
					'linkdata' => [
						'url' => 'index.php?module=' . $recordModel->getModuleName() . '&action=Delete&record=' . $recordModel->getId(),
						'confirm' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_DESC'),
					],
					'linkicon' => 'fas fa-eraser',
					'linkclass' => 'btn-dark btn-sm',
				]);
			}
			if ($moduleModel->isPermitted('DuplicateRecord')) {
				$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_BASIC',
					'linklabel' => 'LBL_DUPLICATE',
					'linkurl' => $recordModel->getDuplicateRecordUrl(),
					'linkicon' => 'fas fa-clone',
					'linkclass' => 'btn-outline-dark btn-sm',
					'title' => \App\Language::translate('LBL_DUPLICATE_RECORD'),
				]);
			}
			if ($moduleModel->isPermitted('ExportPdf')) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
				$pdfModel = new $handlerClass();
				$additionalClass = '';
				if (!$pdfModel->checkActiveTemplates($recordId, $moduleName, 'Detail')) {
					$additionalClass = ' d-none';
				}
				$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_BASIC',
					'linklabel' => \App\Language::translate('LBL_EXPORT_PDF'),
					'dataUrl' => 'index.php?module=' . $moduleName . '&view=PDF&fromview=Detail&record=' . $recordId,
					'linkicon' => 'fas fa-file-pdf',
					'linkclass' => 'btn-outline-dark btn-sm showModal js-pdf' . $additionalClass,
					'title' => \App\Language::translate('LBL_EXPORT_PDF'),
				]);
			}
		}
		$relatedLinks = $this->getDetailViewRelatedLinks();
		foreach ($relatedLinks as &$relatedLinkEntry) {
			$relatedLink = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
			$linkModelList[$relatedLink->getType()][] = $relatedLink;
		}
		$allLinks = Vtiger_Link_Model::getAllByType($moduleModel->getId(), ['DETAIL_VIEW_ADDITIONAL', 'DETAIL_VIEW_BASIC', 'DETAIL_VIEW_HEADER_WIDGET', 'DETAIL_VIEW_EXTENDED', 'DETAILVIEWTAB', 'DETAILVIEWRELATED'], $linkParams);
		if (!empty($allLinks)) {
			foreach ($allLinks as $type => &$allLinksByType) {
				$linkModelList[$type] = $linkModelList[$type] ?? [];
				foreach ($allLinksByType as $linkModel) {
					$linkModelList[$type][] = $linkModel;
				}
			}
		}
		$this->set('Links', $linkModelList);
		return $linkModelList;
	}

	/**
	 * Function to get the detail view related links.
	 *
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$parentModuleModel = $this->getModule();
		$this->getWidgets();
		$relatedLinks = [];
		if (class_exists($parentModuleModel->getName() . '_ProcessWizard_Model') && $recordModel->isEditable()) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_RECORD_PROCESS_WIZARD',
				'linkKey' => 'LBL_RECORD_PROCESS_WIZARD',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=processWizard',
				'linkicon' => '',
				'related' => 'Summary',
			];
		}
		if ($parentModuleModel->isSummaryViewSupported() && $this->widgetsList) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_RECORD_SUMMARY',
				'linkKey' => 'LBL_RECORD_SUMMARY',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=summary',
				'linkicon' => '',
				'related' => 'Summary',
			];
		}
		//link which shows the summary information(generally detail of record)
		$relatedLinks[] = [
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => 'LBL_RECORD_DETAILS',
			'linkKey' => 'LBL_RECORD_DETAILS',
			'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=full',
			'linkicon' => '',
			'related' => 'Details',
		];
		if ($parentModuleModel->isCommentEnabled() && ($modCommentsModel = Vtiger_Module_Model::getInstance('ModComments')) && $modCommentsModel->isPermitted('DetailView')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'ModComments',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showAllComments',
				'linkicon' => '',
				'related' => $modCommentsModel->getName(),
				'countRelated' => App\Config::relation('SHOW_RECORDS_COUNT'),
			];
		}
		if ($parentModuleModel->isTrackingEnabled() && $parentModuleModel->isPermitted('ModTracker')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_UPDATES',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRecentActivities&page=1',
				'linkicon' => '',
				'related' => 'ModTracker',
				'countRelated' => App\Config::module('ModTracker', 'UNREVIEWED_COUNT') && $parentModuleModel->isPermitted('ReviewingUpdates'),
				'badgeClass' => 'bgDanger',
			];
		}
		if (
			\App\User::getCurrentUserId() === \App\User::getCurrentUserRealId() &&
			\App\Module::isModuleActive('Chat') && !\App\RequestUtil::getBrowserInfo()->ie &&
			false !== \App\ModuleHierarchy::getModuleLevel($parentModuleModel->getName())
		) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_CHAT',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showChat',
				'linkicon' => 'fas fa-comments',
			];
		}
		foreach ($parentModuleModel->getRelations() as $relation) {
			if ($relation->isRelatedViewType('RelatedTab')) {
				$relatedLinks[] = [
					'linktype' => 'DETAILVIEWRELATED',
					'linklabel' => $relation->get('label'),
					'linkurl' => $relation->getListUrl($recordModel),
					'linkicon' => '',
					'relatedModuleName' => $relation->get('relatedModuleName'),
					'relationId' => $relation->getId(),
				];
			}
		}
		return $relatedLinks;
	}

	/**
	 * @param type $viewType
	 *
	 * @return type
	 */
	public function getBlocks($viewType)
	{
		$recordModel = $this->getRecord();
		$relatedLinks = [];
		foreach ($this->getModule()->getRelations() as $relation) {
			if ($relation->isRelatedViewType($viewType)) {
				$relatedLinks[] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => $relation->get('label'),
					'linkurl' => $relation->getListUrl($recordModel),
					'linkicon' => '',
					'relatedModuleName' => $relation->get('relatedModuleName'),
					'relationId' => $relation->getId(),
				]);
			}
		}
		return $relatedLinks;
	}

	/**
	 * Function to get the detail view widgets.
	 */
	public function getWidgets()
	{
		if (empty($this->widgetsList)) {
			$moduleModel = $this->getModule();
			$moduleName = $this->getModuleName();
			$recordId = $this->getRecord()->getId();
			$modelWidgets = $moduleModel->getWidgets($moduleName);
			foreach ($modelWidgets as $widgetCol) {
				foreach ($widgetCol as $widget) {
					$widgetName = Vtiger_Loader::getComponentClassName('Widget', $widget['type'], $moduleName);
					if (class_exists($widgetName)) {
						$this->widgetsList[] = $widget['type'];
						$widgetInstance = new $widgetName($moduleName, $moduleModel, $recordId, $widget);
						$widgetObject = $widgetInstance->getWidget();
						if (\count($widgetObject) > 0) {
							$this->widgets[$widgetObject['wcol']][] = $widgetObject;
						}
					}
				}
			}
		}
	}

	/**
	 * Function to get the Quick Links for the Detail view of the module.
	 *
	 * @param <Array> $linkParams
	 *
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams)
	{
		$linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
		$moduleLinks = $this->getModule()->getSideBarLinks($linkTypes);

		$listLinkTypes = ['DETAILVIEWSIDEBARLINK', 'DETAILVIEWSIDEBARWIDGET'];
		$listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

		if (isset($listLinks['DETAILVIEWSIDEBARLINK'])) {
			foreach ($listLinks['DETAILVIEWSIDEBARLINK'] as $link) {
				$link->linkurl = $link->linkurl . '&record=' . $this->getRecord()->getId() . '&source_module=' . $this->getModule()->getName();
				$moduleLinks['SIDEBARLINK'][] = $link;
			}
		}

		if (isset($listLinks['DETAILVIEWSIDEBARWIDGET'])) {
			foreach ($listLinks['DETAILVIEWSIDEBARWIDGET'] as $link) {
				$link->linkurl = $link->linkurl . '&record=' . $this->getRecord()->getId() . '&source_module=' . $this->getModule()->getName();
				$moduleLinks['SIDEBARWIDGET'][] = $link;
			}
		}
		return $moduleLinks;
	}

	/**
	 * Function to get the module label.
	 *
	 * @return string - label
	 */
	public function getModuleLabel()
	{
		return $this->getModule()->get('label');
	}

	/**
	 *  Function to get the module name.
	 *
	 * @return string - name of the module
	 */
	public function getModuleName()
	{
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the instance.
	 *
	 * @param string $moduleName - module name
	 * @param string $recordId   - record id
	 *
	 * @return Vtiger_DetailView_Model
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
				$instance = new $modelClassName();
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
