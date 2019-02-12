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

class Campaigns_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		parent::checkPermission($request);
		if (!$request->isEmpty('sourceRecord', true) && !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('sourceRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!$request->isEmpty('relatedRecord', true) && !\App\Privilege::isPermitted($request->getByType('relatedModule', 2), 'DetailView', $request->getInteger('relatedRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addRelationsFromRelatedModuleViewId');
		$this->exposeMethod('updateStatus');
	}

	/**
	 * Function to add relations using related module viewid.
	 *
	 * @param \App\Request $request
	 */
	public function addRelationsFromRelatedModuleViewId(\App\Request $request)
	{
		$sourceRecordId = $request->getInteger('sourceRecord');
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($relatedModuleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$viewId = $request->getByType('viewId', 2);
		$response = new Vtiger_Response();
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
				$dataReader->close();
				if (empty($relatedRecordIdsList)) {
					$response->setResult(false);
				} else {
					foreach ($relatedRecordIdsList as $relatedRecordId) {
						$relationModel->addRelation($sourceRecordId, $relatedRecordId);
					}
					$response->setResult(true);
				}
			} else {
				$response->setResult(false);
			}
		} else {
			$response->setResult(false);
		}
		$response->emit();
	}

	/**
	 * Function to update Relation status.
	 *
	 * @param \App\Request $request
	 */
	public function updateStatus(\App\Request $request)
	{
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$status = !$request->isEmpty('status') ? $request->getInteger('status') : '';
		$response = new Vtiger_Response();
		if ($status && $status < 5) {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($request->getModule());
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
			$relationModel->updateStatus($request->getInteger('sourceRecord'), [$request->getInteger('relatedRecord') => $status]);
			$response->setResult([true]);
		} else {
			$response->setError(false);
		}
		$response->emit();
	}
}
