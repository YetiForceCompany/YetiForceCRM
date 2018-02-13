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

class Reports_CheckDuplicate_Action extends \App\Controller\Action
{
    /**
     * Function to check permission.
     *
     * @param \App\Request $request
     *
     * @throws \App\Exceptions\NoPermitted
     */
    public function checkPermission(\App\Request $request)
    {
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
            throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
        }
    }

    public function process(\App\Request $request)
    {
        $moduleName = $request->getModule();
        $reportName = $request->get('reportname');

        if (!$request->isEmpty('record', true)) {
            $record = $request->getInteger('record');
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
            $recordModel->set('reportid', $record);
        } else {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
        }
        $recordModel->set('reportname', $reportName);
        $recordModel->set('isDuplicate', $request->getBoolean('isDuplicate'));
        if (!$recordModel->checkDuplicate()) {
            $result = ['success' => false];
        } else {
            $result = ['success' => true, 'message' => \App\Language::translate('LBL_DUPLICATES_EXIST', $moduleName)];
        }
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
