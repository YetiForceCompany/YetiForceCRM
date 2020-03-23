<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PriceBooks_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	public function saveRecord(App\Request $request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if ($request->getBoolean('relationOperation')) {
			$parentModuleName = $request->getByType('sourceModule', 2);
			$parentRecordId = $request->getInteger('sourceRecord');

			$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
			if ($relationModel = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($parentModuleName), $recordModel->getModule(), $relationId)) {
				$relationModel->addRelation($parentRecordId, $recordModel->getId());
			}
			//To store the relationship between Products/Services and PriceBooks
			if ($parentRecordId && ('Products' === $parentModuleName || 'Services' === $parentModuleName)) {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
				$recordModel->updateListPrice($parentRecordId, $parentRecordModel->getField('unit_price')->getUITypeModel()->getValueForCurrency(
					$parentRecordModel->get('unit_price'), $recordModel->get('currency_id'))
				);
			}
		}
		return $recordModel;
	}
}
