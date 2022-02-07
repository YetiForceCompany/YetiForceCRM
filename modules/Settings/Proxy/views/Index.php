<?php
/**
 * Settings proxy index view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Settings proxy index view class.
 */
class Settings_Proxy_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('CONFIG_FIELDS', Settings_Proxy_ConfigForm_Model::getFields($qualifiedModuleName));
		$viewer->assign('CONFIG', App\Config::module('Proxy', null, []));
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
