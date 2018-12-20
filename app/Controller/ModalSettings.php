<?php
/**
 * Abstract modal controller class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace App\Controller;

/**
 * Class for modals in administration panel.
 */
abstract class ModalSettings extends Modal
{
	/**
	 * Only administrator user can access settings modal.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 *
	 * @return bool
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
		return true;
	}

	/**
	 * Get modal scripts files that need to loaded in the modal.
	 *
	 * @param \App\Request $request
	 *
	 * @return \Vtiger_JsScript_Model[]
	 */
	public function getModalScripts(\App\Request $request)
	{
		$viewName = $request->getByType('view', 2);
		return $this->checkAndConvertJsScripts([
			"modules.Settings.Vtiger.resources.$viewName",
			"modules.Settings.{$request->getModule()}.resources.$viewName"
		]);
	}

	/**
	 * Get modal css files that need to loaded in the modal.
	 *
	 * @param \App\Request $request
	 *
	 * @return \Vtiger_CssScript_Model[]
	 */
	public function getModalCss(\App\Request $request)
	{
		$viewName = $request->getByType('view', 2);
		return $this->checkAndConvertCssStyles([
			"modules.Settings.Vtiger.$viewName",
			"modules.Settings.{$request->getModule()}.$viewName"
		]);
	}
}
