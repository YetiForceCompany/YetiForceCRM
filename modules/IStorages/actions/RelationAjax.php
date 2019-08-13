<?php

/**
 * RelationAjax Class for IStorages.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getHierarchyCount');
		$this->exposeMethod('setQtyProducts');
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
		$focus = CRMEntity::getInstance($sourceModule);
		$hierarchy = $focus->getHierarchy($recordId);
		$response = new Vtiger_Response();
		$response->setResult(\count($hierarchy['entries']) - 1);
		$response->emit();
	}

	/**
	 * Sets number of products in storage.
	 *
	 * @param \App\Request $request
	 */
	public function setQtyProducts(App\Request $request)
	{
		$sourceModule = $request->getModule();
		if (!\App\Privilege::isPermitted($sourceModule, 'SetQtyProducts')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_TO_ACTION', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('src_record'), $sourceModule);
		if (!$recordModel->isViewable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$response = new Vtiger_Response();
		$response->setResult($recordModel->updateQtyProducts($request->getInteger('record'), $request->getByType('qty', 'NumberInUserFormat')));
		$response->emit();
	}
}
