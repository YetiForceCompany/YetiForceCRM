<?php

/**
 * Tree Category Modal Class
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_TreeCategoryModal_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
		}

		if (!Users_Privileges_Model::isPermitted($request->get('src_module'), 'Detail', $request->get('src_record'))) {
			throw new NoPermittedToRecordException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$srcRecord = $request->get('src_record');
		$srcModule = $request->get('src_module');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$treeCategoryModel = Vtiger_TreeCategoryModal_Model::getInstance($moduleModel);
		$treeCategoryModel->set('srcRecord', $srcRecord);
		$treeCategoryModel->set('srcModule', $srcModule);

		$viewer->assign('TREE', Zend_Json::encode($treeCategoryModel->getTreeData()));
		$viewer->assign('SRC_RECORD', $srcRecord);
		$viewer->assign('SRC_MODULE', $srcModule);
		$viewer->assign('TEMPLATE', $treeCategoryModel->getTemplate());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TreeCategoryModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	public function getModalScripts(Vtiger_Request $request)
	{
		$parentScriptInstances = parent::getModalScripts($request);

		$scripts = [
			'~libraries/jquery/jstree/jstree.js',
			'~libraries/jquery/jstree/jstree.category.js',
			'modules.Vtiger.resources.TreeCategoryModal'
		];

		$modalInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($modalInstances, $parentScriptInstances);
		return $scriptInstances;
	}

	public function getModalCss(Vtiger_Request $request)
	{
		$parentCssInstances = parent::getModalCss($request);
		$cssFileNames = [
			'~libraries/jquery/jstree/themes/proton/style.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($modalInstances, $parentCssInstances);
		return $cssInstances;
	}
}
