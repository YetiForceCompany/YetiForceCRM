<?php

/**
 * Database info view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_ConfReport_DbInfo_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-full';
	/**
	 * {@inheritdoc}
	 */
	public $showFooter = false;
	/**
	 * {@inheritdoc}
	 */
	public $modalIcon = 'fas fa-database';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('DB_INFO', \App\Db::getInstance()->getDbInfo());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('DbInfo.tpl', $qualifiedModule);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_DB_INFO', $request->getModule(false));
	}
}
