<?php

/**
 * QuickCreate view for module Calendar.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Calendar_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$tplName = AppConfig::module('Calendar', 'CALENDAR_VIEW') . '\QuickCreate.tpl';
		$viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->view($tplName, $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$jsFiles = parent::getFooterScripts($request);
		if (AppConfig::module('Calendar', 'CALENDAR_VIEW') === 'Extended') {
			$jsFiles = array_merge($jsFiles, $this->checkAndConvertJsScripts([
				'~libraries/moment/min/moment.min.js',
				'~libraries/fullcalendar/dist/fullcalendar.js',
				'~libraries/css-element-queries/src/ResizeSensor.js',
				'~libraries/css-element-queries/src/ElementQueries.js',
				'modules.Calendar.resources.Standard.CalendarView',
				'modules.Calendar.resources.Extended.YearView',
				'modules.Calendar.resources.Extended.CalendarView',
				'modules.Calendar.resources.Extended.QuickCreate',
			]));
		}
		return $jsFiles;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return $this->checkAndConvertCssStyles([
			'~libraries/fullcalendar/dist/fullcalendar.css',
		]);
	}
}
