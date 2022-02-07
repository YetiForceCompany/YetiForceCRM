<?php
/**
 * View to show events which they are visible under the form.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Class Calendar_QuickCreateEvents_View.
 */
class Calendar_QuickCreateEvents_View extends Vtiger_IndexAjax_View
{
	/**
	 * Main process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$dates = [];
		$moduleName = $request->getModule();
		$currentDate = DateTimeField::convertToDBFormat($request->getByType('currentDate', 'DateInUserFormat'));
		$hideDays = App\Config::module($moduleName, 'HIDDEN_DAYS_IN_CALENDAR_VIEW');
		$numberDaysToDisplay = 3;
		$dates[$numberDaysToDisplay] = $currentDate;
		$currentDateInstance = new DateTime($currentDate);
		$dateInstance = clone $currentDateInstance;
		while ($numberDaysToDisplay) {
			$dateInstance->sub(new DateInterval('P1D'));
			if (!\in_array($dateInstance->format('w'), $hideDays)) {
				--$numberDaysToDisplay;
				$dates[$numberDaysToDisplay] = $dateInstance->format('Y-m-d');
			}
		}
		$numberDaysToDisplay = 3;
		$dateInstance = clone $currentDateInstance;
		while (6 !== $numberDaysToDisplay) {
			$dateInstance->add(new DateInterval('P1D'));
			if (!\in_array($dateInstance->format('w'), $hideDays)) {
				++$numberDaysToDisplay;
				$dates[$numberDaysToDisplay] = $dateInstance->format('Y-m-d');
			}
		}
		ksort($dates);
		$record = Calendar_Calendar_Model::getCleanInstance();
		$record->set('user', $request->getInteger('user'));
		$record->set('time', 'current');
		$record->set('start', \App\Fields\Date::formatToDisplay(reset($dates)) . ' 00:00:00');
		$record->set('end', \App\Fields\Date::formatToDisplay(end($dates)) . ' 23:59:59');
		$events = $record->getEntity();
		$records = [];
		foreach ($events as $event) {
			$records[$event['start_date']][] = $event;
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('DATES', $dates);
		$viewer->assign('EVENTS', $records);
		$viewer->assign('SHOW_COMPANIES', App\Config::module($moduleName, 'SHOW_COMPANIES_IN_QUICKCREATE'));
		$viewer->view('QuickCreateEvents.tpl', $moduleName);
	}
}
