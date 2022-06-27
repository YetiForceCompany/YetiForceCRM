<?php

/**
 * Settings modal for RecordCollector file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <t.poradzewski@yetiforce.com>
 */

/**
 * Settings modal for RecordCollector class.
 */
class Settings_RecordCollector_CollectorConfigModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->qualifiedModuleName = $request->getModule(false);
		$this->pageTitle = \App\Language::translate('LBL_COLLECTOR_CONFIG', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$provider = \App\Map\Address::getInstance($request->getByType('provider'));
		$viewer->assign('CONFIG', Settings_ApiAddress_Module_Model::getInstance('Settings:ApiAddress')->getConfig()[$provider->getName()] ?? []);
		$viewer->assign('PROVIDER', $provider);
		$viewer->assign('CUSTOM_FIELDS', $provider->getCustomFields());
		$viewer->view('ConfigModal.tpl', $this->qualifiedModuleName);
	}
}
