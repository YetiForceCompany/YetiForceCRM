<?php

/**
 * Detail preview view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Detail preview view class.
 */
class Vtiger_DetailPreview_View extends Vtiger_Detail_View
{
	/** {@inheritdoc} */
	public function preProcessTplName(App\Request $request)
	{
		return 'DetailPreviewPreProcess.tpl';
	}

	/** {@inheritdoc} */
	protected function showBodyHeader()
	{
		return false;
	}

	/** {@inheritdoc} */
	protected function showFooter()
	{
		return false;
	}

	/** {@inheritdoc} */
	protected function showBreadCrumbLine()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getHeaderCss(App\Request $request)
	{
		$moduleName = $request->getModule();
		$handlerClass = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);
		$detailModel = new $handlerClass();

		return $detailModel->getHeaderCss($request);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$handlerClass = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);
		$detailModel = new $handlerClass();
		$scripts = $detailModel->getFooterScripts($request);
		unset($scripts['modules.Vtiger.resources.DetailPreview']);

		return array_merge($scripts, $this->checkAndConvertJsScripts([
			'~libraries/split.js/dist/split.js',
			'~libraries/css-element-queries/src/ResizeSensor.js',
			'~libraries/css-element-queries/src/ElementQueries.js',
			'modules.Vtiger.resources.Detail',
			"modules.$moduleName.resources.Detail",
			'modules.Vtiger.resources.DetailPreview',
			"modules.$moduleName.resources.DetailPreview",
		]));
	}

	/** {@inheritdoc} */
	public function loadJsConfig(App\Request $request)
	{
		parent::loadJsConfig($request);
		\App\Config::setJsEnv('modalTarget', 'parentIframe');
		\App\Config::setJsEnv('openUrlTarget', 'parentIframe');
	}
}
