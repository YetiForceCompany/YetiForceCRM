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

class Products_RelationListView_Model extends Vtiger_RelationListView_Model
{
	/** {@inheritdoc} */
	public function getLinks(): array
	{
		$relationModel = $this->getRelationModel();
		$parentModel = $this->getParentRecordModel();
		$isSubProduct = false;
		if ($parentModel->getModule()->getName() == $relationModel->getRelationModuleModel()->getName()) {
			$isSubProduct = $relationModel->isSubProduct($parentModel->getId());
		}
		if (!$isSubProduct) {
			return parent::getLinks();
		}
		return [];
	}

	/** {@inheritdoc} */
	public function getHeaders()
	{
		$headerFields = parent::getHeaders();
		if ('IStorages' === $this->getRelationModel()->get('modulename') && 'getManyToMany' === $this->getRelationModel()->get('name')) {
			$qtyInStockField = new Vtiger_Field_Model();
			$qtyInStockField->setModule(Vtiger_Module_Model::getInstance('IStorages'));
			$qtyInStockField->set('name', 'qtyinstock');
			$qtyInStockField->set('column', 'qtyinstock');
			$qtyInStockField->set('label', 'FL_QTY_IN_STOCK');
			$qtyInStockField->set('fromOutsideList', true);
			if (App\Config::module('IStorages', 'allowSetQtyProducts', false) && App\Privilege::isPermitted('IStorages', 'SetQtyProducts')) {
				$qtyInStockField->set('isEditable', true);
			}
			$headerFields['qtyinstock'] = $qtyInStockField;
		}
		if ('PriceBooks' === $this->getRelationModel()->getRelationModuleModel()->getName() &&
		($unitPriceField = $this->getParentRecordModel()->getModule()->getFieldByName('unit_price')) &&
		$unitPriceField->isActiveField()) {
			//Added to support Unit Price
			$moduleModel = $this->getRelationModel()->getRelationModuleModel();
			$unitPriceField->setModule($moduleModel);
			$unitPriceField->set('label', 'Unit Price');
			$unitPriceField->set('fromOutsideList', true);
			$headerFields[$unitPriceField->getName()] = $unitPriceField;
			//Added to support List Price
			$field = new Vtiger_Field_Model();
			$field->setModule($moduleModel);
			$field->set('name', 'listprice');
			$field->set('column', 'listprice');
			$field->set('label', 'List Price');
			$field->set('typeofdata', 'N~O');
			$field->set('isEditable', true);
			$field->set('fromOutsideList', true);
			$field->set('class', 'validate[required,funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]');
			$headerFields['listprice'] = $field;
		}
		return $headerFields;
	}
}
