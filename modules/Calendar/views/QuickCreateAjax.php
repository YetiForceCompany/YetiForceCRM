<?php

/**
 * QuickCreate view for module Calendar.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
	/** @var Calendar_QuickCreateAjax_View Record model instance. */
	public $record;

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
		$viewer->assign('RECORD', $this->record);
		$viewer->assign('WEEK_COUNT', App\Config::module('Calendar', 'WEEK_COUNT'));
		$viewer->assign('WEEK_VIEW', App\Config::module('Calendar', 'SHOW_TIMELINE_WEEK') ? 'timeGridWeek' : 'basicWeek');
		$viewer->assign('DAY_VIEW', App\Config::module('Calendar', 'SHOW_TIMELINE_DAY') ? 'timeGridDay' : 'basicDay');
		$viewer->assign('ALL_DAY_SLOT', App\Config::module('Calendar', 'ALL_DAY_SLOT'));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('MODAL_TITLE', $this->getPageTitle($request));
		$viewer->view('QuickCreate.tpl', ('Extended' === App\Config::module('Calendar', 'CALENDAR_VIEW')) ? $request->getModule() : '');
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		if ('Extended' === App\Config::module('Calendar', 'CALENDAR_VIEW')) {
			return $this->checkAndConvertJsScripts([
				'~libraries/fullcalendar/main.js',
				'~libraries/css-element-queries/src/ResizeSensor.js',
				'~libraries/css-element-queries/src/ElementQueries.js',
				'modules.Calendar.resources.Edit',
				'~layouts/resources/Calendar.js',
				'modules.Vtiger.resources.CalendarView',
				"modules.{$request->getModule()}.resources.CalendarView",
				'modules.Calendar.resources.CalendarQuickCreate',
			]);
		}
		return parent::getFooterScripts($request);
	}

	/** {@inheritdoc} */
	public function getHeaderCss(App\Request $request)
	{
		return $this->checkAndConvertCssStyles([
			'~libraries/fullcalendar/main.css',
		]);
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_QUICK_CREATE', $request->getModule());
	}
}
