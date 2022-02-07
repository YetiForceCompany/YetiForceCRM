<?php

/**
 * Relation Model.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
