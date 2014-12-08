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
class OSSMail_index_View extends Vtiger_Index_View{

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$url = OSSMail_Record_Model::GetSite_URL().'modules/OSSMail/roundcube/';
		$config = OSSMail_Record_Model::getConfig('email_list');
		if($config['autologon'] == 'true'){
			$account = OSSMail_Record_Model::get_active_email_account();
			if($account){
				require_once 'modules/OSSMail/RoundcubeLogin.class.php';
				$rcl = new RoundcubeLogin($url, false);
				try {
					if (!$rcl->isLoggedIn()){
						$rcl->login($account[0]['username'], $account[0]['password']);
					}
				}
				catch (RoundcubeLoginException $ex) {      
					//$status = "ERROR: ".$ex->getMessage();
				}
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('URL', $url);
		$viewer->view('index.tpl', $moduleName);
	}

}
?>
