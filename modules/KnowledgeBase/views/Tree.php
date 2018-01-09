<?php

/**
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_Tree_View extends Vtiger_Index_View
{

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->getByType('view')];
		$linkModels = $moduleModel->getSideBarLinks($linkParams);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUICK_LINKS', $linkModels);
		$viewer->view('TreeHeader.tpl', $moduleName);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$parentScriptInstances = parent::getFooterScripts($request);
		$scripts = [
			'~libraries/js/jstree/jstree.js',
			'~libraries/js/datatables/media/js/jquery.dataTables.js',
			'~libraries/js/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.js',
		];
		$viewInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($parentScriptInstances, $viewInstances);
		return $scriptInstances;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$parentCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/js/jstree/themes/proton/style.css',
			'~libraries/js/datatables/media/css/jquery.dataTables_themeroller.css',
			'~libraries/js/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($parentCssInstances, $modalInstances);
		return $cssInstances;
	}
}
