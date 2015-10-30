<?php
/* +********************************************************************************
 * Terms & Conditions are placed on the: http://opensaas.pl/ruls.html
 * *******************************************************************************
 *  Module				: OSSMailView
 *  Author				: OpenSaaS Sp. z o.o. 
 *  Help/Email			: bok@opensaas.pl
 *  Website				: www.opensaas.pl
 * *******************************************************************************+ */

class Vtiger_DetailView_Model extends Vtiger_Base_Model
{

	protected $module = false;
	protected $record = false;
	public $widgetsList = array();
	public $widgets = array();

	/**
	 * Function to get Module instance
	 * @return <Vtiger_Module_Model>
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set the module instance
	 * @param <Vtiger_Module_Model> $moduleInstance - module model
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
		$linkTypes = array('DETAILVIEWBASIC', 'DETAILVIEW');
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

		$detailViewLinks = [];
		$adb = PearDatabase::getInstance();
		vimport('~~modules/com_vtiger_workflow/include.inc');
		vimport('~~modules/com_vtiger_workflow/VTEntityMethodManager.inc');
		$wfs = new VTWorkflowManager($adb);
		$workflows = $wfs->getWorkflowsForModule($moduleName, VTWorkflowManager::$TRIGGER);
		if (Users_Privileges_Model::isPermitted($moduleName, 'WorkflowTrigger') && count($workflows) > 0) {
			$detailViewLinks[] = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => '',
				'linkurl' => 'Vtiger_Detail_Js.showWorkflowTriggerView(this)',
				'linkicon' => 'glyphicon glyphicon-plus-sign',
				'linkhint' => 'BTN_WORKFLOW_TRIGGER',
			);
		}
		$lockEdit = Users_Privileges_Model::checkLockEdit($moduleName, $recordId);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId) && !$lockEdit) {
			$detailViewLinks[] = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => '',
				'linkurl' => $recordModel->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil',
				'linkclass' => 'btn',
				'linkhint' => 'BTN_RECORD_EDIT',
			);
		}
		foreach ($detailViewLinks as $detailViewLink) {
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
		}
		$linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);
		//Mark all detail view basic links as detail view links.
		//Since ui will be look ugly if you need many basic links
		$detailViewBasiclinks = $linkModelListDetails['DETAILVIEWBASIC'];
		unset($linkModelListDetails['DETAILVIEWBASIC']);

		if (Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId) && $recordPermissionToEditView) {
			$deletelinkModel = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => sprintf("%s %s", getTranslatedString('LBL_DELETE', $moduleName), vtranslate('SINGLE_' . $moduleName, $moduleName)),
				'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("' . $recordModel->getDeleteUrl() . '")',
				'linkicon' => 'glyphicon glyphicon-trash',
				'title' => vtranslate('LBL_DELETE_RECORD')
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);
		}

		if (Users_Privileges_Model::isPermitted($moduleName, 'DuplicateRecord')) {
			$duplicateLinkModel = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_DUPLICATE',
				'linkurl' => $recordModel->getDuplicateRecordUrl(),
				'linkicon' => 'glyphicon glyphicon-retweet',
				'title' => vtranslate('LBL_DUPLICATE_RECORD')
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);
		}


		if (Users_Privileges_Model::isPermitted($moduleName, 'ExportPdf')) {
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

		if (!empty($detailViewBasiclinks)) {
			foreach ($detailViewBasiclinks as $linkModel) {
				// Remove view history, needed in vtiger5 to see history but not in vtiger6
				if ($linkModel->linklabel == 'View History') {
					continue;
				}
				$linkModelList['DETAILVIEW'][] = $linkModel;
			}
		}

		$relatedLinks = $this->getDetailViewRelatedLinks();

		foreach ($relatedLinks as $relatedLinkEntry) {
			$relatedLink = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
			$linkModelList[$relatedLink->getType()][] = $relatedLink;
		}

		if ($currentUserModel->isAdminUser()) {
			$settingsLinks = $moduleModel->getSettingLinks();
			foreach ($settingsLinks as $settingsLink) {
				$linkModelList['DETAILVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
			}
		}

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
		$relatedLinks = array();

		if ($parentModuleModel->isSummaryViewSupported() && $this->widgetsList) {
			$relatedLinks = array(array(
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => vtranslate('LBL_RECORD_SUMMARY', $moduleName),
					'linkKey' => 'LBL_RECORD_SUMMARY',
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=summary',
					'linkicon' => '',
					'related' => 'Summary'
			));
		}
		//link which shows the summary information(generally detail of record)
		$relatedLinks[] = array(
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => vtranslate('LBL_RECORD_DETAILS', $moduleName),
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
				'related' => 'Comments'
			);
		}

		if ($parentModuleModel->isTrackingEnabled()) {
			$relatedLinks[] = array(
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_UPDATES',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRecentActivities&page=1',
				'linkicon' => '',
				'related' => 'Updates'
			);
		}


		$relationModels = $parentModuleModel->getRelations();

		foreach ($relationModels as $relation) {
			//TODO : Way to get limited information than getting all the information
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
		$Module = $this->getModuleName();
		$Record = $this->getRecord()->getId();
		$ModelWidgets = $moduleModel->getWidgets($Module, $Record);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		foreach ($ModelWidgets as $widgetCol) {
			foreach ($widgetCol as $widget) {
				$widgetName = 'Vtiger_' . $widget['type'] . '_Widget';
				if (class_exists($widgetName)) {
					$this->widgetsList[] = $widget['type'];
					$widgetInstance = new $widgetName($Module, $moduleModel, $Record, $widget);
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
	 * @return <String> - label
	 */
	public function getModuleLabel()
	{
		return $this->getModule()->get('label');
	}

	/**
	 *  Function to get the module name
	 *  @return <String> - name of the module
	 */
	public function getModuleName()
	{
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the instance
	 * @param <String> $moduleName - module name
	 * @param <String> $recordId - record id
	 * @return <Vtiger_DetailView_Model>
	 */
	public static function getInstance($moduleName, $recordId)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DetailView', $moduleName);
		$instance = new $modelClassName();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->trackView();
		return $instance->setModule($moduleModel)->setRecord($recordModel);
	}
}
