<?php

/**
 * YetiForce vulnerabilities view class.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Dependencies_Vulnerabilities_View extends Settings_Vtiger_Index_View
{

	/** {@inheritdoc} */
	public function checkPermission(\App\Request $request)
	{
		parent::checkPermission($request);
		if(!\App\YetiForce\Register::getProduct('YetiForceVulnerabilities')){
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('VULNERABILITIES', (new \App\Security\Dependency())->securityChecker());
		$viewer->view('Vulnerabilities.tpl', $request->getModule(false));
	}
}
