<?php

/**
 * Calendar actions.
 *
 * @package Action
 *
 * @copyright 	YetiForce Sp. z o.o
 * @license 	YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author   	Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Vtiger_Calendar_Action class.
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
		$this->exposeMethod('getCountEvents');
		$this->exposeMethod('updateEvent');
		$this->exposeMethod('getCountEventsGroup');
	}

	public function getEvents(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = Vtiger_Calendar_Model::getInstance($moduleName);
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
		$entity = array_merge($record->getEntity(), $record->getPublicHolidays());
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
		$record = Vtiger_Calendar_Model::getInstance($request->getModule());
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
		$record = Vtiger_Calendar_Model::getInstance($request->getModule());
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

	/**
	 * Update event.
	 *
	 * @param App\Request $request
	 */
	public function updateEvent(App\Request $request)
	{
		$record = Vtiger_Calendar_Model::getInstance($request->getModule());
		$success = $record->updateEvent($request->getInteger('id'), $request->get('start'), $request->getArray('delta'));
		$response = new Vtiger_Response();
		$response->setResult($success);
		$response->emit();
	}
}
