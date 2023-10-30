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
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showGantt');
	}

	/**
	 * Show gantt.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function showGantt(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PROJECTID', $request->getInteger('record'));
		$viewer->assign('GANTT_TITLE', \App\Language::translate('LBL_GANTT_TITLE', 'Project'));
		$viewer->view('gantt/GanttContents.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/chart.js/dist/Chart.js',
			'~libraries/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.js',
			'~libraries/gantt-elastic/dist/bundle.js',
			'modules.Project.resources.Gantt',
			'modules.Project.resources.GanttController'
		]));
	}
}
