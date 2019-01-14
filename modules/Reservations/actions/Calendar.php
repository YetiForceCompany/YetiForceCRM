<?php

/**
 * Reservations calendar action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Reservations_Calendar_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getEvents');
		$this->exposeMethod('updateEvent');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function getEvents(\App\Request $request)
	{
		$record = Reservations_Calendar_Model::getInstance();
		$record->set('user', $request->getArray('user', 'Integer'));
		$record->set('types', $request->getArray('types', 'Alnum'));
		if ($request->has('start') && $request->has('end')) {
			$record->set('start', $request->getByType('start', 'DateInUserFormat'));
			$record->set('end', $request->getByType('end', 'DateInUserFormat'));
		}
		$entity = $record->getEntity();
		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}

	public function updateEvent(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('id');
		$date_start = date('Y-m-d', strtotime($request->get('start')));
		$time_start = date('H:i:s', strtotime($request->get('start')));
		$succes = false;
		if (!\App\Privilege::isPermitted($moduleName, 'EditView', $recordId)) {
			$succes = false;
		} else {
			if (!empty($recordId)) {
				try {
					$delta = $request->get('delta');
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
					$recordData = $recordModel->entity->column_fields;
					$end = self::changeDateTime($recordData['due_date'] . ' ' . $recordData['time_end'], $delta);
					$due_date = $end['date'];
					$time_end = $end['time'];
					$recordModel->setId($recordId);
					$recordModel->set('date_start', $date_start);
					$recordModel->set('time_start', $time_start);
					$recordModel->set('due_date', $due_date);
					$recordModel->set('time_end', $time_end);
					$recordModel->save();
					$succes = true;
				} catch (Exception $e) {
					$succes = false;
				}
			}
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
}
