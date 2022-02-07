<?php

/**
 * Tree category inventory modal view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Tree category inventory model view class.
 */
class Vtiger_TreeInventoryModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $autoRegisterEvents = false;

	/** {@inheritdoc} */
	public $successBtn = 'LBL_SELECT';

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$this->modalIcon = "yfm-{$request->getModule()}";
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	protected function preProcessTplName(App\Request $request)
	{
		return 'Modals/TreeHeader.tpl';
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule()) || !\App\Privilege::isPermitted($request->getByType('src_module', \App\Purifier::ALNUM))) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeCategoryModel = Vtiger_TreeInventoryModal_Model::getInstance($moduleModel);
		$viewer->assign('TREE', \App\Json::encode($treeCategoryModel->getTreeData()));
		$viewer->assign('IS_MULTIPLE', true);
		$viewer->view('Modals/TreeModal.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		$scripts = [
			'~libraries/jstree/dist/jstree.js',
			'~layouts/resources/libraries/jstree.category.js',
			'~layouts/resources/libraries/jstree.checkbox.js'
		];
		return array_merge($this->checkAndConvertJsScripts($scripts), parent::getModalScripts($request));
	}

	/** {@inheritdoc} */
	public function getModalCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
		]), parent::getModalCss($request));
	}
}
