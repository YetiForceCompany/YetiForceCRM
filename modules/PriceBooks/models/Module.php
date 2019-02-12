<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PriceBooks_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator)
	{
		if (($sourceModule === 'Products' || $sourceModule === 'Services') && isset($queryGenerator->currencyId) && ($field === 'productid' || $field === 'serviceid')) {
			$queryGenerator->setCustomColumn('vtiger_pricebookproductrel.listprice');
			$queryGenerator->addJoin(['LEFT JOIN', 'vtiger_pricebookproductrel', 'vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid']);
			$queryGenerator->addNativeCondition(['and',
				['vtiger_pricebook.currency_id' => $queryGenerator->currencyId],
				['vtiger_pricebook.active' => 1],
				['vtiger_pricebookproductrel.productid' => $record],
			]);
		}
	}

	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalRecordsListFields(\App\QueryGenerator $queryGenerator, $sourceModule = false)
	{
		$popupFields = parent::getModalRecordsListFields($queryGenerator, $sourceModule);
		if (!isset($popupFields['currency_id'])) {
			$fieldModel = Vtiger_Field_Model::getInstance('currency_id', $this);
			if ($fieldModel->getPermissions()) {
				$queryGenerator->setField('currency_id');
			}
		}
		return $popupFields;
	}
}
