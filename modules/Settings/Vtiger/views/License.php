<?php

/**
 * Settings OSSMailView index view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Vtiger_License_View extends Settings_Vtiger_Index_View
{

	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userLang = $currentUser->get('language');
		$viewer->assign('USERLANG', $userLang);
		$viewer->view('License.tpl', $qualifiedModuleName);
	}
}
