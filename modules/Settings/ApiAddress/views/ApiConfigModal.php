<?php

/**
 * YetiForce product Modal.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

/**
 * Offline registration modal view class.
 */
class Settings_ApiAddress_ApiConfigModal_View extends \App\Controller\ModalSettings
{
	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(App\Request $request)
	{
		$this->qualifiedModuleName = $request->getModule(false);
		$this->pageTitle = \App\Language::translate('LBL_PROVIDER_CONFIG', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$provider = \App\Map\Address::getInstance($request->getByType('provider'));
		$viewer->assign('PROVIDER', $provider);
		$viewer->assign('CUSTOM_FIELDS', $provider->getCustomFields());
		$viewer->view('ApiConfigModal.tpl', $this->qualifiedModuleName);
	}
}
