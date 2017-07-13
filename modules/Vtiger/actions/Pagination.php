<?php

/**
 * Actions to pagination
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_Pagination_Action extends Vtiger_BasicAjax_Action
{

	public function __construct()
	{
		$this->exposeMethod('getTotalCount');
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}

	public function getTotalCount(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->get('viewname');
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $viewName);
		$searchParmams = $request->get('search_params');
		if (empty($searchParmams) || !is_array($searchParmams)) {
			$searchParmams = [];
		}
		$searchParmams = $listViewModel->get('query_generator')->parseBaseSearchParamsToCondition($searchParmams);
		$listViewModel->set('search_params', $searchParmams);
		$totalCount = (int) $listViewModel->getListViewCount();
		$data = [
			'totalCount' => $totalCount
		];
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}

	public function process(\App\Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
}
