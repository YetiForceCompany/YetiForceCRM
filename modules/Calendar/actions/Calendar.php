<?php

/**
 * Calendar action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Calendar_Calendar_Action extends Vtiger_BasicAjax_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ($request->getMode() === 'updateEvent' && ($request->isEmpty('id', true) || !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('id')))) {
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
		$this->exposeMethod('pinOrUnpinUser');
	}

	public function getEvents(\App\Request $request)
	{
		$record = Calendar_Calendar_Model::getCleanInstance();
		$record->set('user', $request->getArray('user', 'Alnum'));
		$record->set('types', $request->getArray('types', 'Text'));
		$record->set('time', $request->getByType('time'));
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
	public function getEventsYear(\App\Request $request)
	{
		$record = Calendar_Calendar_Model::getCleanInstance();
		$record->set('user', $request->getArray('user', 'Alnum'));
		$record->set('time', $request->getByType('time'));
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
	public function getCountEvents(\App\Request $request)
	{
		$record = Calendar_Calendar_Model::getCleanInstance();
		$record->set('user', $request->getArray('user', 'Alnum'));
		$record->set('types', $request->getArray('types', 'Text'));
		$record->set('time', $request->getByType('time'));
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
	public function getCountEventsGroup(\App\Request $request)
	{
		$record = Calendar_Calendar_Model::getCleanInstance();
		$record->set('user', $request->getArray('user', 'Alnum'));
		$record->set('types', $request->getArray('types', 'Text'));
		$record->set('time', $request->getByType('time'));
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

	public function updateEvent(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('id');
		$delta = $request->getArray('delta');
		$start = DateTimeField::convertToDBTimeZone($request->get('start'));
		$date_start = $start->format('Y-m-d');
		$time_start = $start->format('H:i:s');
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordData = $recordModel->entity->column_fields;
			$end = self::changeDateTime($recordData['due_date'] . ' ' . $recordData['time_end'], $delta);
			$due_date = $end['date'];
			$time_end = $end['time'];
			$recordModel->setId($recordId);
			$recordModel->set('date_start', $date_start);
			$recordModel->set('due_date', $due_date);
			if ($request->getBoolean('allDay')) {
				$recordModel->set('allday', 1);
				$start = self::changeDateTime($recordData['date_start'] . ' ' . $recordData['time_start'], $delta);
				$recordModel->set('date_start', $start['date']);
			} else {
				$recordModel->set('time_start', $time_start);
				$recordModel->set('time_end', $time_end);
				$recordModel->set('allday', 0);
			}
			$recordModel->save();
			$succes = true;
		} catch (Exception $e) {
			$succes = false;
		}
		$response = new Vtiger_Response();
		$response->setResult($succes);
		$response->emit();
	}

	public function changeDateTime($datetime, $delta)
	{
		$date = new DateTime($datetime);
		if ($delta['days'] != 0) {
			$date = $date->modify('+' . $delta['days'] . ' days');
		}
		if ($delta['hours'] != 0) {
			$date = $date->modify('+' . $delta['hours'] . ' hours');
		}
		if ($delta['minutes'] != 0) {
			$date = $date->modify('+' . $delta['minutes'] . ' minutes');
		}
		return ['date' => $date->format('Y-m-d'), 'time' => $date->format('H:i:s')];
	}

	/**
	 * Get count Events for extended calendar's left column.
	 *
	 * @param \App\Request $request
	 */
	public function pinOrUnpinUser(\App\Request $request)
	{
		$db = \App\Db::getInstance();
		$userId = \App\User::getCurrentUserId();
		if (!$request->isEmpty('element_id')) {
			$favouritesId = $request->getInteger('element_id');
			if (\App\User::isExists($favouritesId)) {
				$query = new \App\Db\Query();
				$existsRecords = $query
					->from('u_#__users_pinned')
					->where(['owner_id' => $userId])
					->where(['fav_element_id' => $favouritesId])
					->exists();
				$data = [
					'owner_id' => $userId,
					'fav_element_id' => $favouritesId,
				];
				if (!$existsRecords) {
					$db->createCommand()->insert('u_#__users_pinned', $data)->execute();
					$result = 'pin';
				} else {
					$db->createCommand()->delete('u_#__users_pinned', $data)->execute();
					$result = 'unpin';
				}
				\App\Cache::delete('UsersFavourite', $userId);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
