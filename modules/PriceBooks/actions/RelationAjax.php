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
	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode) && method_exists($this, "$mode")) {
			$this->$mode($request);
		}
	}

	/**
	 * Function adds PriceBooks-Products Relation.
	 *
	 * @param \App\Request $request
	 */
	public function addListPrice(\App\Request $request)
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

	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 */

	public function addRelation(\App\Request $request)
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
	 * @param <array> $request
	 */
	public function deleteRelation(\App\Request $request)
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
