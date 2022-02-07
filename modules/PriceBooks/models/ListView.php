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

class PriceBooks_ListView_Model extends Vtiger_ListView_Model
{
	/** {@inheritdoc} */
	public function getListViewEntries(Vtiger_Paging_Model $pagingModel)
	{
		if ($currencyId = $this->get('currency_id')) {
			$this->getQueryGenerator()->currencyId = $currencyId;
		}
		return parent::getListViewEntries($pagingModel);
	}

	/** {@inheritdoc} */
	public function getListViewHeaders()
	{
		$headerFields = parent::getListViewHeaders();
		if ($this->get('currency_id')) {
			$field = new Vtiger_Field_Model();
			$field->set('name', 'listprice');
			$field->set('column', 'listprice');
			$field->set('label', 'List Price');
			$field->set('typeofdata', 'N~O');
			$field->set('fromOutsideList', true);
			$headerFields['listprice'] = $field;
		}
		return $headerFields;
	}
}
