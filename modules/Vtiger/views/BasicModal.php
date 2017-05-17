<?php

/**
 * Basic Modal Class
 * @package YetiForce.Modal
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_BasicModal_View extends Vtiger_IndexAjax_View
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function getSize(\App\Request $request)
	{
		return '';
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		$moduleName = $request->getModule();
		$viewName = $request->get('view');
		echo '<div class="modal fade modal' . $moduleName . '' . $viewName . '" id="modal' . $viewName . '"><div class="modal-dialog ' . $this->getSize($request) . '"><div class="modal-content">';
		foreach ($this->getModalCss($request) as $style) {
			echo '<link rel="stylesheet" href="' . $style->getHref() . '">';
		}
	}

	public function postProcess(\App\Request $request)
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
		$viewName = $request->get('view');

		$scripts = array(
			"modules.Vtiger.resources.$viewName",
			"modules.$moduleName.resources.$viewName"
		);

		$scriptInstances = $this->checkAndConvertJsScripts($scripts);
		return $scriptInstances;
	}

	public function getModalCss(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->get('view');
		$cssFileNames = [
			"modules.$moduleName.$viewName",
			"modules.Vtiger.$viewName",
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = $cssInstances;
		return $headerCssInstances;
	}
}
