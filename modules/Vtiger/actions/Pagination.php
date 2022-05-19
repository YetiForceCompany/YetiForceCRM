<?php

/**
 * Actions to pagination.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getTotalCount');
	}

	public function getTotalCount(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewName = $request->getByType('viewname', 2);
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $viewName);
		if ($advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : []) {
			$listViewModel->set('advancedConditions', \App\Condition::validAdvancedConditions($advancedConditions));
		}
		$searchParams = App\Condition::validSearchParams($moduleName, $request->getArray('search_params'));
		if (empty($searchParams) || !\is_array($searchParams)) {
			$searchParams = [];
		}
		$listViewModel->set('search_params', $listViewModel->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams));
		$totalCount = (int) $listViewModel->getListViewCount();
		$data = [
			'totalCount' => $totalCount,
		];
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
