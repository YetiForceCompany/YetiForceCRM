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

class Calendar_Calendar_View extends Vtiger_Index_View
{
	/**
	 * List of tpl files.
	 *
	 * @var string[]
	 */
	protected $tplFiles = [
		'PreProcess' => '_#_/CalendarViewPreProcess.tpl',
		'PostProcess' => '_#_/CalendarViewPostProcess.tpl',
		'Process' => '_#_/CalendarView.tpl'
	];

	/**
	 * Calendar view.
	 *
	 * @var string
	 */
	protected $calendarView;

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
		$this->calendarView = \AppConfig::module($request->getModule(), 'CALENDAR_VIEW');
		if ($this->calendarView === false) {
			$this->calendarView = 'Standard';
		}
		foreach ($this->tplFiles as &$tplName) {
			$tplName = str_replace('_#_', $this->calendarView, $tplName);
		}
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
		return $this->tplFiles['PreProcess'];
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
			'modules.Calendar.resources.' . $this->calendarView . '.CalendarView',
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
		$viewer->assign('CURRENT_USER', $currentUserModel);
		$viewer->assign('EVENT_LIMIT', AppConfig::module('Calendar', 'EVENT_LIMIT'));
		$viewer->assign('WEEK_VIEW', AppConfig::module('Calendar', 'SHOW_TIMELINE_WEEK') ? 'agendaWeek' : 'basicWeek');
		$viewer->assign('DAY_VIEW', AppConfig::module('Calendar', 'SHOW_TIMELINE_DAY') ? 'agendaDay' : 'basicDay');
		$viewer->assign('ACTIVITY_STATE_LABELS', \App\Json::encode([
			'current' => Calendar_Module_Model::getComponentActivityStateLabel('current'),
			'history' => Calendar_Module_Model::getComponentActivityStateLabel('history'),
		]));
		$viewer->view($this->tplFiles['Process'], $request->getModule());
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
		$viewer->view($this->tplFiles['PostProcess'], $moduleName);
		parent::postProcess($request);
	}
}
