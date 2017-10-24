<?php

/**
 * Reset password modal view class
 * @package YetiForce.Modal
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_ResetPasswordModal_View extends Vtiger_BasicModal_View
{

	/**
	 * {@inheritDoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser() === true || (AppConfig::security('SHOW_MY_PREFERENCES') && (int) $currentUserModel->get('id') === $request->getInteger('record'))) {
			return true;
		}
		throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $request->getInteger('record'));
		$viewer->assign('ACTIVE_SMTP', App\Mail::getDefaultSmtp());
		$this->preProcess($request);
		$viewer->view('ResetPasswordModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
