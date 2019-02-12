<?php

/**
 * Actions to pagination.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Pagination_Action extends Vtiger_BasicAjax_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function __construct()
	{
		$this->exposeMethod('getTotalCount');
	}

	public function getTotalCount(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('viewname', 2);
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $viewName);
		$searchParmams = App\Condition::validSearchParams($moduleName, $request->getArray('search_params'));
		if (empty($searchParmams) || !is_array($searchParmams)) {
			$searchParmams = [];
		}
		$listViewModel->set('search_params', $listViewModel->get('query_generator')->parseBaseSearchParamsToCondition($searchParmams));
		$totalCount = (int) $listViewModel->getListViewCount();
		$data = [
			'totalCount' => $totalCount,
		];
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
