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
        $adb = PearDatabase::getInstance();
        global $log;
		$log->debug('Settings_BackUp_SaveFTPConfig_Action: process started');
		$ftpServerName = mysql_real_escape_string($request->get('ftpservername'));
		$ftpLogin = mysql_real_escape_string($request->get('ftplogin'));
		$ftpPassword = mysql_real_escape_string($request->get('ftppassword'));
		$ftpPort = mysql_real_escape_string($request->get('ftpport'));
		$ftpPath = mysql_real_escape_string($request->get('ftppath'));
		$ftpActive = $request->get('ftpactive');
		if('true' == $ftpActive)
			$ftpActive = TRUE;
		else
			$ftpActive = FALSE;
	
		if('' != $ftpPort){
			$ftpConnect = @ftp_connect($ftpServerName, $ftpPort);
		}
		else{
			$ftpConnect = @ftp_connect($ftpServerName);
			$ftpPort = NULL;
		}

		if(!$ftpConnect){
			$result = array('success' => true, 'fptConnection' => false, 'message' => 'JS_HOST_NOT_CORRECT');
		}else{
			$loginResult = @ftp_login($ftpConnect, $ftpLogin, $ftpPassword); 
			if(FALSE == $loginResult) {
				$log->debug('FTP connection has failed!');
				$result = array('success' => true, 'fptConnection' => false, 'message' => 'JS_CONNECTION_FAIL');
			} else {
				$log->debug('FTP connection has success!');
				$result = array('success' => true, 'fptConnection' => true, 'message' => 'JS_SAVE_CHANGES');
				Settings_BackUp_Module_Model::saveFTPSettings($ftpServerName, $ftpLogin, $ftpPassword, TRUE, $ftpPort, $ftpActive, $ftpPath);
			}
		} 
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

}
