<?php
/**
 * Abstract modal controller for administration panel file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller;

/**
 * Abstract modal controller for administration panel class.
 */
abstract class ModalSettings extends Modal
{
	use Traits\SettingsPermission;

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
			"modules.Settings.{$request->getModule()}.resources.$viewName",
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
			"modules.Settings.{$request->getModule()}.$viewName",
		]);
	}

	/** {@inheritdoc} */
	public function getPageTitle(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		if (isset($this->pageTitle)) {
			$pageTitle = \App\Language::translate($this->pageTitle, $moduleName);
		} else {
			$pageTitle = \App\Language::translate($request->getModule(), $moduleName);
		}
		return $pageTitle;
	}
}
