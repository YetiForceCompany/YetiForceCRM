<?php

/**
 * Relation Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_Relation_Model extends Vtiger_Relation_Model
{

	/**
	 * Get many to many
	 */
	public function getManyToMany()
	{
		if ($this->getRelationModuleName() === 'Products') {
			$queryGenerator = $this->getQueryGenerator();
			$queryGenerator->setCustomColumn('u_#__istorages_products.qtyinstock qtyproductinstock');
		}
		parent::getManyToMany();
	}
}
