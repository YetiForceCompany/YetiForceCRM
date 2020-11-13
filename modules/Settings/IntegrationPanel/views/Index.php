<?php

/**
 * Settings Integration panel index view file.
 *
 * @package Settings
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Settings Integration panel index view class.
 */
class Settings_IntegrationPanel_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
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
}
