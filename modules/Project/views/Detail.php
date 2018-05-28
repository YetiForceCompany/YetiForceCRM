<?php

/**
 * Gantt view.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
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
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', Vtiger_Module_Model::getInstance('OSSTimeControl')->getTimeUsers($recordId, $moduleName));
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
		$gantt = new Project_Gantt_Model();
		$data = $gantt->getById($recordId);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->view('gantt/GanttContents.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
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
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
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
