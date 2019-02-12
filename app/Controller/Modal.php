<?php

namespace App\Controller;

/**
 * Abstract modal controller class.
 *
 * @package   Controller
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
abstract class Modal extends View
{
	/**
	 * Modal size.
	 *
	 * @var string
	 */
	public $modalSize = 'modal-lg';
	/**
	 * Modal icon.
	 *
	 * @var string
	 */
	public $modalIcon = '';
	/**
	 * Modal data.
	 *
	 * @var string[]
	 */
	public $modalData = [];
	/**
	 * The name of the success button.
	 *
	 * @var string
	 */
	public $successBtn = 'LBL_SAVE';
	/**
	 * The name of the danger button.
	 *
	 * @var string
	 */
	public $dangerBtn = 'LBL_CANCEL';
	/**
	 * Block the window closing.
	 *
	 * @var bool
	 */
	public $lockExit = false;
	/**
	 * Show modal header.
	 *
	 * @var bool
	 */
	public $showHeader = true;
	/**
	 * Show modal footer.
	 *
	 * @var bool
	 */
	public $showFooter = true;

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
		$viewer->assign('LOCK_EXIT', $this->lockExit);
		$viewer->assign('PARENT_MODULE', $request->getByType('parent', 2));
		$viewer->assign('MODAL_VIEW', $this);
		$viewer->assign('MODAL_SCRIPTS', $this->getModalScripts($request));
		$viewer->assign('MODAL_CSS', $this->getModalCss($request));
		if ($request->getBoolean('onlyBody')) {
			$this->showHeader = false;
			$this->showFooter = false;
		}
		if ($this->showHeader) {
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
		if ($this->showFooter()) {
			$viewer = $this->getViewer($request);
			$viewer->assign('BTN_SUCCESS', $this->successBtn);
			$viewer->assign('BTN_DANGER', $this->dangerBtn);
			$viewer->view('Modals/Footer.tpl', $request->getModule());
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function showFooter()
	{
		return $this->showFooter;
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
			"modules.{$request->getModule()}.resources.$viewName"
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

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if (isset($this->pageTitle)) {
			$pageTitle = \App\Language::translate($this->pageTitle, $moduleName);
		} else {
			$pageTitle = \App\Language::translate($moduleName, $moduleName);
		}
		return $pageTitle;
	}
}
