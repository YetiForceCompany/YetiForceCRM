<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Project_Gantt_View extends Vtiger_Index_View
{
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$mid = false;
		if ($request->has('mid')) {
			$mid = $request->getInteger('mid');
		}
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName, $mid));
		$this->viewName = App\CustomView::getInstance($moduleName)->getViewId();
		if ($request->isEmpty('viewname') && App\CustomView::hasViewChanged($moduleName, $this->viewName)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($this->viewName);
			if ($customViewModel) {
				App\CustomView::setDefaultSortOrderBy($moduleName, ['orderBy' => $customViewModel->getSortOrderBy('orderBy'), 'sortOrder' => $customViewModel->getSortOrderBy('sortOrder')]);
			}
			App\CustomView::setCurrentView($moduleName, $this->viewName);
		}
		$this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $this->viewName);
		if (isset($_SESSION['lvs'][$moduleName]['entityState'])) {
			$this->listViewModel->set('entityState', $_SESSION['lvs'][$moduleName]['entityState']);
		}
		$viewer->assign('VIEWID', $this->viewName);
		$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	public function preProcessTplName(\App\Request $request)
	{
		return 'gantt/ListViewPreProcess.tpl';
	}

	protected function preProcessDisplay(\App\Request $request)
	{
		parent::preProcessDisplay($request);
	}

	public function postProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->view('gantt/ListViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$gantt = new Project_Gantt_Model();
		$data = $gantt->getAllGanttProjects();
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		if ($request->has('view') && $request->getByType('view', 2) ==='Gantt') {
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
			'modules.Vtiger.resources.List',
			"modules.$moduleName.resources.List",
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
		]));
	}
}
