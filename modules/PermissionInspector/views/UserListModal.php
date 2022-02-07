<?php

/**
 * User list modal class.
 *
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PermissionInspector_UserListModal_View extends Vtiger_BasicModal_View
{
	/** {@inheritdoc} */
	public function getSize(App\Request $request)
	{
		return 'modal-lg c-modal--fit-lg';
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule();
		$srcModule = $request->getByType('srcModule', 'Alnum');
		$unreviewedChanges = [];
		if ($request->has('srcRecord')) {
			$srcRecordId = $request->getInteger('srcRecord');
		} else {
			$srcRecordId = 0;
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$moduleModel->set('sourceModule', $srcModule);
		$moduleModel->set('sourceRecord', $srcRecordId);
		$actions = ['DetailView', 'CreateView', 'EditView', 'Delete'];
		$moduleModel->set('actions', $actions);

		$userPermission = $moduleModel->getUsersPermission();
		if ($srcRecordId) {
			$watchdog = \App\Privilege::isPermitted($srcModule, 'WatchingRecords');
			if (App\Config::module('ModTracker', 'UNREVIEWED_COUNT')) {
				foreach ($userPermission as $userId => $permission) {
					$unreviewedChanges[$userId] = current(ModTracker_Record_Model::getUnreviewed($srcRecordId, $userId));
				}
			}
		} else {
			$watchdog = \App\Privilege::isPermitted($srcModule, 'WatchingModule');
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('ACTIONS', $actions);
		$viewer->assign('SRC_RECORD_ID', $srcRecordId);
		$viewer->assign('USERS_PERMISSION', $userPermission);
		$viewer->assign('UNREVIEWED_CHANGES', $unreviewedChanges);
		$viewer->assign('WATCHDOG', $watchdog);
		$viewer->view('UserListModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
			'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
			'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js'
		]));
	}

	/** {@inheritdoc} */
	public function getModalCss(App\Request $request)
	{
		return array_merge(parent::getModalCss($request), $this->checkAndConvertCssStyles([
			'~libraries/datatables.net-bs4/css/dataTables.bootstrap4.css',
			'~libraries/datatables.net-responsive-bs4/css/responsive.bootstrap4.css'
		]));
	}
}
