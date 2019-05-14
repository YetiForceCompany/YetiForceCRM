<?php
/**
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

class KnowledgeBase_Tree_View extends Vtiger_Index_View
{

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
			'~layouts/basic/modules/KnowledgeBase/Tree.vue.js'
		]));
	}
	public function getHeaderCss(App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/@mdi/font/css/materialdesignicons.min.css',
			'~libraries/quasar/dist/quasar.min.css'
		];
		return array_merge($headerCssInstances, $this->checkAndConvertCssStyles($cssFileNames));
	}
}
