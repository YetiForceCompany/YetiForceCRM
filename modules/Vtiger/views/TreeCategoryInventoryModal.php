<?php

/**
 * Tree category inventory modal view file.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Tree category inventory model view class.
 */
class Vtiger_TreeCategoryInventoryModal_View extends \App\Controller\Modal
{
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'EditView')) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(App\Request $request)
	{
		$moduleName = $request->getModule();
		$this->modalIcon = "yfm-$moduleName";
		$this->successBtn = 'LBL_SELECT';
		$this->showHeader = true;
		parent::preProcessAjax($request);
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
		$srcModule = $request->getByType('src_module', 2);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeCategoryModel = Vtiger_TreeCategoryInventoryModal_Model::getInstance($moduleModel);
		$viewer->assign('TREE', \App\Json::encode($treeCategoryModel->getTreeData()));
		$viewer->assign('SRC_MODULE', $srcModule);
		$viewer->assign('TEMPLATE', $treeCategoryModel->getTemplate());
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TreeCategoryInventoryModal.tpl', $moduleName);
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
		$scripts = [
			'~libraries/jstree/dist/jstree.js',
			'~layouts/resources/libraries/jstree.category.js',
			'~layouts/resources/libraries/jstree.checkbox.js'
		];
		return array_merge($this->checkAndConvertJsScripts($scripts), parent::getModalScripts($request));
	}

	/**
	 * Get modal css files that need to loaded in the modal.
	 *
	 * @param \App\Request $request
	 *
	 * @return \Vtiger_CssScript_Model[]
	 */
	public function getModalCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
		]), parent::getModalCss($request));
	}
}
