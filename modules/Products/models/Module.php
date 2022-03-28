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

class Products_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 * @param bool                $skipSelected
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, App\QueryGenerator $queryGenerator)
	{
		$supportedModulesList = [$this->getName(), 'Vendors', 'Leads', 'Accounts', 'PriceBooks'];
		if (\in_array($sourceModule, $supportedModulesList) || Vtiger_Module_Model::getInstance($sourceModule)->isInventory()) {
			$condition = ['and', ['vtiger_products.discontinued' => 1]];
			if ($sourceModule === $this->getName()) {
				$subQuery = (new App\Db\Query())
					->select(['productid'])
					->from('vtiger_seproductsrel')
					->where(['setype' => $sourceModule]);
				$condition[] = ['not in', 'vtiger_products.productid', $subQuery];
				$subQuery = (new App\Db\Query())
					->select(['crmid'])
					->from('vtiger_seproductsrel')
					->where(['productid' => $record]);
				$condition[] = ['not in', 'vtiger_products.productid', $subQuery];
				$condition[] = ['<>', 'vtiger_products.productid', $record];
			} elseif ('PriceBooks' === $sourceModule) {
				$subQuery = (new App\Db\Query())
					->select(['productid'])
					->from('vtiger_pricebookproductrel')
					->where(['pricebookid' => $record]);
				$condition[] = ['not in', 'vtiger_products.productid', $subQuery];
			} elseif ('Vendors' === $sourceModule) {
				$condition[] = ['<>', 'vtiger_products.vendor_id', $record];
			}
			$queryGenerator->addNativeCondition($condition);
		}
	}

	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getQueryForRecords(string $searchValue, int $limit, int $srcRecord = null): App\QueryGenerator
	{
		$queryGenerator = parent::getQueryForRecords($searchValue, $limit, $srcRecord);
		$queryGenerator->addCondition('discontinued', 1, 'e');
		return $queryGenerator;
	}
}
