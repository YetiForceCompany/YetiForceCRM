<?php

/**
 * RelationAjax Class for Competition.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Contacts_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getHierarchyCount');
	}

	public function getHierarchyCount(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$recordId = $request->getInteger('record');
		if (!\App\Privilege::isPermitted($sourceModule, 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$focus = CRMEntity::getInstance($sourceModule);
		$hierarchy = $focus->getHierarchy($recordId);
		$response = new Vtiger_Response();
		$response->setResult(\count($hierarchy['entries']) - 1);
		$response->emit();
	}
}
