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
	public function getHeaders()
	{
		$headerFields = parent::getHeaders();
		if ('Products' == $this->getRelationModel()->get('modulename') && 'getManyToMany' == $this->getRelationModel()->get('name')) {
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
