<?php

/**
 * Records list view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_RecordsList_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-fullscreen';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$request->isEmpty('related_parent_module') && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('related_parent_module', 2))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('src_module') && (!\App\Security\AdminAccess::isPermitted($request->getByType('src_module', 2)) && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('src_module', 2)))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('related_parent_id', true)) {
			$this->relatedParentId = $request->getInteger('related_parent_id');
			$this->relatedParentModule = $request->isEmpty('related_parent_module', true) ? \App\Record::getType($this->relatedParentId) : $request->getByType('related_parent_module', \App\Purifier::ALNUM);
			if (!\App\Privilege::isPermitted($this->relatedParentModule, 'DetailView', $this->relatedParentId)) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
		if (!$request->isEmpty('src_record', true) && !\in_array($request->getByType('src_module', 2), ['Users', 'WebserviceUsers']) && !\App\Privilege::isPermitted($request->getByType('src_module', 2), 'DetailView', $request->getInteger('src_record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	protected function preProcessTplName(App\Request $request)
	{
		return 'Modals/RecordsListHeader.tpl';
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		if ($request->has('modal_params')) {
			$viewer = $this->getViewer($request);
			$viewer->assign('MODAL_PARAMS', $request->getArray('modal_params'));
		}
		$this->moduleName = $request->getModule();
		$this->modalIcon = "modCT_{$this->moduleName} yfm-{$this->moduleName}";
		$this->initializeContent($request);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('ONLY_BODY', $request->getBoolean('onlyBody'));
		if ('getPagination' === $request->getMode()) {
			$viewer->assign('VIEWNAME', 'recordsList');
			$viewer->view('Pagination.tpl', $request->getModule());
		} else {
			$viewer->view('Modals/RecordsList.tpl', $request->getModule());
		}
	}

	/** {@inheritdoc} */
	public function postProcessAjax(App\Request $request)
	{
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.ListSearch',
			"modules.{$request->getModule()}.resources.ListSearch",
		]));
	}

	/** @var bool Show switch */
	public $showSwitch = false;
	/** @var string Switch label */
	public $switchLabel;
	/** @var string Module name */
	public $moduleName;
	/** @var string Source module name */
	public $sourceModule;
	/** @var string Source module field name */
	public $sourceField;
	/** @var int Source record ID */
	public $sourceRecord;
	/** @var int Related parent ID */
	public $relatedParentId;
	/** @var string Related parent module name */
	public $relatedParentModule;
	/** @var int Parent relation ID */
	public $parentRelationId;
	/** @var mixed Record list model */
	public $recordListModel;

	/**
	 * Function to initialize the required data to display the record list view contents.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function initializeContent(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$this->moduleName = $request->getModule();
		$this->sourceModule = $request->getByType('src_module', 2);
		$this->sourceField = $request->isEmpty('src_field', true) ? '' : $request->getByType('src_field', 2);
		$this->sourceRecord = $request->isEmpty('src_record', true) ? 0 : $request->getInteger('src_record');
		if (!isset($this->relatedParentModule)) {
			$this->relatedParentModule = $request->isEmpty('related_parent_module', true) ? '' : $request->getByType('related_parent_module', 2);
		}
		if (!isset($this->relatedParentId)) {
			$this->relatedParentId = $request->isEmpty('related_parent_id') ? '' : $request->getInteger('related_parent_id');
		}
		$filterFields = $request->getArray('filterFields', 'Text');
		$multiSelectMode = $request->has('multi_select') ? $request->getBoolean('multi_select') : false;
		$currencyId = $request->isEmpty('currency_id', true) ? '' : $request->getInteger('currency_id');

		$moduleModel = Vtiger_Module_Model::getInstance($this->moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

		$cvId = $request->getInteger('cvId');
		$pageNumber = $request->isEmpty('page', true) ? 1 : $request->getInteger('page');
		$totalCount = $request->isEmpty('totalCount', true) ? false : $request->getInteger('totalCount');
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!$request->isEmpty('noOfEntries', true)) {
			$pagingModel->set('noOfEntries', $request->getInteger('noOfEntries'));
		}

		$this->setRelatedParent($request);
		$this->setRecordListModel($request);
		$orderBy = $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL);
		if (empty($orderBy)) {
			$moduleInstance = CRMEntity::getInstance($this->moduleName);
			if ($moduleInstance->default_order_by && $moduleInstance->default_sort_order) {
				$orderBy = [];
				foreach ((array) $moduleInstance->default_order_by as $value) {
					$orderBy[$value] = $moduleInstance->default_sort_order;
				}
			}
		}
		if (!empty($orderBy)) {
			$this->recordListModel->set('orderby', $orderBy);
		}
		if (!empty($filterFields)) {
			$this->recordListModel->set('filterFields', $filterFields);
		}
		if (!empty($this->sourceModule)) {
			$this->recordListModel->set('src_module', $this->sourceModule)->set('src_field', $this->sourceField)->set('src_record', $this->sourceRecord);
		}
		if (!$request->isEmpty('search_key', true) && !$request->isEmpty('search_value', true)) {
			$operator = 's';
			if (!$request->isEmpty('operator')) {
				$operator = $request->getByType('operator');
			}
			$searchKey = $request->getByType('search_key', 'Alnum');
			$searchValue = App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $this->recordListModel->getQueryGenerator()->getModule(), $searchKey, $operator);
			$this->recordListModel->set('operator', $operator);
			$this->recordListModel->set('search_key', $searchKey);
			$this->recordListModel->set('search_value', $searchValue);
			$viewer->assign('SEARCH_KEY', $searchKey);
			$viewer->assign('SEARCH_VALUE', $searchValue);
			$viewer->assign('ALPHABET_VALUE', $searchValue);
		}
		$searchParams = App\Condition::validSearchParams($this->recordListModel->getQueryGenerator()->getModule(), $request->getArray('search_params'));
		if (empty($searchParams)) {
			$searchParamsRaw = $searchParams = [];
		}
		$transformedSearchParams = $this->recordListModel->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
		$this->recordListModel->set('search_params', $transformedSearchParams);
		$this->recordListModel->loadSearchLockedFields($request);
		//To make smarty to get the details easily accesible
		foreach ($request->getArray('search_params') as $fieldListGroup) {
			$searchParamsRaw[] = $fieldListGroup;
			foreach ($fieldListGroup as $fieldSearchInfo) {
				$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
				$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
				$fieldSearchInfo['specialOption'] = \in_array($fieldSearchInfo[1], ['ch', 'kh']) ? true : '';
				$searchParams[$fieldName] = $fieldSearchInfo;
			}
		}
		if ($currencyId) {
			$this->recordListModel->set('currency_id', $currencyId);
		}
		if (!empty($this->relatedParentModule) && !empty($this->relatedParentId)) {
			$listViewHeaders = $this->recordListModel->getHeaders();
			$listViewEntries = $this->recordListModel->getEntries($pagingModel);
		} else {
			$listViewHeaders = $this->recordListModel->getListViewHeaders();
			$listViewEntries = $this->recordListModel->getListViewEntries($pagingModel);
		}
		if (App\Config::performance('LISTVIEW_COMPUTE_PAGE_COUNT') || ($request->getBoolean('showTotalCount') && !$totalCount)) {
			if (method_exists($this->recordListModel, 'getListViewCount')) {
				$totalCount = $this->recordListModel->getListViewCount();
			} elseif (method_exists($this->recordListModel, 'getRelatedEntriesCount')) {
				$totalCount = $this->recordListModel->getRelatedEntriesCount();
			}
		}
		if (!empty($totalCount)) {
			$pagingModel->set('totalCount', (int) $totalCount);
		}
		if ($this->showSwitch) {
			$viewer->assign('SWITCH', true)
				->assign('SWITCH_ON_TEXT', $this->switchLabel ? \App\Language::translate($this->switchLabel, $this->moduleName) : \App\Language::translateSingularModuleName($this->relatedParentModule));
		}
		$viewer->assign('LISTVIEW_COUNT', (int) $totalCount);
		$viewer->assign('PAGE_COUNT', $pagingModel->getPageCount());
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $pagingModel->getStartPagingFrom());
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', \count($listViewEntries));
		$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($this->moduleName));
		$viewer->assign('MODULE', $this->moduleName);
		$viewer->assign('RELATED_MODULE', $this->moduleName);
		$viewer->assign('MODULE_NAME', $this->moduleName);
		$viewer->assign('SOURCE_MODULE', $this->sourceModule);
		$viewer->assign('SOURCE_FIELD', $this->sourceField);
		$viewer->assign('SOURCE_RECORD', $this->sourceRecord);
		$viewer->assign('RELATED_PARENT_MODULE', $this->relatedParentModule);
		$viewer->assign('RELATED_PARENT_ID', $this->relatedParentId);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('CURRENCY_ID', $currencyId);
		$viewer->assign('FILTER_FIELDS', $filterFields);
		$viewer->assign('ADDITIONAL_INFORMATIONS', $request->getBoolean('additionalInformations'));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('LISTVIEW_HEADERS', $listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $listViewEntries);
		$viewer->assign('MULTI_SELECT', $multiSelectMode);
		$viewer->assign('SEARCH_DETAILS', $searchParams);
		$viewer->assign('SEARCH_PARAMS', $searchParamsRaw);
		$viewer->assign('RECORD_SELECTED', $request->getBoolean('record_selected', false));
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($request->getModule()));
		$viewer->assign('LOCKED_FIELDS', $request->isEmpty('lockedFields', true) ? [] : $request->getArray('lockedFields'));
		$viewer->assign('LOCKED_EMPTY_FIELDS', $request->isEmpty('lockedEmptyFields', true) ? [] : $request->getArray('lockedEmptyFields'));
		$viewer->assign('CV_ID', $cvId);
	}

	/**
	 * Set record list model.
	 *
	 * @param App\Request $request
	 */
	public function setRecordListModel(App\Request $request)
	{
		if ($this->relatedParentId && !\App\Record::isExists($this->relatedParentId)) {
			$this->relatedParentId = $this->relatedParentModule = '';
		}
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		if (!empty($this->relatedParentModule) && !empty($this->relatedParentId)) {
			$this->showSwitch = !$request->has('showSwitch') || $request->getBoolean('showSwitch');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($this->relatedParentId, $this->relatedParentModule);
			if (!$parentRecordModel->isViewable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			$this->recordListModel = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $this->moduleName, $this->parentRelationId, $cvId);
		} else {
			$this->recordListModel = Vtiger_ListView_Model::getInstanceForPopup($this->moduleName, $this->sourceModule, $cvId);
		}
	}

	/**
	 * Set relation parent.
	 *
	 * @param App\Request $request
	 */
	public function setRelatedParent(App\Request $request)
	{
		$filterFields = $request->getArray('filterFields', 'Text');
		if (!($relationId = $request->isEmpty('relationId') ? 0 : $request->getInteger('relationId')) && $this->sourceField) {
			$relationId = \App\Relation::getRelationId($this->moduleName, $this->sourceModule, $this->sourceField);
		}
		$this->parentRelationId = 0;
		if ($relationId && !$request->has('related_parent_id') && $hierarchyData = \App\ModuleHierarchy::getHierarchyByRelation($relationId)) {
			$relatedParentIdTemp = 0;
			foreach ($hierarchyData as $hierarchy) {
				$relField = $hierarchy['rel_field_name'];
				if (!empty($filterFields[$relField])) {
					$relatedParentIdTemp = $filterFields[$relField];
				} elseif (!isset($filterFields[$relField]) && $this->sourceRecord) {
					$srcRecordModel = Vtiger_Record_Model::getInstanceById($this->sourceRecord, $this->sourceModule);
					$relatedParentIdTemp = $srcRecordModel->get($relField);
				}
				if ($relatedParentIdTemp && ($relatedParentModuleTemp = \App\Record::getType($relatedParentIdTemp)) === \App\Module::getModuleName($hierarchy['rel_tabid'])) {
					$this->relatedParentId = $relatedParentIdTemp;
					$this->relatedParentModule = $relatedParentModuleTemp;
					$this->parentRelationId = $hierarchy['dest_relationid'];
					if (!$this->switchLabel && $hierarchy['label']) {
						$this->switchLabel = $hierarchy['label'];
					}
					break;
				}
			}
		} elseif (!$request->isEmpty('process', true) || !$request->isEmpty('link', true)) {
			if (!$request->isEmpty('process', true) && \in_array($this->moduleName, array_keys(\App\ModuleHierarchy::getModulesByLevel(2)))) {
				$processRecord = $request->getInteger('process');
				$processModule = \App\Record::getType($processRecord);
				if (\in_array($this->moduleName, \App\ModuleHierarchy::getChildModules($processModule)) && \in_array($processModule, \App\ModuleHierarchy::getModulesMap1M($this->moduleName))) {
					$this->relatedParentModule = $processModule;
					$this->relatedParentId = $processRecord;
				} elseif (!$request->isEmpty('link', true)) {
					$linkRecord = $request->getInteger('link');
					$linkModule = \App\Record::getType($linkRecord);
					if (\in_array($linkModule, \App\ModuleHierarchy::getModulesMap1M($this->moduleName))) {
						$this->relatedParentModule = $linkModule;
						$this->relatedParentId = $linkRecord;
					}
				}
			} elseif (!$request->isEmpty('link', true)) {
				$linkRecord = $request->getInteger('link');
				$linkModule = \App\Record::getType($linkRecord);
				if (\in_array($linkModule, \App\ModuleHierarchy::getModulesMap1M($this->moduleName))) {
					$this->relatedParentModule = $linkModule;
					$this->relatedParentId = $linkRecord;
				}
			}
		} elseif (!empty($filterFields['parent_id']) && 0 !== $this->relatedParentId) {
			$linkRecord = (int) $filterFields['parent_id'];
			$linkModule = \App\Record::getType($linkRecord);
			if (\in_array($linkModule, \App\ModuleHierarchy::getModulesMap1M($this->moduleName))) {
				$this->relatedParentModule = $linkModule;
				$this->relatedParentId = $linkRecord;
			}
		}
	}
}
