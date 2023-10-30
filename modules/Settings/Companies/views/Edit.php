<?php

/**
 * Companies edit view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Companies_Edit_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public $pageTitle = 'LBL_EDIT';

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request): void
	{
		$module = $request->getModule(false);
		$recordModel = Settings_Companies_Record_Model::getInstance();
		$registration = new \App\YetiForce\Register();
		$status = \App\YetiForce\Register::STATUS_MESSAGES[$registration->getStatus(true)];

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $recordModel->getId());
		$viewer->assign('STATUS', \App\Language::translate($status, $module));
		$viewer->assign('IS_REGISTERED', \App\YetiForce\Register::isRegistered());
		$viewer->assign('STATUS_ERROR', $registration->getError());

		if (\App\User::getCurrentUserModel()->isAdmin()) {
			$viewer->assign('EMAIL_URL', Settings_Companies_EmailVerificationModal_View::MODAL_EVENT['url']);
		}
		$viewer->view('EditView.tpl', $module);
	}
}
