<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class KnowledgeBase_Tree_View extends Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('Tree.tpl', $moduleName);
	}
	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/vue/dist/vue.min.js',
			'~libraries/quasar/dist/quasar.umd.min.js',
			'~libraries/quasar/dist/icon-set/mdi-v3.umd.min.js',
			'~layouts/basic/modules/KnowledgeBase/Tree.js'
		]));
	}
	public function getHeaderCss(App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/@mdi/font/css/materialdesignicons.min.css',
			'~libraries/quasar/dist/quasar.min.css'
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);

		return array_merge($headerCssInstances, $cssInstances);
	}
}
