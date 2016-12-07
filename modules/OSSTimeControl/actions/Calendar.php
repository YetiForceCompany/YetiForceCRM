<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSTimeControl_Calendar_Action extends Vtiger_Action_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getEvent');
		$this->exposeMethod('updateEvent');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
		}
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function getEvent(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$id = $request->get('id');

		$record = OSSTimeControl_Calendar_Model::getInstance();
		$record->set('user', $request->get('user'));
		$record->set('types', $request->get('types'));
		if ($request->get('start') && $request->get('end')) {
			$record->set('start', $request->get('start'));
			$record->set('end', $request->get('end'));
		}
		$entity = $record->getEntity();

		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}

	public function updateEvent(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('id');
		$date_start = date('Y-m-d', strtotime($request->get('start')));
		$time_start = date('H:i:s', strtotime($request->get('start')));
		$succes = false;
		if (isPermitted($moduleName, 'EditView', $recordId) === 'no') {
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
					$recordModel->set('id', $recordId);
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
