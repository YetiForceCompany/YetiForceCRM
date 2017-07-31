<?php

/**
 * Settings ConfReport index view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_ConfReport_Index_View extends Settings_Vtiger_Index_View
{

	public function process(\App\Request $request)
	{
		\App\Cache::clear();
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModulePermission($qualifiedModuleName)) {
			$viewer->assign('CCURL', 'index.php?module=OSSMail&view=CheckConfig');
		}
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
