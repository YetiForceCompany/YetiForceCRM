<?php

/**
 * YetiForce product Modal.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Offline registration modal view class.
 */
class Settings_ApiAddress_ApiConfigModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->qualifiedModuleName = $request->getModule(false);
		$provider = $request->getByType('provider', \App\Purifier::STANDARD);
		$this->pageTitle = \App\Language::translate('LBL_PROVIDER_CONFIG', $this->qualifiedModuleName) . ': ' . \App\Language::translate('LBL_PROVIDER_' . strtoupper($provider), $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$provider = \App\Map\Address::getInstance($request->getByType('provider', \App\Purifier::STANDARD));
		$viewer->assign('CONFIG', Settings_ApiAddress_Module_Model::getInstance($this->qualifiedModuleName)->getConfig()[$provider->getName()] ?? []);
		$viewer->assign('PROVIDER', $provider);
		$viewer->assign('MODULE_NAME', $request->getModule(true));
		$viewer->view('ApiConfigModal.tpl', $this->qualifiedModuleName);
	}
}
