<?php

/**
 * Calendar view Class.
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
	protected function preProcessTplName(\App\Request $request)
	{
		return 'Extended/CalendarViewPreProcess.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		parent::process($request);
		$viewer = $this->getViewer($request);
		$viewer->view('Extended/CalendarView.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
		parent::postProcess($request, $display);
		$viewer = $this->getViewer($request);
		$viewer->view('Extended/CalendarViewPostProcess.tpl', $request->getModule());
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
			'modules.Calendar.resources.Standard.CalendarView',
			'modules.Calendar.resources.Extended.CalendarView',
		]));
	}
}
