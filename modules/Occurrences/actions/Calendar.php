<?php

/**
 * OSSPasswords calendar action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Occurrences_Calendar_Action extends Vtiger_BasicAjax_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ('updateEvent' === $request->getMode() && ($request->isEmpty('id', true) || !\App\Privilege::isPermitted($request->getModule(), 'EditView', $request->getInteger('id')))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getEvents');
		$this->exposeMethod('getEventsYear');
		$this->exposeMethod('getCountEvents');
		$this->exposeMethod('updateEvent');
		$this->exposeMethod('getCountEventsGroup');
	}

	public function getEvents(App\Request $request)
	{
		$record = Occurrences_Calendar_Model::getInstance();
		$record->set('user', $request->getArray('user', 'Alnum'));
		$record->set('types', $request->getArray('types', 'Text'));
		$record->set('time', $request->isEmpty('time') ? '' : $request->getByType('time'));
		if ($request->has('start') && $request->has('end')) {
			$record->set('start', $request->getByType('start', 'DateInUserFormat'));
			$record->set('end', $request->getByType('end', 'DateInUserFormat'));
		}
		if ($request->has('filters')) {
			$record->set('filters', $request->getByType('filters', 'Alnum'));
		}
		if ($request->has('cvid')) {
			$record->set('customFilter', $request->getInteger('cvid'));
		}
		if ($request->getBoolean('widget')) {
			$record->set('customFilter', $request->getByType('customFilter', 2));
			$entity = array_merge($record->getEntityCount(), $record->getPublicHolidays());
		} else {
			if ($request->getBoolean('yearView')) {
				$entity = array_merge($record->getEntityCount(), $record->getPublicHolidays());
			} else {
				$entity = array_merge($record->getEntity(), $record->getPublicHolidays());
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}

	/**
	 * Get events for year view.
	 *
	 * @param \App\Request $request
	 */
	public function getEventsYear(App\Request $request)
	{
		$record = Occurrences_Calendar_Model::getInstance();
		$record->set('user', $request->getArray('user', 'Alnum'));
		$record->set('time', $request->isEmpty('time') ? '' : $request->getByType('time'));
		if ($request->has('start') && $request->has('end')) {
			$record->set('start', $request->getByType('start', 'DateInUserFormat'));
			$record->set('end', $request->getByType('end', 'DateInUserFormat'));
		}
		if ($request->has('filters')) {
			$record->set('filters', $request->getByType('filters', 'Alnum'));
		}
		if ($request->has('cvid')) {
			$record->set('customFilter', $request->getInteger('cvid'));
		}
		$entity = array_merge($record->getEntityYearCount(), $record->getPublicHolidays());
		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}

	/**
	 * Get count Events for extended calendar's left column.
	 *
	 * @param \App\Request $request
	 */
	public function getCountEvents(App\Request $request)
	{
		$record = Occurrences_Calendar_Model::getInstance();
		$record->set('user', $request->getArray('user', 'Alnum'));
		$record->set('types', $request->getArray('types', 'Text'));
		$record->set('time', $request->isEmpty('time') ? '' : $request->getByType('time'));
		if ($request->has('start') && $request->has('end')) {
			$record->set('start', $request->getByType('start', 'DateInUserFormat'));
			$record->set('end', $request->getByType('end', 'DateInUserFormat'));
		}
		if ($request->has('filters')) {
			$record->set('filters', $request->getByType('filters', 'Alnum'));
		}
		if ($request->has('cvid')) {
			$record->set('customFilter', $request->getInteger('cvid'));
		}
		$entity = $record->getEntityRecordsCount();
		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}

	/**
	 * Get count Events for extended calendar's left column.
	 *
	 * @param \App\Request $request
	 */
	public function getCountEventsGroup(App\Request $request)
	{
		$record = Occurrences_Calendar_Model::getInstance();
		$record->set('user', $request->getArray('user', 'Alnum'));
		$record->set('types', $request->getArray('types', 'Text'));
		$record->set('time', $request->isEmpty('time') ? '' : $request->getByType('time'));
		if ($request->has('filters')) {
			$record->set('filters', $request->getByType('filters', 'Alnum'));
		}
		if ($request->has('cvid')) {
			$record->set('customFilter', $request->getInteger('cvid'));
		}
		$result = [];
		foreach ($request->getArray('dates', 'DateTimeInUserFormat') as $datePair) {
			$record->set('start', $datePair[0]);
			$record->set('end', $datePair[1]);
			$result[] = $record->getEntityRecordsCount();
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function updateEvent(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('id');
		$delta = $request->getArray('delta');
		$start = DateTimeField::convertToDBTimeZone($request->get('start'), \App\User::getCurrentUserModel(), false);
		$date_start = $start->format('Y-m-d H:i:s');
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$end = self::changeDateTime($recordModel->get('date_end'), $delta);
			$due_date = $end['date'] . ' ' . $end['time'];
			$recordModel->set('date_start', $date_start);
			$recordModel->set('date_end', $due_date);
			$recordModel->save();
			$success = true;
		} catch (Exception $e) {
			$success = false;
		}
		$response = new Vtiger_Response();
		$response->setResult($success);
		$response->emit();
	}

	public function changeDateTime($datetime, $delta)
	{
		$date = new DateTime($datetime);
		if (0 != $delta['days']) {
			$date = $date->modify('+' . $delta['days'] . ' days');
		}
		if (0 != $delta['hours']) {
			$date = $date->modify('+' . $delta['hours'] . ' hours');
		}
		if (0 != $delta['minutes']) {
			$date = $date->modify('+' . $delta['minutes'] . ' minutes');
		}
		return ['date' => $date->format('Y-m-d'), 'time' => $date->format('H:i:s')];
	}
}
