<?php

/**
 * Speed test view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_ConfReport_Speed_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $successBtn = false;
	/**
	 * {@inheritdoc}
	 */
	public $modalIcon = 'fas fa-stopwatch';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('TESTS', Settings_ConfReport_Module_Model::testSpeed());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('Speed.tpl', $qualifiedModule);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(\App\Request $request)
	{
		return \App\Language::translate('LBL_SERVER_SPEED_TEST', $request->getModule(false));
	}
}
