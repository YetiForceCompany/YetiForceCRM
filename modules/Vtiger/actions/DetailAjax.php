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

class Vtiger_DetailAjax_Action extends App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getByType('relatedModule', 2))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getRecordsCount');
	}

	/**
	 * Function to get related Records count from this relation.
	 *
	 * @param \App\Request $request
	 */
	public function getRecordsCount(App\Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$parentId = $request->getInteger('record');
		$parentRecordModel = \Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		$relationListView = \Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $request->getInteger('relationId'), $cvId);
		$searchParams = App\Condition::validSearchParams($relatedModuleName, $request->getArray('search_params'));
		if (!empty($searchParams) && \is_array($searchParams)) {
			$relationListView->set('search_params', $relationListView->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams));
		}

		$result = [];
		$result['module'] = $moduleName;
		$result['viewname'] = $request->getByType('viewname', 2);
		$result['count'] = $relationListView->getRelatedEntriesCount();
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
