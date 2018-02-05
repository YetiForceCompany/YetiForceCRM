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

class Campaigns_DetailAjax_Action extends Vtiger_RelatedList_View
{

	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getRecordsCount');
	}

	/**
	 * Function to get related Records count from this relation
	 * @param \App\Request $request
	 * @return <Number> Number of record from this relation
	 */
	public function getRecordsCount(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$parentId = $request->getInteger('record');
		$label = $request->get('tab_label');

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$count = $relationListView->getRelatedEntriesCount();
		$result = [];
		$result['module'] = $moduleName;
		$result['viewname'] = $request->getByType('viewname', 2);
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}
