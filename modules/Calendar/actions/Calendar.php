<?php

/**
 * Calendar actions file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
/**
 * Calendar actions class.
 */
class Calendar_Calendar_Action extends Vtiger_Calendar_Action
{
	/** {@inheritdoc} */
	public function getEvents(App\Request $request)
	{
		$record = $this->getCalendarModel($request);
		if ($request->getBoolean('widget')) {
			if ($request->has('customFilter')) {
				$record->set('customFilter', $request->getByType('customFilter', 2));
			}
			$entity = array_merge($record->getEntityCount(), $record->getPublicHolidays());
		} else {
			$entity = array_merge($record->getEntity(), $record->getPublicHolidays());
		}
		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}
}
