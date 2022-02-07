<?php

/**
 * Abstract modal controller file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller;

/**
 * Abstract modal controller class.
 */
abstract class Modal extends View\Base
{
	/**
	 * Page title.
	 *
	 * @var string
	 */
	protected $pageTitle;

	/**
	 * Modal size.
	 *
	 * @var string
	 */
	public $modalSize = 'modal-lg';
	/**
	 * Header class.
	 *
	 * @var string
	 */
	public $headerClass = '';
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
	 * Modal ID.
	 *
	 * @var string
	 */
	public $modalId = '';

	/**
	 * The name of the success button.
	 *
	 * @var string
	 */
	public $successBtn = 'LBL_SAVE';

	/**
	 * The name of the success button icon.
	 *
	 * @var string
	 */
	public $successBtnIcon = 'fas fa-check';

	/**
	 * The name of the danger button.
	 *
	 * @var string
	 */
	public $dangerBtn = 'LBL_CANCEL';

	/**
	 * The name of the footerClass.
	 *
	 * @var string
	 */
	public $footerClass = '';

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
	 * @var bool Auto register events
	 */
	public $autoRegisterEvents = true;

	/** {@inheritdoc} */
	public function preProcessAjax(\App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$view = $request->getByType('view', 2);
		if ($this->modalId) {
			$this->modalData['modalid'] = $this->modalId;
		}
		$this->modalData['view'] = $view;
		$this->modalData['module'] = $moduleName;
		if ($request->has('mode')) {
			$this->modalData['mode'] = $request->getByType('mode', 'Alnum');
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODAL_TITLE', $this->getPageTitle($request));
		$viewer->assign('MODAL_ID', $this->modalId);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEW', $view);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LOCK_EXIT', $this->lockExit);
		$viewer->assign('PARENT_MODULE', $request->getByType('parent', 2));
		$viewer->assign('MODAL_VIEW', $this);
		$viewer->assign('MODAL_SCRIPTS', $this->getModalScripts($request));
		$viewer->assign('MODAL_CSS', $this->getModalCss($request));
		$viewer->assign('REGISTER_EVENTS', $this->autoRegisterEvents);
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

	/** {@inheritdoc} */
	public function postProcessAjax(\App\Request $request)
	{
		if ($this->showFooter()) {
			$viewer = $this->getViewer($request);
			$viewer->assign('BTN_SUCCESS', $this->successBtn);
			$viewer->assign('BTN_SUCCESS_ICON', $this->successBtnIcon);
			$viewer->assign('BTN_DANGER', $this->dangerBtn);
			$viewer->assign('FOOTER_CLASS', $this->footerClass);
			$viewer->view('Modals/Footer.tpl', $request->getModule());
		}
	}

	/** {@inheritdoc} */
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
			"modules.{$request->getModule()}.$viewName",
		]);
	}

	/** {@inheritdoc} */
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
