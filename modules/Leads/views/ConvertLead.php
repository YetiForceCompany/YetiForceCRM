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

class Leads_ConvertLead_View extends Vtiger_Index_View
{
	/**
	 * Record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $record = false;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$moduleName = $request->getModule();
		if (!$recordId) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		if (!$this->record->isEditable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($moduleName, 'ConvertLead')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!Leads_Module_Model::checkIfAllowedToConvert($this->record->get('leadstatus'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(App\Request $request)
	{
		$currentUserPriviligeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$moduleModel = $this->record->getModule();
		$marketingProcessConfig = Vtiger_Processes_Model::getConfig('marketing', 'conversion');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('CURRENT_USER_PRIVILEGE', $currentUserPriviligeModel);
		$viewer->assign('RECORD', $this->record);
		$viewer->assign('CONVERT_LEAD_FIELDS', $this->record->getConvertLeadFields());

		$assignedToFieldModel = $moduleModel->getFieldByName('assigned_user_id');
		if ('true' === $marketingProcessConfig['change_owner']) {
			$assignedToFieldModel->set('fieldvalue', App\User::getCurrentUserId());
		} else {
			$assignedToFieldModel->set('fieldvalue', $this->record->get('assigned_user_id'));
		}
		$viewer->assign('CONVERSION_CONFIG', $marketingProcessConfig);
		$viewer->assign('ASSIGN_TO', $assignedToFieldModel);
		$viewer->view('ConvertLead.tpl', $moduleName);
	}
}
