<?php

/**
 * Visit purpose when logging in as an administrator.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Request visit purpose when logging in as an administrator.
 */
class Users_VisitPurpose_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtnIcon = 'far fa-save';

	/** {@inheritdoc} */
	public $modalIcon = 'mdi mdi-eye-settings';

	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_VISIT_PURPOSE_INFO';

	/** {@inheritdoc} */
	public $lockExit = true;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!(\App\Process::hasEvent('showVisitPurpose')) && !(\App\Process::hasEvent('showSuperUserVisitPurpose'))) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('VisitPurpose.tpl', $request->getModule());
	}
}
