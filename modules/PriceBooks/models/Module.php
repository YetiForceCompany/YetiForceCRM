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
	 * Function to get list view query for popup window
	 * @param string $sourceModule Parent module
	 * @param string $field parent fieldname
	 * @param string $record parent id
	 * @param \App\QueryGenerator $queryGenerator
	 * @param integer $currencyId
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator, $currencyId = false)
	{
		$relatedModulesList = array('Products', 'Services');
		if (in_array($sourceModule, $relatedModulesList)) {
			$condition = [];
			if ($currencyId && in_array($field, array('productid', 'serviceid'))) {
				$subQuery = (new App\Db\Query())->select(['pricebookid'])
					->from('vtiger_pricebookproductrel')
					->where(['productid' => $record]);
				$condition = ['and', ['not in', 'vtiger_pricebook.pricebookid', $subQuery], ['vtiger_pricebook.currency_id' => $currencyId], ['vtiger_pricebook.active' => 1]];
			} else if ($field == 'productsRelatedList') {
				$subQuery = (new App\Db\Query())->select(['pricebookid'])
					->from('vtiger_pricebookproductrel')
					->where(['productid' => $record]);
				$condition = ['and', ['not in', 'vtiger_pricebook.pricebookid', $subQuery], ['vtiger_pricebook.active' => 1]];
			}
			$queryGenerator->addNativeCondition($condition);
		}
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return boolean - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/**
	 * Function to get popup view fields
	 * @param string|boolean $sourceModule
	 * @return string[]
	 */
	public function getPopupViewFieldsList($sourceModule = false)
	{
		$popupFields = parent::getPopupViewFieldsList($sourceModule);
		if (!isset($popupFields['currency_id'])) {
			$fieldModel = Vtiger_Field_Model::getInstance('currency_id', $this);
			if ($fieldModel->getPermissions()) {
				$popupFields['currency_id'] = 'currency_id';
			}
		}
		return $popupFields;
	}
}
