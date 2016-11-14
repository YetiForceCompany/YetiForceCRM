<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com.
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_GlobalPermission_FieldPermissions_View extends Settings_Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);

        $modules = Vtiger_Module_Model::getAll();
        $viewer = $this->getViewer($request);
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $roles = Settings_Roles_Record_Model::getAll();
        $users = Users_Record_Model::getAll();
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('MODULES', $modules);
        $viewer->assign('ROLES', $roles);
        $viewer->assign('USER_MODEL', $currentUser);
        $viewer->assign('USERS', $users);
        $viewer->view('FieldPermission.tpl', $qualifiedModuleName);
    }
}
