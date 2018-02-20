<?php

/**
 * Basic Modal Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Vtiger_BasicModal_View extends Settings_Vtiger_IndexAjax_View
{
	public function preProcess(\App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', 1);
		echo '<div class="modal fade modal' . $moduleName . '' . $viewName . '" id="modal' . $viewName . '"><div class="modal-dialog ' . $this->getSize($request) . '"><div class="modal-content">';
		foreach ($this->getModalCss($request) as $style) {
			echo '<link rel="stylesheet" href="' . $style->getHref() . '">';
		}
	}

	public function postProcess(\App\Request $request, $display = true)
	{
		foreach ($this->getModalScripts($request) as $script) {
			echo '<script type="' . $script->getType() . '" src="' . $script->getSrc() . '"></script>';
		}
		echo '</div></div></div>';
	}

	public function getSize(\App\Request $request)
	{
		return '';
	}

	public function process(\App\Request $request)
	{
		$this->preProcess($request);
		//Content
		$this->postProcess($request);
	}

	public function getModalScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', 1);

		$scripts = [
			"modules.Settings.Vtiger.resources.$viewName",
			"modules.Settings.$moduleName.resources.$viewName",
		];

		$scriptInstances = $this->checkAndConvertJsScripts($scripts);

		return $scriptInstances;
	}

	public function getModalCss(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', 1);
		$cssFileNames = [
			"modules.Settings.$moduleName.$viewName",
			"modules.Settings.Vtiger.$viewName",
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = $cssInstances;

		return $headerCssInstances;
	}
}
