<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Campaigns_RelationAjax_Action extends Vtiger_RelationAjax_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addRelationsFromRelatedModuleViewId');
		$this->exposeMethod('updateStatus');
	}

	/**
	 * Function to add relations using related module viewid
	 * @param \App\Request $request
	 */
	public function addRelationsFromRelatedModuleViewId(\App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('sourceRecord');
		if (!\App\Privilege::isPermitted($sourceModule, 'DetailView', $sourceRecordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		$relatedModuleName = $request->getByType('relatedModule', 1);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($relatedModuleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$viewId = $request->get('viewId');
		if ($viewId) {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($request->getModule());
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
			if (in_array($relatedModuleName, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
				$queryGenerator = new App\QueryGenerator($relatedModuleName);
				$queryGenerator->initForCustomViewById($viewId);
				$dataReader = $queryGenerator->createQuery()->createCommand()->query();
				while ($row = $dataReader->read()) {
					$relatedRecordIdsList[] = $row['id'];
				}
				if (empty($relatedRecordIdsList)) {
					$response = new Vtiger_Response();
					$response->setResult([false]);
					$response->emit();
				} else {
					foreach ($relatedRecordIdsList as $relatedRecordId) {
						$relationModel->addRelation($sourceRecordId, $relatedRecordId);
					}
				}
			}
		}
	}

	/**
	 * Function to update Relation status
	 * @param \App\Request $request
	 */
	public function updateStatus(\App\Request $request)
	{
		$relatedModuleName = $request->getByType('relatedModule', 1);
		$relatedRecordId = $request->getInteger('relatedRecord');
		$status = $request->get('status');
		$response = new Vtiger_Response();
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('sourceRecord')) || !\App\Privilege::isPermitted($relatedModuleName, 'DetailView', $relatedRecordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		if ($relatedRecordId && $status && $status < 5) {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($request->getModule());
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);

			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
			$relationModel->updateStatus($request->getInteger('sourceRecord'), [$relatedRecordId => $status]);

			$response->setResult([true]);
		} else {
			$response->setError($code);
		}
		$response->emit();
	}
}
