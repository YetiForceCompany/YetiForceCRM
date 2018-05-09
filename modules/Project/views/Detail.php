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
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getGanttProject($recordId);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', \App\Json::encode($data));
		$viewer->view('gantt/GanttContents.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/gantt/skins/dhtmlxgantt_broadway.css',
			'~libraries/jQueryGantt/platform.css',
			'~libraries/jQueryGantt/libs/dateField/jquery.dateField.css',
			'~libraries/jQueryGantt/gantt.css',
		]));
	}

	public function getHeaderScripts(\App\Request $request)
	{
		return array_merge(parent::getHeaderScripts($request), $this->checkAndConvertJsScripts([
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
			//'~libraries/gantt/dhtmlxgantt.js',
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
