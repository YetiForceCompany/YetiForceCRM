<?php

/**
 * Calendar action class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$this->exposeMethod('pinOrUnpinUser');
	}

	public function getEvents(App\Request $request)
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
	public function getEventsYear(App\Request $request)
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
	public function getCountEvents(App\Request $request)
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
	public function getCountEventsGroup(App\Request $request)
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
		foreach ($request->getArray('dates', 'Date') as $datePair) {
			$record->set('start', $datePair[0] . ' 00:00:00');
			$record->set('end', $datePair[1] . ' 23:59:59');
			$result[] = $record->getEntityRecordsCount();
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function updateEvent(App\Request $request)
	{
		$recordId = $request->getInteger('id');
		$start = DateTimeField::convertToDBTimeZone($request->getByType('start', 'DateTimeInUserFormat'));
		$end = DateTimeField::convertToDBTimeZone($request->getByType('end', 'DateTimeInUserFormat'));
		try {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $request->getModule());
			$recordModel->set('date_start', $start->format('Y-m-d'));
			$recordModel->set('due_date', $end->format('Y-m-d'));
			if ($request->getBoolean('allDay')) {
				$recordModel->set('allday', 1);
			} else {
				$recordModel->set('time_start', $start->format('H:i:s'));
				$recordModel->set('time_end', $end->format('H:i:s'));
				$recordModel->set('allday', 0);
			}
			$recordModel->save();
			$success = true;
		} catch (Exception $e) {
			$success = false;
		}
		$response = new Vtiger_Response();
		$response->setResult($success);
		$response->emit();
	}

	/**
	 * Get count Events for extended calendar's left column.
	 *
	 * @param \App\Request $request
	 */
	public function pinOrUnpinUser(App\Request $request)
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
