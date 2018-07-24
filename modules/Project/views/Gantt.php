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
			$viewer->view('gantt/GanttAll.tpl', $moduleName);
		} else {
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
			'~libraries/jquery-gantt-editor/platform.css',
			'~libraries/jquery-gantt-editor/libs/dateField/jquery.dateField.css',
			'~libraries/jquery-gantt-editor/gantt.css',
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
			'~libraries/jquery-gantt-editor/libs/jquery/jquery.livequery.1.1.1.min.js',
			'~libraries/jquery-timers/jquery.timers.js',
			'~libraries/jquery-gantt-editor/libs/utilities.js',
			'~libraries/jquery-gantt-editor/libs/forms.js',
			'~libraries/jquery-gantt-editor/libs/date.js',
			'~libraries/jquery-gantt-editor/libs/dialogs.js',
			'~libraries/jquery-gantt-editor/libs/layout.js',
			'~libraries/jquery-gantt-editor/libs/i18nJs.js',
			'~libraries/jquery-gantt-editor/libs/jquery/dateField/jquery.dateField.js',
			'~libraries/jquery-gantt-editor/libs/jquery/JST/jquery.JST.js',
			'~libraries/svg/jquery.svg.js',
			'~libraries/svg/jquery.svgdom.js',
			'~libraries/jquery-gantt-editor/ganttUtilities.js',
			'~libraries/jquery-gantt-editor/ganttTask.js',
			'~libraries/jquery-gantt-editor/ganttDrawerSVG.js',
			'~libraries/jquery-gantt-editor/ganttZoom.js',
			'~libraries/jquery-gantt-editor/ganttGridEditor.js',
			'~libraries/jquery-gantt-editor/ganttMaster.js',
			'modules.Project.resources.Gantt',
			'modules.Project.resources.GanttController',
		]));
	}
}
