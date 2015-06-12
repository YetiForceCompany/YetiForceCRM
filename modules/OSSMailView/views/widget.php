<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

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
		$Config = $recordModel_OSSMailScanner->getConfig('email_list');

		$emailModuleModel = Vtiger_Module_Model::getInstance('OSSMail');
		$config = $emailModuleModel->getComposeParameters();
		$urldata = $emailModuleModel->getComposeUrl($smodule, $srecord, 'Detail', $config['popup']);
				
		$viewer = $this->getViewer($request);
		$viewer->assign('RECOLDLIST', $recordModel->$mode($srecord,$smodule,$Config,$type) );
		$viewer->assign('SENDURLDDATA', $urldata );
		$viewer->assign('MODULENAME', $moduleName );
		$viewer->assign('SMODULENAME', $smodule );
		$viewer->assign('RECORD', $record );
		$viewer->assign('SRECORD', $srecord );
        $viewer->assign('TYPE', $type);
        $viewer->assign('POPUP', $config['popup']);
        $viewer->view('widgets.tpl', 'OSSMailView');
	}
}
