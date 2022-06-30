<?php

/**
 * WAPRO ERP list synchronizer modal file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * WAPRO ERP list synchronizer modal class.
 */
class Settings_Wapro_ListSynchronizerModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_SYNCHRONIZER_LIST';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-list';

	/** {@inheritdoc} */
	public $modalSize = 'modal-lg';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('WAPRO_MODEL', new App\Integrations\Wapro($request->getInteger('id')));
		$viewer->view('ListSynchronizerModal.tpl', $request->getModule(false));
	}
}
