<?php

/**
 * Visit purpose when logging in as an administrator.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Users_VisitPurpose_View class.
 */
class Users_VisitPurpose_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $successBtnIcon = 'far fa-save';

	/**
	 * {@inheritdoc}
	 */
	public $modalIcon = 'mdi mdi-eye-settings';

	/**
	 * {@inheritdoc}
	 */
	public $pageTitle = 'LBL_VISIT_PURPOSE_INFO';

	/**
	 * {@inheritdoc}
	 */
	public $lockExit = true;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Session::get('showVisitPurpose') || !App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('VisitPurpose.tpl', $request->getModule());
	}
}
