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

	public function preProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $request->getModule());

		parent::preProcess($request, false);
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessTplName(\App\Request $request)
	{
		return 'CalendarViewPreProcess.tpl';
	}

	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$jsFileNames = [
			'~libraries/fullcalendar/dist/fullcalendar.js',
			'~libraries/css-element-queries/src/ResizeSensor.js',
			'~libraries/css-element-queries/src/ElementQueries.js',
			'modules.Calendar.resources.CalendarView',
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = [
			'~libraries/fullcalendar/dist/fullcalendar.css',
		];
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}

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
		$viewer->view('CalendarView.tpl', $request->getModule());
	}

	public function postProcess(\App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$calendarFilters = Calendar_CalendarFilters_Model::getCleanInstance();
		$viewer->assign('CALENDAR_FILTERS', $calendarFilters);
		$viewer->view('CalendarViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}
}
