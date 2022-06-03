<?php

/**
 * Basic modal file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Basic modal class.
 */
class Vtiger_BasicModal_View extends Vtiger_IndexAjax_View
{
	/** @var string Additional classes for the modal window. */
	protected $modalClass = '';

	/**
	 * Function get modal size.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getSize(App\Request $request)
	{
		return '';
	}

	/** {@inheritdoc} */
	public function preProcess(App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', 1);
		echo '<div class="modal fade modal' . $moduleName . '' . $viewName . ' ' . $this->modalClass . '" id="modal' . $viewName . '"><div class="modal-dialog ' . $this->getSize($request) . '"><div class="modal-content">';
		foreach ($this->getModalCss($request) as $style) {
			echo '<link rel="stylesheet" href="' . $style->getHref() . '">';
		}
	}

	/** {@inheritdoc} */
	public function postProcess(App\Request $request, $display = true)
	{
		foreach ($this->getModalScripts($request) as $script) {
			echo '<script type="' . $script->getType() . '" src="' . $script->getSrc() . '"></script>';
		}
		echo '</div></div></div>';
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->preProcess($request);
		//Content
		$this->postProcess($request);
	}

	/**
	 * Get modal scripts files that need to loaded in the modal.
	 *
	 * @param \App\Request $request
	 *
	 * @return \Vtiger_JsScript_Model[]
	 */
	public function getModalScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', 1);
		return $this->checkAndConvertJsScripts([
			"modules.Vtiger.resources.$viewName",
			"modules.$moduleName.resources.$viewName",
		]);
	}

	/**
	 * Function to get the list of Js models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_CssScript_Model[] - List of Vtiger_JsScript_Model instances
	 */
	public function getModalCss(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('view', 1);
		return $this->checkAndConvertCssStyles([
			"modules.$moduleName.$viewName",
			"modules.Vtiger.$viewName",
		]);
	}
}
