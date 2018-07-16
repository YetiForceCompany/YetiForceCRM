<?php

/**
 * RelationListView Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_RelationListView_Model extends Vtiger_RelationListView_Model
{
	protected $addRelatedFieldToEntries = [
		'Products' => ['qtyproductinstock' => 'qtyproductinstock'],
		'Calendar' => ['visibility' => 'visibility'],
		'PriceBooks' => ['unit_price' => 'unit_price', 'listprice' => 'listprice', 'currency_id' => 'currency_id'],
		'Documents' => ['filelocationtype' => 'filelocationtype', 'filestatus' => 'filestatus'],
	];

	public function getHeaders()
	{
		$headerFields = parent::getHeaders();
		if ($this->getRelationModel()->get('modulename') == 'Products' && $this->getRelationModel()->get('name') == 'getManyToMany') {
			$qtyInStock = new Vtiger_Field_Model();
			$qtyInStock->setModule(Vtiger_Module_Model::getInstance('Products'));
			$qtyInStock->set('name', 'qtyproductinstock');
			$qtyInStock->set('column', 'qtyproductinstock');
			$qtyInStock->set('label', 'FL_QTY_IN_STOCK');
			$qtyInStock->set('fieldDataType', 'double');
			$qtyInStock->set('fromOutsideList', true);
			$headerFields['qtyproductinstock'] = $qtyInStock;
		}
		return $headerFields;
	}
}
