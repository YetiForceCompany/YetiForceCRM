<?php

/**
 * Detail preview view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_DetailPreview_View extends Vtiger_Detail_View
{

	/**
	 * {@inheritDoc}
	 */
	public function preProcessTplName(\App\Request $request)
	{
		return 'DetailPreviewPreProcess.tpl';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function showBodyHeader()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function showFooter()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function showBreadCrumbLine()
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$handlerClass = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);
		$detailModel = new $handlerClass();
		return $detailModel->getHeaderCss($request);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$handlerClass = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);
		$detailModel = new $handlerClass();
		$scripts = $detailModel->getFooterScripts($request);
		unset($scripts['modules.Vtiger.resources.DetailPreview']);
		return array_merge($scripts, $this->checkAndConvertJsScripts([
				'~libraries/split.js/split.js',
				'~libraries/css-element-queries/src/ResizeSensor.js',
				'~libraries/css-element-queries/src/ElementQueries.js',
				'modules.Vtiger.resources.Detail',
				'modules.Vtiger.resources.DetailPreview',
				"modules.$moduleName.resources.DetailPreview"
		]));
	}
}
