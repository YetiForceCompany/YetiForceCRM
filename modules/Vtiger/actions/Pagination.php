<?php

class Vtiger_Pagination_Action extends Vtiger_BasicAjax_Action
{

	function __construct()
	{
		$this->exposeMethod('getTotalCount');
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}

	public function getTotalCount(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->get('viewname');
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $viewName);
		$searchParmams = $request->get('search_params');
		if (empty($searchParmams) || !is_array($searchParmams)) {
			$searchParmams = [];
		}
		$searchParmams = Vtiger_Util_Helper::transferListSearchParamsToFilterCondition($searchParmams, $listViewModel->getModule());
		$listViewModel->set('search_params', $searchParmams);
		$totalCount = (int) $listViewModel->getListViewCount();
		$data = [
			'totalCount' => $totalCount
		];
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}

	function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
}
