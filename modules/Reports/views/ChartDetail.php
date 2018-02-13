<?php
/* * ***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Ondemand Commercial
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Reports_ChartDetail_View extends Vtiger_Index_View
{
    use \App\Controller\ExposeMethod;

    public function checkPermission(\App\Request $request)
    {
        $record = $request->getInteger('record');
        $reportModel = Reports_Record_Model::getCleanInstance($record);

        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($request->getModule()) && !$reportModel->isEditable()) {
            throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
        }
    }

    public function preProcess(\App\Request $request, $display = true)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $recordId = $request->getInteger('record');

        $this->record = $detailViewModel = Reports_DetailView_Model::getInstance($moduleName, $recordId);

        parent::preProcess($request);

        $reportModel = $detailViewModel->getRecord();
        $reportModel->setModule('Reports');

        $primaryModule = $reportModel->getPrimaryModule();
        $secondaryModules = $reportModel->getSecondaryModules();

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
        $permission = $userPrivilegesModel->hasModulePermission($primaryModule);

        if (!$permission) {
            $viewer->assign('MODULE', $primaryModule);
            $viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
            $viewer->view('OperationNotPermitted.tpl', $primaryModule);
            throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
        }

        // Advanced filter conditions
        $viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $reportModel->transformToNewAdvancedFilter());
        $viewer->assign('PRIMARY_MODULE', $primaryModule);

        $recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($reportModel);
        $primaryModuleRecordStructure = $recordStructureInstance->getPrimaryModuleRecordStructure();
        $secondaryModuleRecordStructures = $recordStructureInstance->getSecondaryModuleRecordStructure();

        $viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $primaryModuleRecordStructure);
        $viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $secondaryModuleRecordStructures);

        $secondaryModuleIsCalendar = strpos($secondaryModules, 'Calendar');
        if (($primaryModule == 'Calendar') || ($secondaryModuleIsCalendar !== false)) {
            $advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
        } else {
            $advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
        }
        $viewer->assign('ADVANCED_FILTER_OPTIONS', \App\CustomView::ADVANCED_FILTER_OPTIONS);
        $viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
        $reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);
        $viewer->assign('PRIMARY_MODULE_FIELDS', $reportModel->getPrimaryModuleFieldsForAdvancedReporting());
        $viewer->assign('SECONDARY_MODULE_FIELDS', $reportModel->getSecondaryModuleFieldsForAdvancedReporting());
        $viewer->assign('CALCULATION_FIELDS', $reportModel->getModuleCalculationFieldsForReport());
        $viewer->assign('DATE_FILTERS', Vtiger_AdvancedFilter_Helper::getDateFilter($moduleName));
        $viewer->assign('REPORT_MODEL', $reportModel);
        $viewer->assign('RECORD', $recordId);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('CHART_MODEL', $reportChartModel);

        $viewer->view('ChartReportHeader.tpl', $moduleName);
    }

    public function process(\App\Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
        echo $this->getReport($request);
    }

    public function getReport(\App\Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $record = $request->getInteger('record');

        $reportModel = Reports_Record_Model::getInstanceById($record);
        $reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);
        $secondaryModules = $reportModel->getSecondaryModules();
        if (empty($secondaryModules)) {
            $viewer->assign('CLICK_THROUGH', true);
        }

        $viewer->assign('ADVANCED_FILTERS', $request->get('advanced_filter'));
        $viewer->assign('PRIMARY_MODULE_FIELDS', $reportModel->getPrimaryModuleFields());
        $viewer->assign('SECONDARY_MODULE_FIELDS', $reportModel->getSecondaryModuleFields());
        $viewer->assign('CALCULATION_FIELDS', $reportModel->getModuleCalculationFieldsForReport());

        $data = $reportChartModel->getData();
        $viewer->assign('CHART_TYPE', $reportChartModel->getChartType());
        $viewer->assign('DATA', json_encode($data, JSON_HEX_APOS));
        $viewer->assign('REPORT_MODEL', $reportModel);

        $viewer->assign('RECORD_ID', $record);
        $viewer->assign('REPORT_MODEL', $reportModel);
        $viewer->assign('SECONDARY_MODULES', $secondaryModules);
        $viewer->assign('MODULE', $moduleName);

        $viewer->view('ChartReportContents.tpl', $moduleName);
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
            'modules.Vtiger.resources.Detail',
            'modules.Vtiger.resources.dashboards.Widget',
            "modules.$moduleName.resources.Detail",
            "modules.$moduleName.resources.Edit",
            "modules.$moduleName.resources.Edit3",
            "modules.$moduleName.resources.ChartEdit",
            "modules.$moduleName.resources.ChartEdit2",
            "modules.$moduleName.resources.ChartEdit3",
            "modules.$moduleName.resources.ChartDetailView",
            "modules.$moduleName.resources.TypeCharts",
            '~libraries/updated-jqplot/dist/jquery.updated-jqplot.js',
            '~libraries/updated-jqplot/dist/plugins/jqplot.barRenderer.js',
            '~libraries/updated-jqplot/dist/plugins/jqplot.canvasTextRenderer.js',
            '~libraries/updated-jqplot/dist/plugins/jqplot.canvasAxisTickRenderer.js',
            '~libraries/updated-jqplot/dist/plugins/jqplot.categoryAxisRenderer.js',
            '~libraries/updated-jqplot/dist/plugins/jqplot.pointLabels.js',
            '~libraries/updated-jqplot/dist/plugins/jqplot.highlighter.js',
            '~libraries/updated-jqplot/dist/plugins/jqplot.pieRenderer.js',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

    /**
     * Function to get the list of Css models to be included.
     *
     * @param \App\Request $request
     *
     * @return <Array> - List of Vtiger_CssScript_Model instances
     */
    public function getHeaderCss(\App\Request $request)
    {
        $parentHeaderCssScriptInstances = parent::getHeaderCss($request);

        $headerCss = [
            '~libraries/js/jqplot/jquery.jqplot.min.css',
        ];
        $cssScripts = $this->checkAndConvertCssStyles($headerCss);
        $headerCssScriptInstances = array_merge($parentHeaderCssScriptInstances, $cssScripts);

        return $headerCssScriptInstances;
    }
}
