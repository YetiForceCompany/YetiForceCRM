<?php

/**
 * Records list view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RecordsList_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-fullscreen';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$request->isEmpty('related_parent_module') && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('related_parent_module', 2))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('src_module') && (!$currentUserPrivilegesModel->isAdminUser() && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('src_module', 2)))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('related_parent_id', true) && !\App\Privilege::isPermitted($request->getByType('related_parent_module', 2), 'DetailView', $request->getInteger('related_parent_id'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!$request->isEmpty('src_record', true) && $request->getByType('src_module', 2) !== 'Users' && !\App\Privilege::isPermitted($request->getByType('src_module', 2), 'DetailView', $request->getInteger('src_record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function preProcessTplName(\App\Request $request)
	{
		return 'Modals/RecordsListHeader.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$this->modalIcon = "modCT_{$moduleName} userIcon-{$moduleName}";
		$this->initializeContent($request);
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('ONLY_BODY', $request->getBoolean('onlyBody'));
		if ($request->getMode() === 'getPagination') {
			$viewer->assign('VIEWNAME', 'recordsList');
			$viewer->view('Pagination.tpl', $request->getModule());
		} else {
			$viewer->view('Modals/RecordsList.tpl', $request->getModule());
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(\App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.ListSearch',
			"modules.{$request->getModule()}.resources.ListSearch",
		]));
	}

	/**
	 * Function to initialize the required data to display the record list view contents.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function initializeContent(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule($request);
		$pageNumber = $request->isEmpty('page', true) ? 1 : $request->getInteger('page');
		$totalCount = $request->isEmpty('totalCount', true) ? false : $request->getInteger('totalCount');
		$orderBy = $request->getForSql('orderby');
		$sortOrder = $request->getForSql('sortorder');
		$sourceModule = $request->getByType('src_module', 2);
		$sourceRecord = $request->isEmpty('src_record', true) ? 0 : $request->getInteger('src_record');
		$sourceField = $request->isEmpty('src_field', true) ? '' : $request->getByType('src_field', 2);
		$currencyId = $request->isEmpty('currency_id', true) ? '' : $request->getInteger('currency_id');
		$relatedParentModule = $request->isEmpty('related_parent_module', true) ? '' : $request->getByType('related_parent_module', 2);
		$relatedParentId = $request->isEmpty('related_parent_id') ? '' : $request->getInteger('related_parent_id');
		$filterFields = $request->getArray('filterFields', 'Alnum');
		$showSwitch = $request->getInteger('showSwitch');
		//Check whether the request is in multi select mode
		if ($request->isEmpty('multi_select', true)) {
			$multiSelectMode = false;
		} else {
			$multiSelectMode = $request->getByType('multi_select');
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if (!$request->isEmpty('related_parent_module', true)) {
			$pagingModel->set('noOfEntries', $request->getInteger('noOfEntries'));
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
		if (!$request->isEmpty('process', true) || !$request->isEmpty('link', true)) {
			if (!$request->isEmpty('process', true) && in_array($moduleName, array_keys(\App\ModuleHierarchy::getModulesByLevel(2)))) {
				$processRecord = $request->getInteger('process');
				$processModule = \App\Record::getType($processRecord);
				if (in_array($moduleName, \App\ModuleHierarchy::getChildModules($processModule)) && in_array($processModule, \App\ModuleHierarchy::getModulesMap1M($moduleName))) {
					$showSwitch = true;
					$relatedParentModule = $processModule;
					$relatedParentId = $processRecord;
				} elseif (!$request->isEmpty('link', true)) {
					$linkRecord = $request->getInteger('link');
					$linkModule = \App\Record::getType($linkRecord);
					if (in_array($linkModule, \App\ModuleHierarchy::getModulesMap1M($moduleName))) {
						$showSwitch = true;
						$relatedParentModule = $linkModule;
						$relatedParentId = $linkRecord;
					}
				}
			} elseif (!$request->isEmpty('link', true)) {
				$linkRecord = $request->getInteger('link');
				$linkModule = \App\Record::getType($linkRecord);
				if (in_array($linkModule, \App\ModuleHierarchy::getModulesMap1M($moduleName))) {
					$showSwitch = true;
					$relatedParentModule = $linkModule;
					$relatedParentId = $linkRecord;
				}
			}
		} elseif (!empty($filterFields['parent_id'])) {
			$linkRecord = $filterFields['parent_id'];
			$linkModule = \App\Record::getType($linkRecord);
			if (in_array($linkModule, \App\ModuleHierarchy::getModulesMap1M($moduleName))) {
				$showSwitch = true;
				$relatedParentModule = $linkModule;
				$relatedParentId = $linkRecord;
			}
		}
		if ($relatedParentId && !\App\Record::isExists($relatedParentId)) {
			$relatedParentId = $relatedParentModule = '';
		}
		if (!empty($relatedParentModule) && !empty($relatedParentId)) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
			if (!$parentRecordModel->isViewable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			$listViewModel = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName);
		} else {
			$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName, $sourceModule);
		}
		if (empty($orderBy) && empty($sortOrder)) {
			$moduleInstance = CRMEntity::getInstance($moduleName);
			$orderBy = $moduleInstance->default_order_by;
			$sortOrder = $moduleInstance->default_sort_order;
		}
		if (!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy)->set('sortorder', $sortOrder);
		}
		if (!empty($filterFields)) {
			$listViewModel->set('filterFields', $filterFields);
		}
		if (!empty($sourceModule)) {
			$listViewModel->set('src_module', $sourceModule)->set('src_field', $sourceField)->set('src_record', $sourceRecord);
		}
		if (!$request->isEmpty('search_key', true) && !$request->isEmpty('search_value', true)) {
			$operator = 's';
			if (!$request->isEmpty('operator')) {
				$operator = $request->getByType('operator');
			}
			$searchKey = $request->getByType('search_key', 'Alnum');
			$searchValue = App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $listViewModel->getQueryGenerator()->getModule(), $searchKey, $operator);
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
			$viewer->assign('SEARCH_KEY', $searchKey);
			$viewer->assign('SEARCH_VALUE', $searchValue);
		}
		$searchParmams = App\Condition::validSearchParams($listViewModel->getQueryGenerator()->getModule(), $request->getArray('search_params'));
		if (empty($searchParmams)) {
			$searchParmams = [];
		}
		$transformedSearchParams = $listViewModel->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParmams);
		$listViewModel->set('search_params', $transformedSearchParams);
		//To make smarty to get the details easily accesible
		foreach ($searchParmams as $fieldListGroup) {
			foreach ($fieldListGroup as $fieldSearchInfo) {
				$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
				$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
				$searchParmams[$fieldName] = $fieldSearchInfo;
			}
		}
		if ($currencyId) {
			$listViewModel->set('currency_id', $currencyId);
		}
		if (!empty($relatedParentModule) && !empty($relatedParentId)) {
			$listViewHeaders = $listViewModel->getHeaders();
			$listViewEntries = $listViewModel->getEntries($pagingModel);
			if (count($listViewEntries) > 0) {
				$parentRelatedRecords = true;
			}
		} else {
			$listViewHeaders = $listViewModel->getListViewHeaders();
			$listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		// If there are no related records with parent module then, we should show all the records
		if (empty($parentRelatedRecords) && !empty($relatedParentModule) && !empty($relatedParentId)) {
			$relatedParentId = $relatedParentModule = null;
			$showSwitch = false;
			$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName, $sourceModule);
			$listViewModel->set('search_params', $transformedSearchParams);
			if (!empty($orderBy)) {
				$listViewModel->set('orderby', $orderBy)->set('sortorder', $sortOrder);
			}
			if (!empty($filterFields)) {
				$listViewModel->set('filterFields', $filterFields);
			}
			if (!empty($sourceModule)) {
				$listViewModel->set('src_module', $sourceModule)->set('src_field', $sourceField)->set('src_record', $sourceRecord);
			}
			if (!$request->isEmpty('search_key', true) && !$request->isEmpty('search_value', true)) {
				$operator = 's';
				if (!$request->isEmpty('operator')) {
					$operator = $request->getByType('operator');
				}
				$searchKey = $request->getByType('search_key', 'Alnum');
				$listViewModel->set('search_key', $searchKey);
				$listViewModel->set('search_value', App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $listViewModel->getQueryGenerator()->getModule(), $searchKey, $operator));
			}
			$listViewHeaders = $listViewModel->getListViewHeaders();
			$listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		// End
		$noOfEntries = count($listViewEntries);
		if (empty($sortOrder)) {
			$sortOrder = 'ASC';
		}
		if ($sortOrder === 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'fas fa-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'fas fa-chevron-up';
		}
		if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT') || ($request->getBoolean('showTotalCount') && !$totalCount)) {
			if (method_exists($listViewModel, 'getListViewCount')) {
				$totalCount = $listViewModel->getListViewCount();
			} elseif (method_exists($listViewModel, 'getRelatedEntriesCount')) {
				$totalCount = $listViewModel->getRelatedEntriesCount();
			}
		}
		if (!empty($totalCount)) {
			$pagingModel->set('totalCount', (int) $totalCount);
		}
		if ($showSwitch) {
			$viewer->assign('SWITCH', true)->assign('SWITCH_ON_TEXT', \App\Language::translateSingularModuleName($relatedParentModule));
		}
		$viewer->assign('LISTVIEW_COUNT', (int) $totalCount);
		$viewer->assign('PAGE_COUNT', $pagingModel->getPageCount());
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('START_PAGIN_FROM', $pagingModel->getStartPagingFrom());
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RELATED_MODULE', $moduleName);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SOURCE_FIELD', $sourceField);
		$viewer->assign('SOURCE_RECORD', $sourceRecord);
		$viewer->assign('RELATED_PARENT_MODULE', $relatedParentModule);
		$viewer->assign('RELATED_PARENT_ID', $relatedParentId);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('CURRENCY_ID', $currencyId);
		$viewer->assign('FILTER_FIELDS', $filterFields);
		$viewer->assign('ADDITIONAL_INFORMATIONS', $request->getBoolean('additionalInformations'));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('LISTVIEW_HEADERS', $listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $listViewEntries);
		$viewer->assign('MULTI_SELECT', $multiSelectMode);
		$viewer->assign('SEARCH_DETAILS', $searchParmams);
	}
}
