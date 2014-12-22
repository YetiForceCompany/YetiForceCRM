<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class OSSPdf_Condition_View extends Vtiger_Index_View {
    public function process(Vtiger_Request $request) {
		vimport('~~modules/OSSPdf/helpers/Conditions.php');
        $moduleSettingsName = $request->getModule(false);
        $moduleName = $request->getModule();
		$record = $request->get('record');
        $baseModule = Vtiger_Functions::getModuleName( $request->get('base_module') );
        $num = $request->get('num');
        if ("" == $num) {
            $num = 0;
        }
        $viewer = $this->getViewer($request);
        $viewer->assign('NUM', ++$num);
		$viewer->assign('MODULE', $moduleName);
        $viewer->assign('BASE_MODULE', $baseModule);
		$viewer->assign('CONDITION_LIST', Conditions::getListBaseModuleField($baseModule));
        $viewer->assign('FIELD_LIST', Conditions::getListBaseModuleField($baseModule));
        echo $viewer->view('Condition.tpl', $moduleSettingsName, true);
    }
}
