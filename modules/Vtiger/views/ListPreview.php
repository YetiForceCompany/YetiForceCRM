<?php

/**
 * List preview view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ListPreview_View extends Vtiger_List_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->view('List/Preview.tpl', $moduleName);
		parent::process($request);
		$viewer->view('Detail/Preview.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $request->getModule();
		if ($request->isAjax() && !isset($this->viewName)) {
			$this->viewName = App\CustomView::getInstance($moduleName)->getViewId();
		}
		if (!$this->listViewModel) {
			$this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $this->viewName);
		}
		parent::initializeListViewContents($request, $viewer);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$scripts = parent::getFooterScripts($request);
		unset($scripts['modules.Vtiger.resources.ListPreview'], $scripts["modules.$moduleName.resources.ListPreview"]);

		return array_merge($scripts, $this->checkAndConvertJsScripts([
			'~libraries/split.js/dist/split.js',
			'modules.Vtiger.resources.ListPreview',
			"modules.$moduleName.resources.ListPreview",
		]));
	}
}
