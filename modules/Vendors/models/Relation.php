<?php

/**
 * Relation Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Vendors_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Get products.
	 */
	public function getProducts()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_vendor', 'vtiger_vendor.vendorid = vtiger_products.vendor_id']);
		$queryGenerator->addNativeCondition(['vtiger_vendor.vendorid' => $this->get('parentRecord')->getId()]);
	}
}
