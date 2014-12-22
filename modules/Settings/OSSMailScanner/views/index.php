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
class Settings_OSSMailScanner_index_View extends Settings_Vtiger_Index_View {

    private $prefixesForModules = array('Contacts', 'Leads', 'Potentials', 'Project', 'HelpDesk', 'Accounts', 'Campaigns');

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $OSSMail_active = Vtiger_Functions::getModuleId('OSSMail');
        if ($OSSMail_active) {
            $OSSMail_Record_Model = Vtiger_Record_Model::getCleanInstance('OSSMail');
            $AccountsList = $OSSMail_Record_Model->getAccountsList();
            $mailboxes = $OSSMail_Record_Model->get_default_mailboxes();
        }
        
        $OSSMailScanner_Record_Model = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');

        $identityList = array();
        
        for($i = 0; $i < count($AccountsList); $i++){
           $identityList[$AccountsList[$i]['user_id']] = $OSSMailScanner_Record_Model->getIdentities($AccountsList[$i]['user_id']);
        }
                
        $EmailActionsList = $OSSMailScanner_Record_Model->getEmailActionsList();
        $EmailActionsListName = $OSSMailScanner_Record_Model->getEmailActionsListName($EmailActionsList);
        $ConfigFolderList = $OSSMailScanner_Record_Model->getConfigFolderList();
        $EmailSearch = $OSSMailScanner_Record_Model->getEmailSearch();
        $EmailSearchList = $OSSMailScanner_Record_Model->getEmailSearchList();
        $WidgetCfg = $OSSMailScanner_Record_Model->getConfig(false);
        $supportedModules = Settings_Vtiger_CustomRecordNumberingModule_Model::getSupportedModules();

        foreach ($supportedModules as $supportedModule) {

            if (in_array($supportedModule->name, $this->prefixesForModules)) {
                $moduleModel = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($supportedModule->name);
                $moduleData = $moduleModel->getModuleCustomNumberingData();
                $RecordNumbering[$supportedModule->name] = $moduleData;
            }
        }

        $check_cron = $OSSMailScanner_Record_Model->get_cron();
        $viewer = $this->getViewer($request);
        $viewer->assign('RecordModel', $OSSMailScanner_Record_Model);
        $viewer->assign('ACCOUNTLIST', $AccountsList);
        $viewer->assign('EMAILACTIONSLIST', $EmailActionsList);
        $viewer->assign('EMAILACTIONSLISTNAME', $EmailActionsListName);
        $viewer->assign('FOLDERMAILBOXES', $mailboxes);
        $viewer->assign('CONFIGFOLDERLIST', $ConfigFolderList);
        $viewer->assign('WIDGET_CFG', $WidgetCfg);
        $viewer->assign('EMAILSEARCH', $EmailSearch);
        $viewer->assign('EMAILSEARCHLIST', $EmailSearchList);
        $viewer->assign('RECORDNUMBERING', $RecordNumbering);
        $viewer->assign('ERRORNOMODULE', !$OSSMail_active);
        $viewer->assign('MODULENAME', $moduleName);
        $viewer->assign('IDENTITYLIST', $identityList);
        $viewer->assign('CHECKCRON', $check_cron);
        echo $viewer->view('index.tpl', $moduleName, true);
    }
}
?>