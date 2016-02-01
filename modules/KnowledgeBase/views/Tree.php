<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class KnowledgeBase_Tree_View extends Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('TreeHeader.tpl', $moduleName);
	}
	public function getFooterScripts(Vtiger_Request $request)
	{
		$parentScriptInstances = parent::getFooterScripts($request);
		$scripts = [
			'~libraries/jquery/jstree/jstree.js',
			'~libraries/jquery/jstree/jstree.category.js'
		];
		$viewInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($parentScriptInstances, $viewInstances);
		return $scriptInstances;
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$parentCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/jquery/jstree/themes/proton/style.css',
		];
		$modalInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$cssInstances = array_merge($parentCssInstances, $modalInstances);
		return $cssInstances;
	}
}
