<?php

/**
 * QuickCreate view.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Occurrences_QuickCreateAjax_View class.
 */
class Occurrences_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!$request->isEmpty('sourceRecord', true) && !\App\Privilege::isPermitted($request->getByType('sourceModule', 2), 'DetailView', $request->getInteger('sourceRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function postProcessAjax(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $this->record ?? null);
		$viewer->assign('WEEK_COUNT', App\Config::module('Calendar', 'WEEK_COUNT'));
		$viewer->assign('WEEK_VIEW', App\Config::module('Calendar', 'SHOW_TIMELINE_WEEK') ? 'timeGridWeek' : 'basicWeek');
		$viewer->assign('DAY_VIEW', App\Config::module('Calendar', 'SHOW_TIMELINE_DAY') ? 'timeGridDay' : 'basicDay');
		$viewer->assign('ALL_DAY_SLOT', App\Config::module('Calendar', 'ALL_DAY_SLOT'));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('MODAL_TITLE', $this->getPageTitle($request));
		$viewer->view('Calendar/QuickCreate.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$jsFiles = parent::getFooterScripts($request);
		unset($jsFiles["modules.{$request->getModule()}.resources.QuickCreate"]);
		$jsFiles = [];
		return array_merge($jsFiles, $this->checkAndConvertJsScripts([
			'~libraries/fullcalendar/main.js',
			'~libraries/css-element-queries/src/ResizeSensor.js',
			'~libraries/css-element-queries/src/ElementQueries.js',
			'~layouts/resources/Calendar.js',
			'modules.Vtiger.resources.CalendarView',
			"modules.{$request->getModule()}.resources.CalendarView",
			"modules.{$request->getModule()}.resources.QuickCreate",
		]));
	}

	/** {@inheritdoc} */
	public function getHeaderCss(App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/fullcalendar/main.css',
		]));
	}
}
