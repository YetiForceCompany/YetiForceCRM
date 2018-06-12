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
		$viewer->assign('DATA', (new Project_Gantt_Model())->getAllData());
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
			'~libraries/jQueryGantt/platform.css',
			'~libraries/jQueryGantt/libs/dateField/jquery.dateField.css',
			'~libraries/jQueryGantt/gantt.css',
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
			'~libraries/jQueryGantt/libs/jquery/jquery.livequery.1.1.1.min.js',
			'~libraries/jQueryGantt/libs/jquery/jquery.timers.js',
			'~libraries/jQueryGantt/libs/utilities.js',
			'~libraries/jQueryGantt/libs/forms.js',
			'~libraries/jQueryGantt/libs/date.js',
			'~libraries/jQueryGantt/libs/dialogs.js',
			'~libraries/jQueryGantt/libs/layout.js',
			'~libraries/jQueryGantt/libs/i18nJs.js',
			'~libraries/jQueryGantt/libs/jquery/dateField/jquery.dateField.js',
			'~libraries/jQueryGantt/libs/jquery/JST/jquery.JST.js',
			'~libraries/jQueryGantt/libs/jquery/svg/jquery.svg.min.js',
			'~libraries/jQueryGantt/libs/jquery/svg/jquery.svgdom.1.8.js',
			'~libraries/jQueryGantt/ganttUtilities.js',
			'~libraries/jQueryGantt/ganttTask.js',
			'~libraries/jQueryGantt/ganttDrawerSVG.js',
			'~libraries/jQueryGantt/ganttZoom.js',
			'~libraries/jQueryGantt/ganttGridEditor.js',
			'~libraries/jQueryGantt/ganttMaster.js',
			'modules.Project.resources.Gantt',
			'modules.Project.resources.GanttController',
		]));
	}
}
