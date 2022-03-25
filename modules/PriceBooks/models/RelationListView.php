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

class PriceBooks_RelationListView_Model extends Vtiger_RelationListView_Model
{
	/** {@inheritdoc} */
	public function getHeaders()
	{
		$headerFields = parent::getHeaders();
		if ('Services' === $this->getRelatedModuleModel()->getName() || 'Products' === $this->getRelatedModuleModel()->getName()) {
			//Added to support List Price
			$field = new Vtiger_Field_Model();
			$field->set('name', 'listprice');
			$field->set('column', 'listprice');
			$field->set('label', 'List Price');
			$field->set('typeofdata', 'N~O');
			$field->set('isEditable', true);
			$field->set('fromOutsideList', true);
			$field->set('maximumlength', '99999999999999999999');
			$field->set('class', 'validate[required,funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]');
			$headerFields['listprice'] = $field;
		}
		return $headerFields;
	}

	/** {@inheritdoc} */
	public function getLinks(): array
	{
		$relatedLink = parent::getLinks();
		if (!$this->getParentRecordModel()->isReadOnly()) {
			$relatedLink['RELATEDLIST_MASSACTIONS'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'RELATEDLIST_MASSACTIONS',
				'linklabel' => 'LBL_SPECIFY_THE_MARGINP',
				'linkurl' => 'javascript:PriceBooks_RelatedList_Js.triggerMassMargin()',
				'linkclass' => '',
				'linkicon' => 'fas fa-hand-holding-usd',
			]);
		}
		return $relatedLink;
	}
}
