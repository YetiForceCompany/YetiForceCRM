<?php

namespace App\Controller;

/**
 * Abstract modal controller class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class Modal extends View
{
	/**
	 * Modal data.
	 *
	 * @var string[]
	 */
	public $modalData = [];

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$view = $request->getByType('view', 2);
		$this->modalData['view'] = $view;
		$this->modalData['module'] = $moduleName;
		$viewer = $this->getViewer($request);
		$viewer->assign('MODAL_TITLE', $this->getPageTitle($request));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEW', $view);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PARENT_MODULE', $request->getByType('parent', 2));
		$viewer->assign('MODAL_DATA', $this->modalData);
		$viewer->assign('MODAL_SCRIPTS', $this->getModalScripts($request));
		$viewer->assign('MODAL_CSS', $this->getModalCss($request));
		$this->initializeContent($request, $viewer);
		if (!$request->getBoolean('onlyBody')) {
			$viewer->view($this->preProcessTplName($request), $moduleName);
		}
	}

	/**
	 * Pre process template name.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	protected function preProcessTplName(\App\Request $request)
	{
		return 'Modals/Header.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule($request);
		$viewer->assign('SHOW_END_TAGS', true);
		$viewer->assign('HIDE_SAVE_BTN', true);
		$viewer->assign('HIDE_CANCEL_BTN', true);
		if (!$request->getBoolean('onlyBody')) {
			$viewer->view('Modals/Footer.tpl', $moduleName);
		}
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
			"modules.Vtiger.resources.$viewName",
			"modules.{$request->getModule()}.resources.$viewName",
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
			"modules.Vtiger.$viewName",
			"modules.{$request->getModule()}.$viewName"
		]);
	}
}
