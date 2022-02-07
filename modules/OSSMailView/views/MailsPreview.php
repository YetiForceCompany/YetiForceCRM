<?php

/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_MailsPreview_View extends Vtiger_IndexAjax_View
{
	public function checkPermission(App\Request $request)
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

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$srecord = $request->getInteger('srecord');
		$smodule = $request->getByType('smodule');
		$type = $request->get('type');
		$mode = $request->getMode();
		$record = $request->getInteger('record');
		$mailFilter = $request->get('mailFilter');
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$config = OSSMail_Module_Model::getComposeParameters();
		$config['widget_limit'] = '';

		$viewer = $this->getViewer($request);
		$viewer->assign('RECOLDLIST', $recordModel->{$mode}($srecord, $smodule, $config, $type, $mailFilter));
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('SMODULENAME', $smodule);
		$viewer->assign('RECORD', $record);
		$viewer->assign('SRECORD', $srecord);
		$viewer->assign('TYPE', $type);
		$viewer->assign('POPUP', $config['popup']);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('MailsPreview.tpl', 'OSSMailView');
	}
}
