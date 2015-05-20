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
class OSSMail_compose_View extends Vtiger_Index_View{
	public function process(Vtiger_Request $request) {
		$url = '';
		if( $request->get('to') != '' ){
			$to = $request->get('to');
		}
		if( $request->get('subject') != '' ){
			$subject = $request->get('subject');
		}
		if(!empty ($_SESSION['POST']['to'])){
			$to = implode(",", $_SESSION['POST']['to']);
		}
		if(!empty ($_SESSION['POST']['cc'])){
			$cc = implode(",", $_SESSION['POST']['cc']);
		}
		if(!empty ($_SESSION['POST']['bcc'])){
			$bcc = implode(",", $_SESSION['POST']['bcc']);
		}
		if(!empty ($_SESSION['POST']['subject'])){
			$subject = implode(",", $_SESSION['POST']['subject']);
		}
		$mod = $_SESSION['POST']['sourceModule'];
		
		if($mod=='Campaigns'){
			if($to != ''){
				$url .= '&bcc='.$to;
			}
			if($_SESSION['POST']['sourceRecord'] != ''){
				$Record_Model = Vtiger_Record_Model::getInstanceById($_SESSION['POST']['sourceRecord'], $mod);
				$campaign_no = $Record_Model->get('campaign_no');
				$url .= '&subject='.$campaign_no.': '.$Record_Model->get('campaignname');
			}			
		}else{
			if($to != ''){
				$url .= '&to='.$to;
			}
			if($cc != ''){
				$url .= '&cc='.$cc;
			}
			if($bcc != ''){
				$url .= '&bcc='.$bcc;
			}
		}
		if($subject != ''){
			$url .= '&subject='.$subject;
		}
                
		$pdfPath = $request->get('pdf_path');
		if ($pdfPath) {
			$url .= '&pdf_path=' . $pdfPath;
		}
		if($url == '' && $request->get('record') != '' && $request->get('mod') != ''){
			$UrlToCompose = OSSMail_Record_Model::getUrlToCompose( $request->get('mod'), $request->get('record') );
			$url .= $UrlToCompose;
		}
		if($url == '' && $request->get('id') != '' && $request->get('type') != ''){
			$url .= '&crmid=' . (int)$request->get('id') . '&type=' . $request->get('type');
		}
		$mainUrl = OSSMail_Record_Model::GetSite_URL().'modules/OSSMail/roundcube/?_task=mail&_action=compose';
		$url = $mainUrl.$url;
		$config = Settings_Mail_Config_Model::getConfig('autologin');
		if ($config['autologinActive'] == 'true') {
			$account = OSSMail_Autologin_Model::getAutologinUsers();
			if($account){
				$rcUser = (isset($_SESSION['AutoLoginUser']) && array_key_exists($_SESSION['AutoLoginUser'], $account)) ? $account[$_SESSION['AutoLoginUser']] : reset($account);
				require_once 'modules/OSSMail/RoundcubeLogin.class.php';
				$rcl = new RoundcubeLogin($mainUrl, false);
				try {
					if ($rcl->isLoggedIn()) {
						if($rcl->getUsername() != $rcUser['username']){
							$rcl->logout();
							$rcl->login($rcUser['username'], $rcUser['password']);
						}
					}else{
						$rcl->login($rcUser['username'], $rcUser['password']);
					}
				} catch (RoundcubeLoginException $ex) {
					$log = vglobal('log');
					$log->error('OSSMail_index_View|RoundcubeLoginException: '.$ex->getMessage());
				}
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign( "URL", $url);
		$viewer->view('index.tpl', 'OSSMail');
	}
}
