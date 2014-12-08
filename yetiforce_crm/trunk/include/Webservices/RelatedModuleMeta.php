<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/

/**
 * Description of RelatedModuleMeta
 * TODO to add and extend a way to track many-many and many-one relationships.
 * @author MAK
 */
class RelatedModuleMeta {
	private $module;
	private $relatedModule;
	private $CAMPAIGNCONTACTREL = 1;
	private $PRODUCTQUOTESREL = 2;
	private $PRODUCTINVOICEREL = 3;
	private $PRODUCTPURCHASEORDERREL = 4;
	
	private function  __construct($module, $relatedModule) {
		$this->module = $module;
		$this->relatedModule = $relatedModule;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $relatedModule
	 * @return RelatedModuleMeta 
	 */
	public static function getInstance($module, $relatedModule) {
		return new RelatedModuleMeta($module, $relatedModule);
	}

	public function getRelationMeta() {
		$campaignContactRel = array('Campaigns','Contacts');
		$productInvoiceRel = array('Products','Invoice');
		$productQuotesRel = array('Products','Quotes');
		$productPurchaseOrder = array('Products','PurchaseOrder');
		if(in_array($this->module, $campaignContactRel) && in_array($this->relatedModule,
				$campaignContactRel)) {
			return $this->getRelationMetaInfo($this->CAMPAIGNCONTACTREL);
		}
		if(in_array($this->module, $productInvoiceRel) && in_array($this->relatedModule,
				$productInvoiceRel)) {
			return $this->getRelationMetaInfo($this->PRODUCTINVOICEREL);
		}
		if(in_array($this->module, $productQuotesRel) && in_array($this->relatedModule,
				$productQuotesRel)) {
			return $this->getRelationMetaInfo($this->PRODUCTQUOTESREL);
		}
		if(in_array($this->module, $productPurchaseOrder) && in_array($this->relatedModule,
				$productPurchaseOrder)) {
			return $this->getRelationMetaInfo($this->PRODUCTPURCHASEORDERREL);
		}
	}

	private function getRelationMetaInfo($relationId) {
		switch($relationId) {
			case $this->CAMPAIGNCONTACTREL: return array(
					'relationTable' => 'vtiger_campaigncontrel',
					'Campaigns' => 'campaignid',
					'Contacts' => 'contactid'
				);
			case $this->PRODUCTINVOICEREL: return array(
					'relationTable' => 'vtiger_inventoryproductrel',
					'Products' => 'productid',
					'Invoice' => 'id'
				);
			case $this->PRODUCTQUOTESREL: return array(
					'relationTable' => 'vtiger_inventoryproductrel',
					'Products' => 'productid',
					'Quotes' => 'id'
				);
			case $this->PRODUCTPURCHASEORDERREL: return array(
					'relationTable' => 'vtiger_inventoryproductrel',
					'Products' => 'productid',
					'PurchaseOrder' => 'id'
				);
		}
	}
}
?>