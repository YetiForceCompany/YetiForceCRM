<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/**
 * Class Calendar_Calendar_View.
 *
 * @package   View
 */
class Calendar_Calendar_View extends Vtiger_Index_View
{
	/**
	 * Get tpl path file.
	 *
	 * @param string $tplFile
	 *
	 * @return string
	 */
	protected function getTpl(string $tplFile)
	{
		return "Standard/$tplFile";
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($moduleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $request->getModule());
		parent::preProcess($request, false);
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function preProcessTplName(\App\Request $request)
	{
		return $this->getTpl('CalendarViewPreProcess.tpl');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/fullcalendar/dist/fullcalendar.js',
			'~libraries/css-element-queries/src/ResizeSensor.js',
			'~libraries/css-element-queries/src/ElementQueries.js',
			'~layouts/resources/Calendar.js',
			'modules.Calendar.resources.Standard.CalendarView'
		]));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/fullcalendar/dist/fullcalendar.css',
		]));
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($request->getBoolean('history')) {
			$historyParams = array_diff_key($request->getAll(), array_flip(['history', 'module', 'view']));
			$viewer->assign('HIDDEN_DAYS', implode(',', $request->getExploded('hiddenDays', ',', 'Integer')));
			$viewer->assign('TIME', $request->getByType('time', 'Standard'));
		}
		$viewer->assign('HISTORY_PARAMS', $historyParams ?? '');
		$viewer->assign('CURRENT_USER', $currentUserModel);
		$viewer->assign('EVENT_CREATE', \App\Privilege::isPermitted($request->getModule(), 'CreateView'));
		$viewer->assign('WEEK_COUNT', AppConfig::module('Calendar', 'WEEK_COUNT'));
		$viewer->assign('WEEK_VIEW', AppConfig::module('Calendar', 'SHOW_TIMELINE_WEEK') ? 'agendaWeek' : 'basicWeek');
		$viewer->assign('DAY_VIEW', AppConfig::module('Calendar', 'SHOW_TIMELINE_DAY') ? 'agendaDay' : 'basicDay');
		$viewer->assign('ALL_DAY_SLOT', AppConfig::module('Calendar', 'ALL_DAY_SLOT'));
		$viewer->assign('ACTIVITY_STATE_LABELS', \App\Json::encode([
			'current' => Calendar_Module_Model::getComponentActivityStateLabel('current'),
			'history' => Calendar_Module_Model::getComponentActivityStateLabel('history'),
		]));
		$viewer->view($this->getTpl('CalendarView.tpl'), $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$calendarFilters = Calendar_CalendarFilters_Model::getCleanInstance();
		$viewer->assign('CALENDAR_FILTERS', $calendarFilters);
		$viewer->view($this->getTpl('CalendarViewPostProcess.tpl'), $moduleName);
		parent::postProcess($request);
	}
}
