<?php

/**
 * Relation Model
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Leads_Relation_Model extends Vtiger_Relation_Model
{

	/**
	 * Get products
	 */
	public function getProducts()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_seproductsrel', 'vtiger_products.productid = vtiger_seproductsrel.productid']);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_leaddetails', 'vtiger_leaddetails.leadid = vtiger_seproductsrel.crmid']);
		$queryGenerator->addNativeCondition(['vtiger_leaddetails.leadid' => $this->get('parentRecord')->getId()]);
	}
}
