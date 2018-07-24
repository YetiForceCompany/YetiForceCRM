<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Project_Detail_View extends Vtiger_Detail_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showCharts');
		$this->exposeMethod('showGantt');
	}

	/**
	 * Show time control chart.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function showCharts(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$moduleModel = Vtiger_Module_Model::getInstance('OSSTimeControl');
		if ($moduleModel) {
			$data = $moduleModel->getTimeUsers($recordId, $moduleName);
		}
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->view('charts/ShowTimeProjectUsers.tpl', $moduleName);
	}

	/**
	 * Show gantt.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function showGantt(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PROJECTID', $request->getInteger('record'));
		$viewer->view('gantt/GanttContents.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
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
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
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
