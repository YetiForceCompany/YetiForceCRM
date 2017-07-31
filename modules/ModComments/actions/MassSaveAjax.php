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

class ModComments_MassSaveAjax_Action extends Vtiger_Mass_Action
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \Exception\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'Save')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Main process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$recordModels = $this->getRecordModelsFromRequest($request);
		$relationModel = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($request->get('source_module')), Vtiger_Module_Model::getInstance($request->getModule()));
		foreach ($recordModels as $relatedRecordId => &$recordModel) {
			$recordModel->save();
			$relationModel->addRelation($relatedRecordId, $recordModel->getId());
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param \App\Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	private function getRecordModelsFromRequest(\App\Request $request)
	{

		$moduleName = $request->getModule();
		$recordIds = $this->getRecordsListFromRequest($request);
		$recordModels = [];
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		foreach ($recordIds as &$recordId) {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('commentcontent', $request->get('commentcontent'));
			$recordModel->set('related_to', $recordId);
			$recordModel->set('assigned_user_id', $currentUserModel->getId());
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}
}
