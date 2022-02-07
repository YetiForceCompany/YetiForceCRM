<?php

/**
 * Mail RBL configuration modal view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Mail RBL configuration modal view class.
 */
class Settings_MailRbl_ConfigModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-lg';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-cogs';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->qualifiedModuleName = $request->getModule(false);
		$this->pageTitle = \App\Language::translate('BTN_RBL_CONFIG', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('CONFIG_FIELDS', Settings_MailRbl_ConfigModal_Model::getFields());
		$viewer->assign('CONFIG', App\Config::component('Mail', null, []));
		$viewer->view('ConfigModal.tpl', $this->qualifiedModuleName);
	}
}
