<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */
include_once 'include/utils/utils.php';

class PBXManager_IncomingCallPoll_Action extends Vtiger_Action_Controller
{

	public function __construct()
	{
		$this->exposeMethod('searchIncomingCalls');
		$this->exposeMethod('createRecord');
		$this->exposeMethod('getCallStatus');
		$this->exposeMethod('checkModuleViewPermission');
		$this->exposeMethod('checkPermissionForPolling');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function checkModuleViewPermission(\App\Request $request)
	{
		$response = new Vtiger_Response();
		$modules = array('Contacts', 'Leads');
		$view = $request->getByType('view', 1);
		Users_Privileges_Model::getCurrentUserPrivilegesModel();
		foreach ($modules as $module) {
			if (\App\Privilege::isPermitted($module, $view)) {
				$result['modules'][$module] = true;
			} else {
				$result['modules'][$module] = false;
			}
		}
		$response->setResult($result);
		$response->emit();
	}

	public function searchIncomingCalls(\App\Request $request)
	{
		$recordModel = PBXManager_Record_Model::getCleanInstance($request->getModule());
		$response = new Vtiger_Response();
		$user = Users_Record_Model::getCurrentUserModel();

		$recordModels = $recordModel->searchIncomingCall();
		// To check whether user have permission on caller record
		if ($recordModels) {
			foreach ($recordModels as $recordModel) {
				// To check whether the user has permission to see contact name in popup
				$recordModel->set('callername', null);

				$callerid = $recordModel->get('customer');
				if ($callerid) {
					$moduleName = $recordModel->get('customertype');
					if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $callerid)) {
						$name = $recordModel->get('customernumber') . \App\Language::translate('LBL_HIDDEN', 'PBXManager');
						$recordModel->set('callername', $name);
					} else {
						$entityNames = \App\Record::getLabel($callerid, $moduleName);
						$callerName = $entityNames[$callerid];
						$recordModel->set('callername', $callerName);
					}
				}
				// End
				$direction = $recordModel->get('direction');
				if ($direction == 'inbound') {
					$userid = $recordModel->get('user');
					if ($userid) {
						$entityNames = \App\Fields\Owner::getUserLabel($userid);
						$userName = $entityNames[$userid];
						$recordModel->set('answeredby', $userName);
					}
				}
				$recordModel->set('current_user_id', $user->id);
				$calls[] = $recordModel->getData();
			}
		}
		$response->setResult($calls);
		$response->emit();
	}

	public function createRecord(\App\Request $request)
	{
		$moduleName = $request->getByType('modulename', 1);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$name = explode("@", $request->get('email'));
		$element['lastname'] = $name[0];
		$element['email'] = $request->get('email');
		$element['phone'] = $request->get('number');

		$moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$mandatoryFieldModels = $moduleInstance->getMandatoryFieldModels();
		foreach ($mandatoryFieldModels as $mandatoryField) {
			$fieldName = $mandatoryField->get('name');
			$fieldType = $mandatoryField->getFieldDataType();
			$defaultValue = App\Purifier::decodeHtml($mandatoryField->get('defaultvalue'));
			if (!empty($element[$fieldName])) {
				continue;
			} else {
				$fieldValue = $defaultValue;
				if (empty($fieldValue)) {
					$fieldValue = Vtiger_Util_Helper::getDefaultMandatoryValue($fieldType);
				}
				$element[$fieldName] = $fieldValue;
			}
		}
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->setData($element);
		$recordModel->save();
		$this->updateCustomerInPhoneCalls($recordModel->getId(), $request);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Updates the customer in phone call
	 * @param int $id
	 * @param \App\Request $request
	 */
	public function updateCustomerInPhoneCalls($id, \App\Request $request)
	{
		$sourceuuid = $request->get('callid');
		$module = $request->get('modulename');
		$recordModel = PBXManager_Record_Model::getInstanceBySourceUUID($sourceuuid);
		$recordModel->updateCallDetails(array('customer' => $id, 'customertype' => $module));
	}

	public function getCallStatus($request)
	{
		$phonecallsid = $request->get('callid');
		$recordModel = PBXManager_Record_Model::getInstanceById($phonecallsid);
		$response = new Vtiger_Response();
		$response->setResult($recordModel->get('callstatus'));
		$response->emit();
	}

	public function checkPermissionForPolling(\App\Request $request)
	{
		Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$callPermission = \App\Privilege::isPermitted('PBXManager', 'ReceiveIncomingCalls');

		$serverModel = PBXManager_Server_Model::getInstance();
		$gateway = $serverModel->get("gateway");

		$user = Users_Record_Model::getCurrentUserModel();
		$userNumber = $user->phone_crm_extension;

		$result = false;
		if ($callPermission && $userNumber && $gateway) {
			$result = true;
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
