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

class Leads_ConvertLead_View extends Vtiger_Index_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if (!$moduleModel->isPermitted('ConvertLead')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}

		$recordId = $request->get('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		if (!Leads_Module_Model::checkIfAllowedToConvert($recordModel->get('leadstatus'))) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$currentUserPriviligeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$viewer = $this->getViewer($request);
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$moduleModel = $recordModel->getModule();
		$marketingProcessConfig = Vtiger_Processes_Model::getConfig('marketing', 'conversion');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('CURRENT_USER_PRIVILEGE', $currentUserPriviligeModel);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('CONVERT_LEAD_FIELDS', $recordModel->getConvertLeadFields());

		$assignedToFieldModel = $moduleModel->getField('assigned_user_id');
		if ($marketingProcessConfig['change_owner'] === 'true') {
			$assignedToFieldModel->set('fieldvalue', App\User::getCurrentUserId());
		} else {
			$assignedToFieldModel->set('fieldvalue', $recordModel->get('assigned_user_id'));
		}
		$viewer->assign('CONVERSION_CONFIG', $marketingProcessConfig);
		$viewer->assign('ASSIGN_TO', $assignedToFieldModel);
		$viewer->view('ConvertLead.tpl', $moduleName);
	}
}
