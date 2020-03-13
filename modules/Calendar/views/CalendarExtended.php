<?php

/**
 * Calendar view Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_CalendarExtended_View extends Calendar_Calendar_View
{
	/**
	 * {@inheritdoc}
	 */
	protected function getTpl(string $tplFile)
	{
		return "Extended/$tplFile";
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, $display);

		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$mid = false;
		if ($request->has('mid')) {
			$mid = $request->getInteger('mid');
		}
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName, $mid));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function preProcessTplName(App\Request $request)
	{
		return 'Calendar/PreProcess.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$calendarFilters = Calendar_CalendarFilters_Model::getCleanInstance();
		$viewer->assign('FILTERS', ['Events', 'Filter']);
		$viewer->assign('SHOW_TYPE', true);
		$viewer->assign('CALENDAR_FILTERS', $calendarFilters);
		$viewer->view('Calendar/PostProcess.tpl', $moduleName);
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
			'modules.Calendar.resources.Standard.CalendarView',
			'modules.Calendar.resources.Extended.CalendarView',
		]));
	}
}
