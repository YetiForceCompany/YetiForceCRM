<?php

/**
 * RelationAjax Class for Accounts
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Accounts_RelationAjax_Action extends Vtiger_RelationAjax_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getHierarchyCount');
	}

	public function getHierarchyCount($request)
	{
		$sourceModule = $request->getModule();
		$recordId = $request->get('record');
		$focus = CRMEntity::getInstance($sourceModule);
		$hierarchy = $focus->getAccountHierarchy($recordId);
		$response = new Vtiger_Response();
		$response->setResult(count($hierarchy['entries']) - 1);
		$response->emit();
	}
}
