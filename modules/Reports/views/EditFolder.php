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

class Reports_EditFolder_View extends Vtiger_IndexAjax_View
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
        if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
            throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
        }
    }

    public function process(\App\Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $folderId = $request->getByType('folderid', 2);

        if ($folderId) {
            $folderModel = Reports_Folder_Model::getInstanceById($folderId);
        } else {
            $folderModel = Reports_Folder_Model::getInstance();
        }

        $viewer->assign('FOLDER_MODEL', $folderModel);
        $viewer->assign('MODULE', $moduleName);
        $viewer->view('EditFolder.tpl', $moduleName);
    }
}
