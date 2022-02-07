<?php

/**
 * Tree Category Modal Class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TreeCategoryModal_View extends Vtiger_BasicModal_View
{
	public function checkPermission(App\Request $request)
	{
		$recordId = $request->getInteger('src_record');
		if (!$recordId) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getByType('src_module', 2), 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Function to get size modal window.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getSize(App\Request $request)
	{
		return 'modal-lg';
	}

	public function process(App\Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$srcRecord = $request->getInteger('src_record');
		$srcModule = $request->getByType('src_module', 2);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeCategoryModel = Vtiger_TreeCategoryModal_Model::getInstance($moduleModel);
		$treeCategoryModel->set('srcRecord', $srcRecord);
		$treeCategoryModel->set('srcModule', $srcModule);
		$this->relationType = $treeCategoryModel->getRelationType();
		$viewer->assign('TREE', \App\Json::encode($treeCategoryModel->getTreeData()));
		$viewer->assign('SRC_RECORD', $srcRecord);
		$viewer->assign('SRC_MODULE', $srcModule);
		$viewer->assign('TEMPLATE', $treeCategoryModel->getTemplate());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RELATION_TYPE', $this->relationType);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TreeCategoryModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	public function getModalScripts(App\Request $request)
	{
		$scripts = [
			'~libraries/jstree/dist/jstree.js',
			'~layouts/resources/libraries/jstree.category.js',
			'~layouts/resources/libraries/jstree.checkbox.js'
		];
		if (1 == $this->relationType) {
			$scripts[] = '~layouts/resources/libraries/jstree.edit.js';
		}
		$scripts[] = 'modules.Vtiger.resources.TreeCategoryModal';
		return array_merge($this->checkAndConvertJsScripts($scripts), parent::getModalScripts($request));
	}

	public function getModalCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
		]), parent::getModalCss($request));
	}
}
