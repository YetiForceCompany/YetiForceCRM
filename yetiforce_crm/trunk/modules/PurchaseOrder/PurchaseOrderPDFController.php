<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'include/InventoryPDFController.php';

class Vtiger_PurchaseOrderPDFController extends Vtiger_InventoryPDFController{
	function buildHeaderModelTitle() {
		$singularModuleNameKey = 'SINGLE_'.$this->moduleName;
		$translatedSingularModuleLabel = getTranslatedString($singularModuleNameKey, $this->moduleName);
		if($translatedSingularModuleLabel == $singularModuleNameKey) {
			$translatedSingularModuleLabel = getTranslatedString($this->moduleName, $this->moduleName);
		}
		return sprintf("%s: %s", $translatedSingularModuleLabel, $this->focusColumnValue('purchaseorder_no'));
	}

	function buildHeaderModelColumnCenter() {
		$contactName = $this->resolveReferenceLabel($this->focusColumnValue('contact_id'), 'Contacts');
		$vendorName = $this->resolveReferenceLabel($this->focusColumnValue('vendor_id'), 'Vendors');
		$trackingNumber = $this->focusColumnValue('tracking_no');
		$requisitionNumber = $this->focusColumnValue('requisition_no');

		$contactNameLabel = getTranslatedString('Contact Name', $this->moduleName);
		$vendorNameLabel = getTranslatedString('Vendor Name', $this->moduleName);
		$trackingNumberLabel = getTranslatedString('Tracking Number', $this->moduleName);
		$requisitionNumberLabel = getTranslatedString('Requisition Number', $this->moduleName);

		$modelColumn1 = array(
				$contactNameLabel	=>	$contactName,
				$vendorNameLabel	=>	$vendorName,
				$trackingNumberLabel=>	$trackingNumber,
				$requisitionNumberLabel=>$requisitionNumber
			
			);
		return $modelColumn1;
	}

	function buildHeaderModelColumnRight() {
		$issueDateLabel = getTranslatedString('Issued Date', $this->moduleName);
		$validDateLabel = getTranslatedString('Due Date', $this->moduleName);
		$billingAddressLabel = getTranslatedString('Billing Address', $this->moduleName);
		$shippingAddressLabel = getTranslatedString('Shipping Address', $this->moduleName);

		$modelColumn2 = array(
				'dates' => array(
					$issueDateLabel  => $this->formatDate(date("Y-m-d")),
					$validDateLabel => $this->formatDate($this->focusColumnValue('duedate')),
				),
				$billingAddressLabel  => $this->buildHeaderBillingAddress(),
				$shippingAddressLabel => $this->buildHeaderShippingAddress()
			);
		return $modelColumn2;
	}

	function getWatermarkContent() {
		return $this->focusColumnValue('postatus');
	}
}
?>