<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class OSSPasswords_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		parent::process($request);

		$viewer = $this->getViewer($request);
		$viewer->assign('CONFIG_PASS', (new App\Db\Query())->from('vtiger_passwords_config')->one());
		$viewer->assign('VALIDATE_STRINGS', \App\Language::translate('Very Weak', $moduleName) . ',' . \App\Language::translate('Weak', $moduleName) . ',' . \App\Language::translate('Better', $moduleName) . ',' .
			\App\Language::translate('Medium', $moduleName) . ',' . \App\Language::translate('Strong', $moduleName) . ',' . \App\Language::translate('Very Strong', $moduleName));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.{$request->getModule()}.resources.gen_pass",
		]));
	}
}
