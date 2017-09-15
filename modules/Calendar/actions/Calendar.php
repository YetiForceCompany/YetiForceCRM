<?php

/**
 * Calendar action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Calendar_Calendar_Action extends Vtiger_BasicAjax_Action
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getEvents');
		$this->exposeMethod('updateEvent');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
		}
	}

	public function getEvents(\App\Request $request)
	{
		$record = Calendar_Calendar_Model::getCleanInstance();
		$record->set('user', $request->get('user'));
		$record->set('types', $request->getArray('types'));
		$record->set('time', $request->get('time'));
		if ($request->get('start') && $request->get('end')) {
			$record->set('start', $request->get('start'));
			$record->set('end', $request->get('end'));
		}
		if ($request->has('filters')) {
			$record->set('filters', $request->get('filters'));
		}
		if ($request->get('widget')) {
			$record->set('customFilter', $request->get('customFilter'));
			$entity = $record->getEntityCount();
		} else {
			$entity = $record->getEntity();
		}

		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}

	public function updateEvent(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('id');
		if (!\App\Privilege::isPermitted($moduleName, 'EditView', $recordId)) {
			$succes = false;
		} else {
			$delta = $request->get('delta');

			$start = DateTimeField::convertToDBTimeZone($request->get('start'));
			$date_start = $start->format('Y-m-d');
			$time_start = $start->format('H:i:s');
			$succes = false;
			if (!empty($recordId)) {
				try {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
					$recordData = $recordModel->entity->column_fields;
					$end = self::changeDateTime($recordData['due_date'] . ' ' . $recordData['time_end'], $delta);
					$due_date = $end['date'];
					$time_end = $end['time'];
					$recordModel->setId($recordId);
					$recordModel->set('date_start', $date_start);
					$recordModel->set('due_date', $due_date);
					if ($request->get('allDay') == 'true') {
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
