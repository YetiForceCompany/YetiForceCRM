<?php

/**
 * YetiForce registration modal view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class Settings_YetiForce_RegistrationOnlineModal_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->view('RegistrationOnlineModal.tpl', $qualifiedModuleName);
	}
}
