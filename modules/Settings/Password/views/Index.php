<?php

/**
 * Settings Password index view class.
 *
 * @package Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Password_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$activeTab = 'pass';
		if ($request->has('tab')) {
			$activeTab = $request->getByType('tab');
		}
		$viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('DETAIL', Settings_Password_Record_Model::getUserPassConfig());
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Settings.' . $request->getModule() . '.resources.Password',
		]));
	}
}
