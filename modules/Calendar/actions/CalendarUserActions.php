<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Class Calendar_CalendarUserActions_Action
 */
class Calendar_CalendarUserActions_Action extends Vtiger_Action_Controller
{

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->exposeMethod('deleteUserCalendar');
		$this->exposeMethod('addUserCalendar');
		$this->exposeMethod('deleteCalendarView');
		$this->exposeMethod('addCalendarView');
	}

	/**
	 * Check permissions
	 * @param \App\Request $request
	 * @throws \Exception\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if (!Users_Privileges_Model::isPermitted($moduleName, 'Save', $record)) {
			throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param \App\Request $request
	 * @return null
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to delete the user calendar from shared calendar
	 * @param \App\Request $request
	 */
	public function deleteUserCalendar(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$sharedUserId = $request->get('userid');

		$count = (new \App\Db\Query())->from('vtiger_shareduserinfo')->where(['userid' => $userId, 'shareduserid' => $sharedUserId])->count();
		$dbCommand = \App\Db::getInstance()->createCommand();
		if ($count) {
			$dbCommand->update('vtiger_shareduserinfo', ['visible' => 0], ['userid' => $userId, 'shareduserid' => $sharedUserId])->execute();
		} else {
			$dbCommand->insert('vtiger_shareduserinfo', [
				'userid' => $userId, 'shareduserid' => $sharedUserId, 'visible' => 0
			])->execute();
		}

		$result = array('userid' => $userId, 'sharedid' => $sharedUserId, 'username' => \App\Fields\Owner::getUserLabel($sharedUserId));
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to add other user calendar to shared calendar
	 * @param \App\Request $request
	 * @return Vtiger_Response $response
	 */
	public function addUserCalendar(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$sharedUserId = $request->get('selectedUser');
		$color = $request->get('selectedColor');

		$count = (new \App\Db\Query())->from('vtiger_shareduserinfo')->where(['userid' => $userId, 'shareduserid' => $sharedUserId])->count();
		$dbCommand = \App\Db::getInstance()->createCommand();
		if ($count) {
			$dbCommand->update('vtiger_shareduserinfo', ['color' => $color, 'visible' => 1], ['userid' => $userId, 'shareduserid' => $sharedUserId])->execute();
		} else {
			$dbCommand->insert('vtiger_shareduserinfo', ['userid' => $userId, 'shareduserid' => $sharedUserId, 'color' => $color, 'visible' => 1])->execute();
		}
		$response = new Vtiger_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}

	/**
	 * Function to delete the calendar view from My Calendar
	 * @param \App\Request $request
	 * @return Vtiger_Response $response
	 */
	public function deleteCalendarView(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$viewmodule = $request->get('viewmodule');
		$viewfieldname = $request->get('viewfieldname');


		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE vtiger_calendar_user_activitytypes
			INNER JOIN vtiger_calendar_default_activitytypes ON vtiger_calendar_default_activitytypes.id = vtiger_calendar_user_activitytypes.defaultid
			SET vtiger_calendar_user_activitytypes.visible=? WHERE vtiger_calendar_user_activitytypes.userid=? && vtiger_calendar_default_activitytypes.module=? && vtiger_calendar_default_activitytypes.fieldname=?', array('0', $userId, $viewmodule, $viewfieldname));

		$result = array('viewmodule' => $viewmodule, 'viewfieldname' => $viewfieldname, 'viewfieldlabel' => $request->get('viewfieldlabel'));
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to add calendar views to My calendar
	 * @param \App\Request $request
	 * @return Vtiger_Response $response
	 */
	public function addCalendarView(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$viewmodule = $request->get('viewmodule');
		$viewfieldname = $request->get('viewfieldname');
		$viewcolor = $request->get('viewColor');

		$db = PearDatabase::getInstance();

		$db->pquery('UPDATE vtiger_calendar_user_activitytypes
					INNER JOIN vtiger_calendar_default_activitytypes ON vtiger_calendar_default_activitytypes.id = vtiger_calendar_user_activitytypes.defaultid
					SET vtiger_calendar_user_activitytypes.color=?, vtiger_calendar_user_activitytypes.visible=?
					WHERE vtiger_calendar_user_activitytypes.userid=? && vtiger_calendar_default_activitytypes.module=? && vtiger_calendar_default_activitytypes.fieldname=?', array($viewcolor, '1', $userId, $viewmodule, $viewfieldname));

		$response = new Vtiger_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}
}
