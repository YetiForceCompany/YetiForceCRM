<?php
/**
 * Settings proxy config module model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz So³ek <a.solek@yetiforce.com>
 */

class Settings_Proxy_ConfigProxyEdit_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$moduleModel = Settings_Proxy_ConfigModule_Model::getInstance();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODEL', $moduleModel);
		$viewer->view('ConfigProxyEdit.tpl', $qualifiedName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.Settings.Proxy.resources.ConfigEditor",
		]));
	}
}
