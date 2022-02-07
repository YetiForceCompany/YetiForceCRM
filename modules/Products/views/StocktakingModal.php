<?php

/**
 * Modal view file responsible for products stocktaking import.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Modal view class responsible for products stocktaking import.
 */
class Products_StocktakingModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtn = false;
	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-boxes';
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_IMPORT_STOCKTAKING';
	/** {@inheritdoc} */
	public $dangerBtn = 'LBL_CLOSE';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'Import') || !$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Change user password.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('Modals/StocktakingModal.tpl', $moduleName);
	}
}
