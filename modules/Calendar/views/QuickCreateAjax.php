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
	 * Record model instance.
	 *
	 * @var Calendar_QuickCreateAjax_View
	 */
	public $record = false;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('sourceRecord')) {
			$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'), $moduleName);
			if (!($this->record->isEditable() || ($this->record->isCreateable() && $this->record->isViewable()))) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			parent::checkPermission($request);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$tplName = AppConfig::module('Calendar', 'CALENDAR_VIEW') . '\QuickCreate.tpl';
		if ($request->has('sourceRecord')) {
			$recordModel = $this->record ?: Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'));
		}
		$viewer->assign('RECORD', $recordModel ?? null);
		$viewer->assign('CURRENT_USER', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('EVENT_LIMIT', AppConfig::module('Calendar', 'EVENT_LIMIT'));
		$viewer->assign('WEEK_VIEW', AppConfig::module('Calendar', 'SHOW_TIMELINE_WEEK') ? 'agendaWeek' : 'basicWeek');
		$viewer->assign('DAY_VIEW', AppConfig::module('Calendar', 'SHOW_TIMELINE_DAY') ? 'agendaDay' : 'basicDay');
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
