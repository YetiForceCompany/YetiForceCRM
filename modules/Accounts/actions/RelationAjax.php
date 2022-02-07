<?php

/**
 * RelationAjax Class for Accounts.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Accounts_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getHierarchyCount');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!$request->isEmpty('record', true) && !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Number of hierarchy entries for a given record.
	 *
	 * @param \App\Request $request
	 */
	public function getHierarchyCount(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$focus = CRMEntity::getInstance($sourceModule);
		$hierarchy = $focus->getAccountHierarchy($request->getInteger('record'));
		$response = new Vtiger_Response();
		$response->setResult(\count($hierarchy['entries']) - 1);
		$response->emit();
	}
}
