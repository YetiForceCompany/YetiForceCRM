<?php

/**
 * Settings dav keys view class.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Dav_Keys_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', Settings_Dav_Module_Model::getInstance($qualifiedModuleName));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('USERS', Users_Record_Model::getAll());
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('ENABLEDAV', !\in_array('dav', App\Config::api('enabledServices')));
		$viewer->view('Keys.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard'
		]));
	}
}
