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

class Settings_BackUp_SaveFTPConfig_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
        global $adb;
        global $log;
        $ftpServerName = $request->get('ftpservername');
        $ftpLogin = $request->get('ftplogin');
        $ftpPassword = $request->get('ftppassword');
        //$moduleName = $request->getModule();       

        $ftpConnect = ftp_connect($ftpServerName);
        $loginResult = ftp_login($ftpConnect, $ftpLogin, $ftpPassword);   

        if ($loginResult == false) {
            $log->info('FTP connection has failed!');
            $result = array('success' => true, 'fptConnection' => false);
             Settings_BackUp_Module_Model::saveFTPSettings($ftpServerName, $ftpLogin, $ftpPassword, FALSE);
        } else {
            
            $log->info('FTP connection has success!');
            $result = array('success' => true, 'fptConnection' => true);
            Settings_BackUp_Module_Model::saveFTPSettings($ftpServerName, $ftpLogin, $ftpPassword, TRUE);
        }

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

}
