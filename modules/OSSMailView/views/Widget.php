<?php

/**
 *
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
Class OSSMailView_Widget_View extends Vtiger_Edit_View
{

	public function checkPermission(\App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());
		if (!$permission) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}

		$srecord = $request->getInteger('srecord');
		$smodule = $request->get('smodule');

		$recordPermission = \App\Privilege::isPermitted($smodule, 'DetailView', $srecord);
		if (!$recordPermission) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function preProcess(\App\Request $request, $display = true)
	{

	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$srecord = $request->get('srecord');
		$smodule = $request->get('smodule');
		$type = $request->get('type');
		$mode = $request->getMode();
		$record = $request->get('record');
		$mailFilter = $request->get('mailFilter');
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
