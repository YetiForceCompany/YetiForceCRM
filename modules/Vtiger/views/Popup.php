<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_Popup_View extends Vtiger_Footer_View
{
    protected $listViewEntries = false;
    protected $listViewHeaders = false;

    /**
     * Function to check permission.
     *
     * @param \App\Request $request
     *
     * @throws \App\Exceptions\NoPermitted
     * @throws \App\Exceptions\NoPermittedToRecord
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
            throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
        }
        if (!$request->isEmpty('src_record', true) && $request->getByType('src_module', 2) !== 'Users' && !\App\Privilege::isPermitted($request->getByType('src_module', 2), 'DetailView', $request->getInteger('src_record'))) {
            throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
        }
    }

    /**
     * Function returns the module name for which the popup should be initialized.
     *
     * @param \App\Request $request
     *
     * @return string
     */
    public function getModule(\App\Request $request)
    {
        $moduleName = $request->getModule();

        return $moduleName;
    }

    public function process(\App\Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $this->getModule($request);

        $this->initializeListViewContents($request, $viewer);
        $viewer->assign('TRIGGER_EVENT_NAME', $request->getByType('triggerEventName', 2));
        $viewer->view('Popup.tpl', $moduleName);
    }

    public function postProcess(\App\Request $request, $display = true)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $this->getModule($request);
        $viewer->assign('FOOTER_SCRIPTS', $this->getFooterScripts($request));
        $viewer->view('PopupFooter.tpl', $moduleName);
    }

    /**
     * Function to get the list of Script models to be included.
     *
     * @param \App\Request $request
     *
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    public function getFooterScripts(\App\Request $request)
    {
        $headerScriptInstances = parent::getFooterScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = [
            '~libraries/js/timepicker/jquery.timepicker.js',
            '~libraries/clockpicker/dist/jquery-clockpicker.js',
            'libraries.js.jquery_windowmsg',
            '~layouts/resources/BaseList.js',
            'modules.Vtiger.resources.Popup',
            "modules.$moduleName.resources.Popup",
            '~layouts/resources/validator/BaseValidator.js',
            '~layouts/resources/validator/FieldValidator.js',
            "modules.$moduleName.resources.validator.FieldValidator",
        ];
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }
    /*
     * Function to initialize the required data in smarty to display the List View Contents
     */

    public function initializeListViewContents(\App\Request $request, Vtiger_Viewer $viewer)
    {
        $moduleName = $this->getModule($request);
        $pageNumber = $request->getInteger('page');
        $orderBy = $request->getForSql('orderby');
        $sortOrder = $request->getForSql('sortorder');
        $sourceModule = $request->getByType('src_module', 2);
        $sourceField = $request->isEmpty('src_field', true) ? '' : $request->getByType('src_field', 2);
        $currencyId = $request->getInteger('currency_id');
        $relatedParentModule = $request->isEmpty('related_parent_module', true) ? '' : $request->getByType('related_parent_module', 2);
        $relatedParentId = $request->isEmpty('related_parent_id') ? '' : $request->getInteger('related_parent_id');
        $filterFields = $request->getArray('filterFields', 'Alnum');
        $showSwitch = $request->getInteger('showSwitch');
        //To handle special operation when selecting record from Popup
        $getUrl = $request->get('get_url');
        //Check whether the request is in multi select mode
        $multiSelectMode = $request->get('multi_select');
        if (empty($multiSelectMode)) {
            $multiSelectMode = false;
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', empty($pageNumber) ? 1 : $pageNumber);
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
        if ($showSwitch) {
            $viewer->assign('SWITCH', true);
            $viewer->assign('POPUP_SWITCH_ON_TEXT', \App\Language::translateSingularModuleName($relatedParentModule));
        }
        if (!\App\Record::isExists($relatedParentId)) {
            $relatedParentModule = '';
            $relatedParentId = '';
        }
        if (!empty($relatedParentModule) && !empty($relatedParentId)) {
            $parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
            if (!$parentRecordModel->isViewable()) {
                throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
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
            $listViewModel->set('orderby', $orderBy);
            $listViewModel->set('sortorder', $sortOrder);
        }
        if (!empty($filterFields)) {
            $listViewModel->set('filterFields', $filterFields);
        }
        if (!empty($sourceModule)) {
            $listViewModel->set('src_module', $sourceModule);
            $listViewModel->set('src_field', $sourceField);
            $listViewModel->set('src_record', (int) $request->get('src_record'));
        }
        if (!$request->isEmpty('search_key', true) && !$request->isEmpty('search_value', true)) {
            $listViewModel->set('search_key', $request->getByType('search_key', 1));
            $listViewModel->set('search_value', $request->get('search_value'));
            $viewer->assign('SEARCH_KEY', $request->getByType('search_key', 1));
            $viewer->assign('SEARCH_VALUE', $request->get('search_value'));
        }
        $searchParmams = $request->get('search_params');
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
        if (!empty($relatedParentModule) && !empty($relatedParentId)) {
            $this->listViewHeaders = $listViewModel->getHeaders();
            $this->listViewEntries = $listViewModel->getEntries($pagingModel);
            if (count($this->listViewEntries) > 0) {
                $parentRelatedRecords = true;
            }
        } else {
            $this->listViewHeaders = $listViewModel->getListViewHeaders();
            $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
        }

        // If there are no related records with parent module then, we should show all the records
        if (empty($parentRelatedRecords) && !empty($relatedParentModule) && !empty($relatedParentId)) {
            $relatedParentModule = null;
            $relatedParentId = null;
            $listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName, $sourceModule);
            $listViewModel->set('search_params', $transformedSearchParams);
            if (!empty($orderBy)) {
                $listViewModel->set('orderby', $orderBy);
                $listViewModel->set('sortorder', $sortOrder);
            }
            if (!empty($sourceModule)) {
                $listViewModel->set('src_module', $sourceModule);
                $listViewModel->set('src_field', $sourceField);
                $listViewModel->set('src_record', $sourceRecord);
            }
            if (!$request->isEmpty('search_key', true) && !$request->isEmpty('search_value', true)) {
                $listViewModel->set('search_key', $request->getByType('search_key', 1));
                $listViewModel->set('search_value', $request->get('search_value'));
                $viewer->assign('SEARCH_KEY', $request->getByType('search_key', 1));
                $viewer->assign('SEARCH_VALUE', $request->get('search_value'));
            }
            $this->listViewHeaders = $listViewModel->getListViewHeaders();
            $this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
        }
        // End
        $noOfEntries = count($this->listViewEntries);
        if (empty($sortOrder)) {
            $sortOrder = 'ASC';
        }
        if ($sortOrder === 'ASC') {
            $nextSortOrder = 'DESC';
            $sortImage = 'downArrowSmall.png';
        } else {
            $nextSortOrder = 'ASC';
            $sortImage = 'upArrowSmall.png';
        }
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
        $viewer->assign('GETURL', $getUrl);
        $viewer->assign('CURRENCY_ID', $currencyId);
        $viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
        $viewer->assign('PAGING_MODEL', $pagingModel);
        $viewer->assign('PAGE_NUMBER', $pageNumber);
        $viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
        $viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
        $viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);

        if (AppConfig::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
            if (!$this->listViewCount) {
                $this->listViewCount = $listViewModel->getListViewCount();
            }
            $totalCount = $this->listViewCount;
            $pageLimit = $pagingModel->getPageLimit();
            $pageCount = ceil((int) $totalCount / (int) $pageLimit);

            if ($pageCount == 0) {
                $pageCount = 1;
            }
            $viewer->assign('PAGE_COUNT', $pageCount);
            $viewer->assign('LISTVIEW_COUNT', $totalCount);
        }

        $viewer->assign('MULTI_SELECT', $multiSelectMode);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('SEARCH_DETAILS', $searchParmams);
    }

    /**
     * Function to get listView count.
     *
     * @param \App\Request $request
     */
    public function getListViewCount(\App\Request $request)
    {
        $moduleName = $request->getModule();
        $sourceModule = $request->getByType('src_module', 2);
        $sourceField = $request->get('src_field', 1);
        $sourceRecord = $request->isEmpty('src_record', true) ? 0 : $request->getInteger('src_record');
        $orderBy = $request->getForSql('orderby');
        $sortOrder = $request->getForSql('sortorder');
        $currencyId = $request->getInteger('currency_id');
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
        $relatedParentModule = $request->isEmpty('related_parent_module', true) ? '' : $request->getByType('related_parent_module', 2);
        $relatedParentId = $request->isEmpty('related_parent_id') ? '' : $request->getInteger('related_parent_id');
        if (!empty($relatedParentModule) && !empty($relatedParentId)) {
            $parentRecordModel = Vtiger_Record_Model::getInstanceById($relatedParentId, $relatedParentModule);
            $listViewModel = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName);
        } else {
            $listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName, $sourceModule);
        }
        if (!empty($sourceModule)) {
            $listViewModel->set('src_module', $sourceModule);
            $listViewModel->set('src_field', $sourceField);
            $listViewModel->set('src_record', $sourceRecord);
            $listViewModel->set('currency_id', $currencyId);
        }
        if (!empty($orderBy)) {
            $listViewModel->set('orderby', $orderBy);
            $listViewModel->set('sortorder', $sortOrder);
        }
        if ((!empty($searchKey)) && (!empty($searchValue))) {
            $listViewModel->set('search_key', $searchKey);
            $listViewModel->set('search_value', $searchValue);
        }
        if (!empty($relatedParentModule) && !empty($relatedParentId)) {
            $count = $listViewModel->getRelatedEntriesCount();
        } else {
            $count = $listViewModel->getListViewCount();
        }

        return $count;
    }

    /**
     * Function to get the page count for list.
     *
     * @return total number of pages
     */
    public function getPageCount(\App\Request $request)
    {
        $listViewCount = $this->getListViewCount($request);
        $pagingModel = new Vtiger_Paging_Model();
        $pageLimit = $pagingModel->getPageLimit();
        $pageCount = ceil((int) $listViewCount / (int) $pageLimit);

        if ($pageCount == 0) {
            $pageCount = 1;
        }
        $result = [];
        $result['page'] = $pageCount;
        $result['numberOfRecords'] = $listViewCount;
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    protected function showBodyHeader()
    {
        return false;
    }
}
