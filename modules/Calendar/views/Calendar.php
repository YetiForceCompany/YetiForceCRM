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

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $request->getModule());

		parent::preProcess($request, false);
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessTplName(Vtiger_Request $request)
	{
		return 'CalendarViewPreProcess.tpl';
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$jsFileNames = array(
			'~libraries/fullcalendar/moment.min.js',
			'~libraries/fullcalendar/fullcalendar.js',
			'modules.Calendar.resources.CalendarView',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$headerCssInstances = parent::getHeaderCss($request);


		$cssFileNames = array(
			'~libraries/fullcalendar/fullcalendar.min.css',
			'~libraries/fullcalendar/fullcalendarCRM.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}

	public function process(Vtiger_Request $request)
	{
		include 'config/calendar.php';
		$mode = $request->getMode();
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$viewer->assign('CURRENT_USER', $currentUserModel);
		$viewer->assign('EVENT_LIMIT', $CALENDAR_CONFIG['EVENT_LIMIT']);
		$viewer->assign('WEEK_VIEW', $CALENDAR_CONFIG['SHOW_TIMELINE_WEEK'] ? 'agendaWeek' : 'basicWeek');
		$viewer->assign('DAY_VIEW', $CALENDAR_CONFIG['SHOW_TIMELINE_DAY'] ? 'agendaDay' : 'basicDay');
		$viewer->view('CalendarView.tpl', $request->getModule());
	}

	function postProcess(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->view('CalendarViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}
}
