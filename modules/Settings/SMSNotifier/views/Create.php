<?php
/**
 * Create SMS provider view file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Create SMS provider view class.
 */
class Settings_SMSNotifier_Create_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_SMSPROVIDER_CREATE';
	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-plus';
	/** {@inheritdoc} */
	public $modalSize = 'modal-md';
	/** {@inheritdoc} */
	public $successBtn = 'LBL_NEXT';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('PROVIDERS', \App\Integrations\SMSProvider::getProviders());
		$viewer->view('Create.tpl', $moduleName);
	}
}
