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
		$main_url = OSSMail_Record_Model::GetSite_URL().'modules/OSSMail/roundcube/?_task=mail&_action=compose'.$url;
		$config = OSSMailScanner_Record_Model::getConfig('email_list');
		if($config['autologon'] == 'true'){
			$account = OSSMail_Record_Model::get_active_email_account();
			if($account){
				require_once 'modules/OSSMail/RoundcubeLogin.class.php';
				$rcl = new RoundcubeLogin($site_URL.'modules/OSSMail/roundcube/', false);
				try {
					if (!$rcl->isLoggedIn()){
						$rcl->login($account[0]['username'], $account[0]['password']);
					}
				}
				catch (RoundcubeLoginException $ex) {   
					global $log;
					$log->debug("OSSMail_compose_View::RoundcubeLoginException Error: ".$ex->getMessage() );
				}
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign( "URL", $main_url);
		$viewer->view('index.tpl', 'OSSMail');
	}
}