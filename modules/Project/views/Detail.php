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
			'modules.Vtiger.resources.dashboards.Widget',
			'~libraries/gantt/dhtmlxgantt.js',
		]));
	}
}
