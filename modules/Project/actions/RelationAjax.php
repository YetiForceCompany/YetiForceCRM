<?php

/**
 * RelationAjax Class for Project.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Project_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getHierarchyCount');
	}

	/**
	 * Number of hierarchy entries for a given record.
	 *
	 * @param \App\Request $request
	 */
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
