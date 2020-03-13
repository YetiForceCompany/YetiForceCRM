<?php

/**
 * Calendar view class.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Vtiger_Calendar_View class.
 */
class Vtiger_Calendar_View extends Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($moduleName) || !\method_exists(Vtiger_Module_Model::getInstance($moduleName), 'getCalendarViewUrl')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getTpl(string $tplFile)
	{
		return "Calendar/{$tplFile}";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function preProcessTplName(App\Request $request)
	{
		return $this->getTpl('PreProcess.tpl');
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, $display);
		$mid = $request->has('mid') ? $request->getInteger('mid') : null;
		$this->getViewer($request)->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($request->getModule(), $mid));
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if ($request->getBoolean('history')) {
			$historyParams = array_diff_key($request->getAll(), array_flip(['history', 'module', 'view']));
			$viewer->assign('HIDDEN_DAYS', implode(',', $request->getExploded('hiddenDays', ',', 'Integer')));
			$viewer->assign('TIME', $request->getByType('time', 'Standard'));
		}
		$viewer->assign('LINKS', Vtiger_Calendar_Model::getInstance($moduleName)->getSideBarLinks([]));
		$viewer->assign('HISTORY_PARAMS', $historyParams ?? []);
		$viewer->assign('EVENT_CREATE', \App\Privilege::isPermitted($request->getModule(), 'CreateView'));
		$viewer->assign('EVENT_EDIT', \App\Privilege::isPermitted($request->getModule(), 'EditView'));
		$viewer->assign('WEEK_COUNT', App\Config::module('Calendar', 'WEEK_COUNT'));
		$viewer->assign('WEEK_VIEW', App\Config::module('Calendar', 'SHOW_TIMELINE_WEEK') ? 'agendaWeek' : 'basicWeek');
		$viewer->assign('DAY_VIEW', App\Config::module('Calendar', 'SHOW_TIMELINE_DAY') ? 'agendaDay' : 'basicDay');
		$viewer->assign('ALL_DAY_SLOT', App\Config::module('Calendar', 'ALL_DAY_SLOT'));
		$viewer->view($this->getTpl('CalendarView.tpl'), $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('FILTERS', ['Filter']);
		$viewer->view($this->getTpl('PostProcess.tpl'), $request->getModule());
		parent::postProcess($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/fullcalendar/dist/fullcalendar.js',
			'~libraries/css-element-queries/src/ResizeSensor.js',
			'~libraries/css-element-queries/src/ElementQueries.js',
			'~layouts/resources/Calendar.js',
			'~layouts/resources/YearView.js',
			'modules.Vtiger.resources.CalendarView',
			"modules.{$request->getModule()}.resources.CalendarView",
		]));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/fullcalendar/dist/fullcalendar.css',
		]));
	}
}
