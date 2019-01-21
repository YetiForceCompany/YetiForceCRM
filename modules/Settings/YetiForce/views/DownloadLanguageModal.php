<?php

/**
 * YetiForce registration modal view class file.
 *
 * @package   Settings
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class Settings_Yetiforce_DownloadLanguageModal_View extends \App\Controller\ModalSettings
{
	/**
	 * {@inheritdoc}
	 */
	public $successBtn = '';
	/**
	 * Qualified module name.
	 *
	 * @var string
	 */
	public $qualifiedModuleName = 'Settings:YetiForce';

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$this->pageTitle = '<span class="fas fas fa-download mr-2"></span>' . \App\Language::translate('LBL_DOWNLOAD_LANG', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $this->qualifiedModuleName);
		$viewer->assign('LANGUAGES', \App\Installer\Languages::getAll());
		$viewer->view('DownloadLanguageModal.tpl', $this->qualifiedModuleName);
	}
}
