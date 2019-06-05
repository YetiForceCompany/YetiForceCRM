<?php
/**
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

class KnowledgeBase_KnowledgeBase_View extends Vtiger_Index_View
{

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('KnowledgeBase.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			'~libraries/@mdi/font/css/materialdesignicons.min.css',
			'~src/css/app.css'
		];
		return array_merge($headerCssInstances, $this->checkAndConvertCssStyles($cssFileNames));
	}
}
