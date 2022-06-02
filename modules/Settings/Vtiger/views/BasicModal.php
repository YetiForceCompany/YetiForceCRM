<?php

/**
 * Basic Modal Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Vtiger_BasicModal_View extends Settings_Vtiger_IndexAjax_View
{
	public function preProcess(App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', 1);
		$viewer = $this->getViewer($request);
		$viewer->assign('VIEW', $viewName);
		echo '<div class="modal fade modal' . $moduleName . '' . $viewName . '" id="modal' . $viewName . '"><div class="modal-dialog ' . $this->getSize($request) . '"><div class="modal-content">';
		foreach ($this->getModalCss($request) as $style) {
			echo '<link rel="stylesheet" href="' . $style->getHref() . '">';
		}
	}

	public function postProcess(App\Request $request, $display = true)
	{
		foreach ($this->getModalScripts($request) as $script) {
			echo '<script type="' . $script->getType() . '" src="' . $script->getSrc() . '"></script>';
		}
		echo '</div></div></div>';
	}

	public function getSize(App\Request $request)
	{
		return '';
	}

	public function process(App\Request $request)
	{
		$this->preProcess($request);
		//Content
		$this->postProcess($request);
	}

	public function getModalScripts(App\Request $request)
	{
		$viewName = $request->getByType('view', 1);
		return $this->checkAndConvertJsScripts([
			"modules.Settings.Vtiger.resources.$viewName",
			"modules.Settings.{$request->getModule()}.resources.$viewName",
		]);
	}

	public function getModalCss(App\Request $request)
	{
		$viewName = $request->getByType('view', 1);
		return $this->checkAndConvertCssStyles([
			"modules.Settings.{$request->getModule()}.$viewName",
			"modules.Settings.Vtiger.$viewName",
		]);
	}
}
