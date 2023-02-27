<?php

/**
 * Config edit view file for Settings WooCommerce module.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Config edit view class for Settings WooCommerce module.
 */
class Settings_WooCommerce_EditConfigModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_EDIT_CONFIG';
	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-chart-bar';
	/** {@inheritdoc} */
	public $modalSize = 'modal-lg';
	/** {@inheritdoc} */
	public $successBtn = '';
	/** {@inheritdoc} */
	public $dangerBtn = 'LBL_CLOSE';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		try {
			$connector = (new App\Integrations\WooCommerce($request->getInteger('record')));
			$viewer->assign('INFO', $connector->getInfo());
		} catch (\Throwable $th) {
			$viewer->assign('EXCEPTION', $th);
		}
		$viewer->view('EditConfigModal.tpl', $request->getModule(false));
	}
}
