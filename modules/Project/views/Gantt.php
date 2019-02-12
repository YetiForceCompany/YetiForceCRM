<?php

/**
 * Gantt view.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Project_Gantt_View extends Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAll($moduleName));
		$this->viewName = App\CustomView::getInstance($moduleName)->getViewId();
		$viewer->assign('VIEWID', $this->viewName);
		$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessTplName(\App\Request $request)
	{
		return 'gantt/GanttViewPreProcess.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->view('EmptyPostProcess.tpl', $request->getModule());
		parent::postProcess($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PROJECTID', 0);
		if ($request->has('view') && $request->getByType('view', 2) === 'Gantt') {
			$viewer->assign('GANTT_TITLE', \App\Language::translate('LBL_GANTT_TITLE_ALL_PROJECTS', 'Project'));
			$viewer->view('gantt/GanttAll.tpl', $moduleName);
		} else {
			$viewer->assign('GANTT_TITLE', \App\Language::translate('LBL_GANTT_TITLE', 'Project'));
			$viewer->view('gantt/GanttContents.tpl', $moduleName);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.css',
		]));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.js',
			'modules.CustomView.resources.CustomView',
			"modules.$moduleName.resources.CustomView",
			'~libraries/chart.js/dist/Chart.js',
			'~libraries/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.js',
			'modules.Project.resources.Gantt',
			'~libraries/gantt-elastic/dist/bundle.js',
			'modules.Project.resources.GanttController',
		]));
	}
}
