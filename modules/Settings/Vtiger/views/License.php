<?php

/**
 * License view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Vtiger_License_View extends Settings_Vtiger_Index_View
{
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		if (\App\User::getCurrentUserModel()->getDetail('language') === 'pl-PL') {
			$license = file_get_contents('licenses/LicensePL.txt');
		} else {
			$license = file_get_contents('licenses/LicenseEN.txt');
		}
		$viewer->assign('LICENSE', \App\Purifier::encodeHtml($license));
		$viewer->view('License.tpl', $request->getModule(false));
	}
}
