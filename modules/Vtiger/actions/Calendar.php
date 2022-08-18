<?php

/**
 * Calendar actions file.
 *
 * @package Action
 *
 * @copyright 	YetiForce S.A.
 * @license 	YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author   	Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Calendar actions class.
 */
class Vtiger_Calendar_Action extends \App\Controller\Action
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
		$moduleName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($moduleName) || !\method_exists(Vtiger_Module_Model::getInstance($moduleName), 'getCalendarViewUrl')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ('updateEvent' === $request->getMode() && ($request->isEmpty('id', true) || !\App\Privilege::isPermitted($moduleName, 'EditView', $request->getInteger('id')))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getEvents');
		$this->exposeMethod('getEventsYear');
		$this->exposeMethod('updateEvent');
		$this->exposeMethod('getCountEventsGroup');
		$this->exposeMethod('pinOrUnpinUser');
	}

	public function getEvents(App\Request $request)
	{
		$record = $this->getCalendarModel($request);
		$record->remove('types');
		$entity = array_merge($record->getEntity(), $record->getPublicHolidays());
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
		$request->delete('end');
		$record = $this->getCalendarModel($request);
		$result = [];
		foreach ($request->getArray('dates', 'date') as $datePair) {
			$record->set('start', App\Fields\DateTime::formatToDisplay($datePair[0] . ' 00:00:00'));
			$record->set('end', App\Fields\DateTime::formatToDisplay($datePair[1] . ' 23:59:59'));
			$result[] = $record->getEntityRecordsCount();
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Get calendar model.
	 *
	 * @param App\Request $request
	 *
	 * @return Vtiger_Calendar_Model
	 */
	public function getCalendarModel(App\Request $request): Vtiger_Calendar_Model
	{
		$record = Vtiger_Calendar_Model::getInstance($request->getModule());
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
		return $record;
	}

	/**
	 * Update event.
	 *
	 * @param App\Request $request
	 */
	public function updateEvent(App\Request $request)
	{
		$record = Vtiger_Calendar_Model::getInstance($request->getModule());
		$success = $record->updateEvent($request->getInteger('id'), $request->getByType('start', 'dateTimeInUserFormat'), $request->getByType('end', 'dateTimeInUserFormat'), $request);
		$response = new Vtiger_Response();
		$response->setResult($success);
		$response->emit();
	}

	/**
	 * Get count Events for extended calendar's left column.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function pinOrUnpinUser(App\Request $request): void
	{
		$db = \App\Db::getInstance();
		$userId = \App\User::getCurrentUserId();
		if (!$request->isEmpty('element_id')) {
			$id = $request->getInteger('element_id');
			if (\App\User::isExists($id)) {
				$users = Vtiger_CalendarRightPanel_Model::getFavoriteUsers($request->getModule());
				if (empty($users[$id])) {
					$db->createCommand()->insert('u_#__users_pinned', [
						'user_id' => $userId,
						'tabid' => \App\Module::getModuleId($request->getModule()),
						'fav_id' => $id,
					])->execute();
					$result = 'pin';
				} else {
					$db->createCommand()->delete('u_#__users_pinned', ['id' => $users[$id]])->execute();
					$result = 'unpin';
				}
				\App\Cache::delete('FavoriteUsers', $userId);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
