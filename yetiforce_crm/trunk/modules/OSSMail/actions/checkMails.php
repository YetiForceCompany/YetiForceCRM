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
class OSSMail_checkMails_Action extends Vtiger_Action_Controller {
	function checkPermission(Vtiger_Request $request) {
		return;
	}
	public function process(Vtiger_Request $request) {
		$info = OSSMail_Record_Model::getMailBoxmsgInfo();
		if(!$info){
			$output['error'] = true;
		}else{
			$output['Unread'] = $info->Unread;
			$output['html'] = '';
			if($info->Unread > 0){
				$output['html'] = '<a href="index.php?module=OSSMail&view=index" title="'.vtranslate('Unread messages', 'OSSMail').': '.$info->Unread.'"><span class="CountUnread">'.$info->Unread.'</span><span class="InfoBox"><img src="layouts/vlayout/skins/images/mailNotification.png"/></span></a>';
			}else{
				$output['html'] = '<a href="index.php?module=OSSMail&view=index" title="'.vtranslate('Unread messages', 'OSSMail').': 0"><span class="InfoBox"><img src="layouts/vlayout/skins/images/mailNotification.png"/></span></a>';
			}
		}
        $response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
    }
}