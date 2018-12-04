<?php

/**
 * Icons Modal View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Vtiger_IconsModal_View extends Vtiger_BasicModal_View
{
	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$this->preProcess($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('IconsModal.tpl', $qualifiedModuleName);

		$this->postProcess($request);
	}

	public function getModalScripts(\App\Request $request)
	{
		return $this->checkAndConvertJsScripts([
			'modules.Settings.Vtiger.resources.IconsModal',
		]);
	}
}
