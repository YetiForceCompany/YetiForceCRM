<?php

/**
 * YetiForce upload list Modal.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */

/**
 * Upload list view class.
 */
class Settings_Wapro_UploadListSynchronizerModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-lg';

	/** {@inheritdoc} */
	public $showFooter = true;

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->qualifiedModuleName = $request->getModule(false);
		$this->pageTitle = \App\Language::translate('LBL_UPLOAD_LIST', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SYNCHRONIZERS', \App\Integrations\Wapro::getAllSynchronizers());
		$viewer->view('ListView/SynchronizerModal.tpl', $this->qualifiedModuleName);
	}
}
