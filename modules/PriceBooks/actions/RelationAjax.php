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

	public function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode) && method_exists($this, "$mode")) {
			$this->$mode($request);
			return;
		}
	}

	/**
	 * Function adds PriceBooks-Products Relation
	 * @param type $request
	 */
	public function addListPrice($request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');
		$relatedModule = $request->get('related_module');
		$relInfos = $request->get('relinfo');
		$relatedModule = $request->get('related_module');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach ($relInfos as $relInfo) {
			$price = CurrencyField::convertToDBFormat($relInfo['price'], null, true);
			$relationModel->addListPrice($sourceRecordId, $relInfo['id'], $price);
		}
	}
	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 */

	public function addRelation($request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		if (is_numeric($relatedModule)) {
			$relatedModule = vtlib\Functions::getModuleName($relatedModule);
		}
		$relatedRecordIdList = $request->get('related_record_list');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach ($relatedRecordIdList as $relatedRecordId) {
			$relationModel->addRelation($sourceRecordId, $relatedRecordId, $listPrice);
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param <array> $request
	 */
	public function deleteRelation($request)
	{
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = PriceBooks_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach ($relatedRecordIdList as $relatedRecordId) {
			$relationModel->deleteRelation($sourceRecordId, $relatedRecordId);
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
