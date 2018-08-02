<?php

/**
 * Relation Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Accounts_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Get products.
	 */
	public function getProducts()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_seproductsrel', 'vtiger_products.productid = vtiger_seproductsrel.productid']);
		$queryGenerator->addNativeCondition(['vtiger_seproductsrel.crmid' => $this->get('parentRecord')->getId()]);
	}
}
