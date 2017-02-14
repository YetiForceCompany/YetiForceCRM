<?php

/**
 * RelationAjax Class for SSalesProcesses
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class SSalesProcesses_RelationAjax_Action extends Vtiger_RelationAjax_Action
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
		$hierarchy = $focus->getHierarchy($recordId);
		$response = new Vtiger_Response();
		$response->setResult(count($hierarchy['entries']) - 1);
		$response->emit();
	}
}
