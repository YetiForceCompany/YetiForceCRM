<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Calendar_ActivityReminder_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('postpone');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Action to postpone activities.
	 *
	 * @param \App\Request $request
	 */
	public function postpone(App\Request $request)
	{
		$time = $request->getByType('time', 'Alnum');
		$module = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $module);
		$recordModel->updateReminderPostpone($time);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
