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
}
