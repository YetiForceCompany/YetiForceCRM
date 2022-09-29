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
		$viewer->assign('WEEK_VIEW', App\Config::module('Calendar', 'SHOW_TIMELINE_WEEK') ? 'timeGridWeek' : 'dayGridWeek');
		$viewer->assign('DAY_VIEW', App\Config::module('Calendar', 'SHOW_TIMELINE_DAY') ? 'timeGridDay' : 'dayGridDay');
		$viewer->assign('ALL_DAY_SLOT', App\Config::module('Calendar', 'ALL_DAY_SLOT'));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
		$viewer->assign('MODAL_TITLE', $this->getPageTitle($request));
		$viewer->view('QuickCreate.tpl', ('Extended' === App\Config::module('Calendar', 'CALENDAR_VIEW')) ? $request->getModule() : '');
	}

	/** {@inheritdoc}  */
	public function loadFieldValuesFromSource(App\Request $request): array
	{
		$fieldValues = [];
		$postponeTimeData = [];
		$postponeTime = $request->getInteger('postponeTime');
		$sourceRelatedField = $this->recordModel->getModule()->getValuesFromSource($request);
		foreach ($sourceRelatedField as $fieldName => $fieldValue) {
			if ('' === $fieldValue) {
				continue;
			}
			if (isset($this->recordStructure[$fieldName])) {
				if ($this->fields[$fieldName]->isEditable() && ('' === $this->recordStructure[$fieldName]->get('fieldvalue') || null === $this->recordStructure[$fieldName]->get('fieldvalue'))) {
					$this->recordStructure[$fieldName]->set('fieldvalue', $fieldValue);
				}
			} else {
				if (isset($this->fields[$fieldName]) && $this->fields[$fieldName]->isEditable()) {
					$fieldModel = $this->fields[$fieldName];
					$fieldModel->set('fieldvalue', $fieldValue);
					$fieldValues[$fieldName] = $fieldModel;
				}
			}
			if (!empty($postponeTime) && \in_array($fieldName, ['date_start', 'due_date', 'time_start', 'time_end'])) {
				$postponeTimeData[$fieldName] = $fieldValue;
			}
		}
		if (!empty($postponeTime)) {
			$fieldValues = array_merge($fieldValues, $this->postponeTimeValue($postponeTimeData, $postponeTime));
		}
		return $fieldValues;
	}

	/**
	 * Load field  postpone values.
	 *
	 * @param array $postponeTimeData
	 * @param int   $postponeTime
	 *
	 * @return Vtiger_Field_Model[] Field instances
	 */
	public function postponeTimeValue($postponeTimeData, $postponeTime): array
	{
		$fieldValues = [];
		if (isset(explode(' ', $postponeTimeData['date_start'])[1])) {
			$dateStart = $postponeTimeData['date_start'];
		} else {
			$dateStart = $postponeTimeData['date_start'] . ' ' . $postponeTimeData['time_start'];
		}
		if (isset(explode(' ', $postponeTimeData['due_date'])[1])) {
			$dateEnd = $postponeTimeData['due_date'];
		} else {
			$dateEnd = $postponeTimeData['due_date'] . ' ' . $postponeTimeData['time_end'];
		}
		$newDateStart = new DateTime($dateStart);
		$newDateStart->modify("+ $postponeTime minutes");
		$newDateEnd = new DateTime($dateEnd);
		$newDateEnd->modify("+ $postponeTime minutes");
		[$startDate, $startTime] = explode(' ', $newDateStart->format('Y-m-d H:i:s'));
		[$endDate, $endTime] = explode(' ', $newDateEnd->format('Y-m-d H:i:s'));
		$postponeTimeData['date_start'] = $startDate;
		$postponeTimeData['due_date'] = $endDate;
		$postponeTimeData['time_start'] = $startTime;
		$postponeTimeData['time_end'] = $endTime;
		foreach ($postponeTimeData as $fieldName => $fieldValue) {
			if (isset($this->fields[$fieldName])) {
				$fieldModel = $this->fields[$fieldName];
				$fieldModel->set('fieldvalue', $fieldValue);
				$fieldValues[$fieldName] = $fieldModel;
			}
		}
		return $fieldValues;
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
