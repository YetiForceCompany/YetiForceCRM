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

class ModComments_MassSaveAjax_Action extends Vtiger_Mass_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$sourceModule = $request->getByType('source_module', 2);
		$moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
		if (!$moduleModel->isCommentEnabled() || !$userPrivilegesModel->hasModuleActionPermission($sourceModule, 'MassAddComment')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Main process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$recordModels = $this->getRecordModelsFromRequest($request);
		$relationModel = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($request->getByType('source_module', 2)), Vtiger_Module_Model::getInstance($request->getModule()));
		foreach ($recordModels as $relatedRecordId => &$recordModel) {
			$recordModel->save();
			$relationModel->addRelation($relatedRecordId, $recordModel->getId());
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	private function getRecordModelsFromRequest(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordIds = self::getRecordsListFromRequest($request);
		$recordModels = [];
		$userId = \App\User::getCurrentUserRealId();
		foreach ($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('commentcontent', \App\Utils\Completions::encodeAll(\App\Purifier::decodeHtml($request->getForHtml('commentcontent'))));
			$recordModel->set('related_to', $recordId);
			$recordModel->set('assigned_user_id', $userId);
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}
}
