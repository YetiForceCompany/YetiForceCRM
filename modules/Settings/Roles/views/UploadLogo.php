<?php
/**
 * Upload logo View Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

/**
 * Upload logo View Class.
 */
class Settings_Roles_UploadLogo_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-md';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('UploadLogo.tpl', $request->getModule(false));
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_UPLOAD_LOGO', $request->getModule(false));
	}

	/** {@inheritdoc} */
	public function initializeContent(App\Request $request)
	{
	}

	/** {@inheritdoc} */
	public function postProcessAjax(App\Request $request)
	{
	}
}
