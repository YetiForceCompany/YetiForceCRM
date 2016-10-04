<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Products_RelationListView_Model extends Vtiger_RelationListView_Model
{

	protected $addRelatedFieldToEntries = [
		'IStorages' => ['qtyinstock' => 'qtyinstock'],
		'Calendar' => ['visibility' => 'visibility'],
		'PriceBooks' => ['unit_price' => 'unit_price', 'listprice' => 'listprice', 'currency_id' => 'currency_id'],
		'Documents' => ['filelocationtype' => 'filelocationtype', 'filestatus' => 'filestatus']
	];

	/**
	 * Function to get the links for related list
	 * @return <Array> List of action models <Vtiger_Link_Model>
	 */
	public function getLinks()
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
	}

	public function getHeaders()
	{
		$headerFields = parent::getHeaders();
		if ($this->getRelationModel()->get('modulename') == 'IStorages' && $this->getRelationModel()->get('name') == 'get_many_to_many') {
			$unitPriceField = new Vtiger_Field_Model();
			$unitPriceField->set('name', 'qtyinstock');
			$unitPriceField->set('column', 'qtyinstock');
			$unitPriceField->set('label', 'FL_QTY_IN_STOCK');
			$unitPriceField->set('fromOutsideList', true);

			$headerFields['qtyinstock'] = $unitPriceField;
		}
		if ($this->getRelationModel()->getRelationModuleModel()->getName() == 'PriceBooks') {
			//Added to support Unit Price
			$unitPriceField = new Vtiger_Field_Model();
			$unitPriceField->set('name', 'unit_price');
			$unitPriceField->set('column', 'unit_price');
			$unitPriceField->set('label', 'Unit Price');
			$unitPriceField->set('fromOutsideList', true);

			$headerFields['unit_price'] = $unitPriceField;

			//Added to support List Price
			$field = new Vtiger_Field_Model();
			$field->set('name', 'listprice');
			$field->set('column', 'listprice');
			$field->set('label', 'List Price');
			$field->set('fromOutsideList', true);

			$headerFields['listprice'] = $field;
		}

		return $headerFields;
	}
}
