<?php

/**
 * Relation Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Get many to many.
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
