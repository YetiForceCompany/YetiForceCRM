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
Class OSSMailView_widget_View extends Vtiger_Edit_View {
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
	}
	public function preProcess(Vtiger_Request $request) {
	}
	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$srecord = $request->get('srecord');
		$smodule = $request->get('smodule');
		$type = $request->get('type');
		$mode = $request->get('mode');
		$record = $request->get('record');
		$module = $request->get('module');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel_OSSMailScanner = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
		$recordModel_OSSMailView = Vtiger_Record_Model::getCleanInstance('OSSMailView');
		$Config = $recordModel_OSSMailScanner->getConfig('email_list');
		$email = $recordModel_OSSMailView->findEmail( $srecord, $smodule );
		$InstanceModel = Vtiger_Record_Model::getInstanceById($srecord, $smodule);
		if($smodule == 'HelpDesk'){
			$urldata = '&subject='.$InstanceModel->get('ticket_no').' - '.$InstanceModel->get('ticket_title');
		}elseif($smodule == 'Potentials'){
			$urldata = '&subject='.$InstanceModel->get('potential_no').' - '.$InstanceModel->get('potentialname');
		}elseif($smodule == 'Project'){
			$urldata = '&subject='.$InstanceModel->get('project_no').' - '.$InstanceModel->get('projectname');
		}
		if($email && $smodule != 'Leads' && $smodule != 'Accounts' && $smodule != 'Contacts'){
			$urldata .= '&to='.$email;
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECOLDLIST', $recordModel->$mode($srecord,$smodule,$Config,$type) );
		$viewer->assign('SENDURLDDATA', $urldata );
		$viewer->assign('MODULENAME', $moduleName );
		$viewer->assign('SMODULENAME', $smodule );
		$viewer->assign('RECORD', $record );
		$viewer->assign('SRECORD', $srecord );
        $viewer->assign('TYPE', $type);
        
        $viewer->view('widgets.tpl', 'OSSMailView');
	}
}
?>
