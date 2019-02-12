<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_Widget_View extends Vtiger_Edit_View
{
	public function checkPermission(\App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());
		if (!$permission) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$srecord = $request->getInteger('srecord');
		$smodule = $request->getByType('smodule');

		$recordPermission = \App\Privilege::isPermitted($smodule, 'DetailView', $srecord);
		if (!$recordPermission) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function preProcess(\App\Request $request, $display = true)
	{
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$srecord = $request->getInteger('srecord');
		$smodule = $request->getByType('smodule');
		$type = $request->getByType('type', 2);
		$mode = $request->getMode();
		$record = $request->getInteger('record');
		$mailFilter = $request->getByType('mailFilter', 1);
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$config = OSSMail_Module_Model::getComposeParameters();
		if ($request->has('limit')) {
			$config['widget_limit'] = $request->getInteger('limit');
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
