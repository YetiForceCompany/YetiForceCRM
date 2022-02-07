<?php

/**
 * Visit purpose when logging in as an administrator.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Request visit purpose when logging in as an administrator.
 */
class Users_YetiForce_View extends \App\Controller\Modal
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-radiation-alt';

	/** {@inheritdoc} */
	public $modalId = 'YetiForceAlert';

	/** {@inheritdoc} */
	public $successBtn = '';

	/** {@inheritdoc} */
	public $dangerBtn = '';

	/** {@inheritdoc} */
	public $lockExit = true;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
	}

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('registration');
		$this->exposeMethod('shop');
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$title = 'registration' === $request->getMode() ? 'LBL_YETIFORCE_REGISTRATION' : 'LBL_YETIFORCE_SHOP';
		return \App\Language::translate($title, $request->getModule());
	}

	/**
	 * Registration modal process.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function registration(App\Request $request): void
	{
		$viewer = $this->getViewer($request);
		$this->viewer->assign('MODE', $request->getMode());
		$viewer->view('Modals/YetiForceAlert.tpl', $request->getModule());
	}

	/**
	 * Shop modal process.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function shop(App\Request $request): void
	{
		$viewer = $this->getViewer($request);
		$this->viewer->assign('MODE', $request->getMode());
		$this->viewer->assign('PRODUCTS', \App\YetiForce\Shop::verify(false, true));
		$viewer->view('Modals/YetiForceAlert.tpl', $request->getModule());
	}
}
