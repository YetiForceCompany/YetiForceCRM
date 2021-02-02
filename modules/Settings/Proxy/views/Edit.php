<?php
/**
 * Settings proxy edit view file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Settings proxy edit view class.
 */
class Settings_Proxy_Edit_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedName = $request->getModule(false);
		$moduleModel = \Settings_Proxy_Module_Model::getInstance();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->view('Edit.tpl', $qualifiedName);
	}
}
