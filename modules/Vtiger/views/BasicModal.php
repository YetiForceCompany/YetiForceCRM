<?php

/**
 * Basic Modal Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_BasicModal_View extends Vtiger_IndexAjax_View
{
	/**
	 * Additional classes for the modal window.
	 *
	 * @var string
	 */
	protected $modalClass = '';

	public function getSize(\App\Request $request)
	{
		return '';
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', 1);
		echo '<div class="modal fade modal' . $moduleName . '' . $viewName . ' ' . $this->modalClass . '" id="modal' . $viewName . '"><div class="modal-dialog ' . $this->getSize($request) . '"><div class="modal-content">';
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
		return $this->checkAndConvertJsScripts([
			"modules.Vtiger.resources.$viewName",
			"modules.$moduleName.resources.$viewName",
		]);
	}

	public function getModalCss(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', 1);
		return $this->checkAndConvertCssStyles([
			"modules.$moduleName.$viewName",
			"modules.Vtiger.$viewName",
		]);
	}
}
