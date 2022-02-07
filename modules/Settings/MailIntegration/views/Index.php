<?php

/**
 * Settings Integration panel index view file.
 *
 * @package Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author	  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Settings Integration panel index view class.
 */
class Settings_MailIntegration_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$activeTab = $request->has('tab') ? $request->getByType('tab') : 'outlook';
		$viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->assign('CONFIG_FIELDS', Settings_MailIntegration_ConfigForm_Model::getFields($qualifiedModuleName));
		$viewer->assign('CONFIG', App\Config::module('MailIntegration', null, []));
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
