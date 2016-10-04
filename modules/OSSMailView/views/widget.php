<?php

/**
 *
 * @package YetiForce.views
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
Class OSSMailView_widget_View extends Vtiger_Edit_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());
		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}

		$srecord = $request->get('srecord');
		$smodule = $request->get('smodule');

		$recordPermission = Users_Privileges_Model::isPermitted($smodule, 'DetailView', $srecord);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$srecord = $request->get('srecord');
		$smodule = $request->get('smodule');
		$type = $request->get('type');
		$mode = $request->get('mode');
		$record = $request->get('record');
		$mailFilter = $request->get('mailFilter');
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$config = OSSMail_Module_Model::getComposeParameters();
		if ($request->has('limit')) {
			$config['widget_limit'] = $request->get('limit');
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECOLDLIST', $recordModel->$mode($srecord, $smodule, $config, $type, $mailFilter));
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('SMODULENAME', $smodule);
		$viewer->assign('RECORD', $record);
		$viewer->assign('SRECORD', $srecord);
		$viewer->assign('TYPE', $type);
		$viewer->assign('POPUP', $config['popup']);
		$viewer->assign('PRIVILEGESMODEL', Users_Privileges_Model::getCurrentUserPrivilegesModel());
		$viewer->view('widgets.tpl', 'OSSMailView');
	}
}
