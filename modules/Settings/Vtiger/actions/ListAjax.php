<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_ListAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPageCount');
	}

	/**
	 * Function returns the number of records for the current filter.
	 *
	 * @param \App\Request $request
	 */
	public function getRecordsCount(App\Request $request)
	{
		$moduleName = $request->getModule();
		$cvId = $request->getByType('viewname', 2);
		$count = $this->getListViewCount($request);

		$result = [];
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	public function getListViewCount(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$sourceModule = $request->getByType('sourceModule', 2);

		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);

		if (!empty($sourceModule)) {
			$listViewModel->set('sourceModule', $sourceModule);
		}
		return $listViewModel->getListViewCount();
	}

	public function getPageCount(App\Request $request)
	{
		$numOfRecords = $this->getListViewCount($request);
		$pagingModel = new Vtiger_Paging_Model();
		$pageCount = ceil((int) $numOfRecords / (int) ($pagingModel->getPageLimit()));

		if (0 == $pageCount) {
			$pageCount = 1;
		}
		$result = [];
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $numOfRecords;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
