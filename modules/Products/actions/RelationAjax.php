<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Products_RelationAjax_Action extends Vtiger_RelationAjax_Action
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addListPrice');
		$this->exposeMethod('setQtyProducts');
	}

	/**
	 * Function adds Products/Services-PriceBooks Relation.
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
	 * Sets number of products in storage.
	 *
	 * @param \App\Request $request
	 */
	public function setQtyProducts(App\Request $request)
	{
		$sourceModule = $request->getModule();
		if (!App\Config::module('IStorages', 'allowSetQtyProducts', false) || !\App\Privilege::isPermitted($sourceModule, 'SetQtyProducts')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_TO_ACTION', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), 'IStorages');
		$productId = $request->getInteger('src_record');
		if (!$recordModel->isViewable() && App\Privilege::isPermitted($sourceModule, 'DetailView', $productId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$response = new Vtiger_Response();
		$response->setResult((bool) $recordModel->updateQtyProducts($productId, $request->getByType('qty', 'NumberInUserFormat')));
		$response->emit();

		$eventHandler = new App\EventHandler();
		$eventHandler->setModuleName('IStorages');
		$eventHandler->setParams([
			'storageId' => $request->getInteger('record'),
			'products' => [$productId => $request->getByType('qty', 'NumberInUserFormat')],
			'operator' => 'value'
		]);
		$eventHandler->trigger('IStoragesAfterUpdateStock');
	}
}
