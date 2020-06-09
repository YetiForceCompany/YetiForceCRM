<?php

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
