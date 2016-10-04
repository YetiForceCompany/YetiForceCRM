<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_Basic_Action extends Vtiger_Action_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateFieldPinnedStatus');
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function updateFieldPinnedStatus(Vtiger_Request $request)
	{
		$fieldId = $request->get('fieldid');
		$menuItemModel = Settings_Vtiger_MenuItem_Model::getInstanceById($fieldId);

		$pin = $request->get('pin');
		if ($pin == 'true') {
			$menuItemModel->markPinned();
		} else {
			$menuItemModel->unMarkPinned();
		}

		$response = new Vtiger_Response();
		$response->setResult(array('SUCCESS' => 'OK'));
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
