<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PriceBooks_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('specifyMargin');
		$this->exposeMethod('addListPrice');
		$this->exposeMethod('addRelation');
		$this->exposeMethod('deleteRelation');
	}

	/**
	 * Setting margins.
	 *
	 * @param App\Request $request
	 */
	public function specifyMargin(App\Request $request)
	{
		$margin = $request->getByType('margin', 'NumberInUserFormat');
		$recordId = $request->getInteger('record');
		$queryGenerator = static::getQuery($request);
		$queryGenerator->setFields(['id', 'purchase']);
		$currencyId = Vtiger_Record_Model::getInstanceById($recordId, 'PriceBooks')->get('currency_id');
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$purchasePrice = (new Vtiger_MultiCurrency_UIType())->getValueForCurrency($row['purchase'], $currencyId);
			$dbCommand->update(
				'vtiger_pricebookproductrel',
				['listprice' => ((100.00 + $margin) / 100.00) * $purchasePrice],
				['pricebookid' => $recordId, 'productid' => $row['id']]
			)->execute();
		}
		$dataReader->close();
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function adds PriceBooks-Products Relation.
	 *
	 * @param \App\Request $request
	 */
	public function addListPrice(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->getInteger('src_record');
		if (!\App\Privilege::isPermitted($sourceModule, 'DetailView', $sourceRecordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($request->getByType('related_module', 2));
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		$status = $relationModel->addListPrice($sourceRecordId, $request->getInteger('record'), $request->getByType('price', 'NumberInUserFormat'));
		$response = new Vtiger_Response();
		$response->setResult((bool) $status);
		$response->emit();
	}

	/**
	 * Function to add relation for specified source record id and related record id list.
	 *
	 * @param \App\Request $request
	 */
	public function addRelation(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->getInteger('src_record');
		$relatedModule = $request->getByType('related_module', 2);
		if (is_numeric($relatedModule)) {
			$relatedModule = \App\Module::getModuleName($relatedModule);
		}
		if (!\App\Privilege::isPermitted($sourceModule, 'DetailView', $sourceRecordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach ($request->getArray('related_record_list', 'Integer') as $relatedRecordId) {
			if (\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedRecordId)) {
				$relationModel->addRelation($sourceRecordId, $relatedRecordId);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list.
	 *
	 * @param \App\Request $request
	 */
	public function deleteRelation(App\Request $request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->getInteger('src_record');
		$relatedModule = $request->getByType('related_module');
		$relatedRecordIdList = $request->getArray('related_record_list', 'Integer');
		if (!\App\Privilege::isPermitted($sourceModule, 'DetailView', $sourceRecordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = PriceBooks_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach ($relatedRecordIdList as $relatedRecordId) {
			if (\App\Privilege::isPermitted($relatedModule, 'DetailView', $relatedRecordId)) {
				$relationModel->deleteRelation($sourceRecordId, $relatedRecordId);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
