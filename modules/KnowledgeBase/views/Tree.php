<?php

/**
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_Tree_View extends Vtiger_Index_View
{

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
		$linkModels = $moduleModel->getSideBarLinks($linkParams);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUICK_LINKS', $linkModels);
		$viewer->view('TreeHeader.tpl', $moduleName);
	}

	public function getFooterScripts(\App\Request $request)
	{
		$parentScriptInstances = parent::getFooterScripts($request);
		$scripts = [
			'~libraries/jquery/jstree/jstree.js',
			'~libraries/jquery/datatables/media/js/jquery.dataTables.js',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.js',
		];
		$viewInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($parentScriptInstances, $viewInstances);
		return $scriptInstances;
	}

	public function getHeaderCss(\App\Request $request)
	{
		$parentCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/jquery/jstree/themes/proton/style.css',
			'~libraries/jquery/datatables/media/css/jquery.dataTables_themeroller.css',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($parentCssInstances, $modalInstances);
		return $cssInstances;
	}
}
