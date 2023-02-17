<?php
/**
 * Settings groups index view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Settings groups index view class.
 */
class Settings_Groups_ListTable_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('LINKS', $moduleModel->getLinks());
		$viewer->assign('VIEW', $request->getByType('view', \App\Purifier::STANDARD));
		$viewer->view('ListTable.tpl', $qualifiedModuleName);
	}
}
