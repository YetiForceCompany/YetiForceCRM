<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PriceBooks_Save_Action extends Vtiger_Save_Action
{
	/** {@inheritdoc} */
	public function saveRecord(App\Request $request)
	{
		$this->getRecordModelFromRequest($request);
		$eventHandler = $this->record->getEventHandler();
		foreach ($eventHandler->getHandlers(\App\EventHandler::EDIT_VIEW_PRE_SAVE) as $handler) {
			if (!(($response = $eventHandler->triggerHandler($handler))['result'] ?? null)) {
				throw new \App\Exceptions\NoPermittedToRecord($response['message'], 406);
			}
		}
		$this->record->save();
		if ($request->getBoolean('relationOperation')) {
			$parentModuleName = $request->getByType('sourceModule', 2);
			$parentRecordId = $request->getInteger('sourceRecord');

			$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
			if ($relationModel = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($parentModuleName), $this->record->getModule(), $relationId)) {
				$relationModel->addRelation($parentRecordId, $this->record->getId());
			}
			//To store the relationship between Products/Services and PriceBooks
			if ($parentRecordId && ('Products' === $parentModuleName || 'Services' === $parentModuleName)) {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
				$this->record->updateListPrice($parentRecordId, $parentRecordModel->getField('unit_price')->getUITypeModel()->getValueForCurrency(
					$parentRecordModel->get('unit_price'), $this->record->get('currency_id'))
				);
			}
		}
	}
}
