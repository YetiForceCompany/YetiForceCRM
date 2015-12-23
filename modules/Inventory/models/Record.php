<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Inventory Record Model Class
 */
class Inventory_Record_Model extends Vtiger_Record_Model {

	function getCurrencyInfo() {
		$moduleName = $this->getModuleName();
		$currencyInfo = getInventoryCurrencyInfo($moduleName, $this->getId());
		return $currencyInfo;
	}

	function getProductTaxes() {
		$taxDetails = $this->get('taxDetails');
		if ($taxDetails) {
			return $taxDetails;
		}

		$record = $this->getId();
		if ($record) {
			$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
			$taxDetails = $relatedProducts[1]['final_details']['taxes'];
		} else {
			$taxDetails = getAllTaxes('available', '', $this->getEntity()->mode, $this->getId());
		}

		$this->set('taxDetails', $taxDetails);
		return $taxDetails;
	}

	function getProducts() {
		$numOfCurrencyDecimalPlaces = getCurrencyDecimalPlaces(); 
		$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
		$relatedProducts[1]['final_details']['grandTotal'] = number_format($this->get('hdnGrandTotal'), $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['total_purchase'] = number_format($this->get('total_purchase'), $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['total_margin'] = number_format($this->get('total_margin'), $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['total_marginp'] = number_format($this->get('total_marginp'), $numOfCurrencyDecimalPlaces,'.','');

		//Updating Pre tax total
		$preTaxTotal = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
						- (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['preTaxTotal'] = number_format($preTaxTotal, $numOfCurrencyDecimalPlaces,'.','');
		
		//Updating Total After Discount
		$totalAfterDiscount = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
								- (float)$relatedProducts[1]['final_details']['discountTotal_final'];
		
		$relatedProducts[1]['final_details']['totalAfterDiscount'] = number_format($totalAfterDiscount, $numOfCurrencyDecimalPlaces,'.','');
		return $relatedProducts;
	}

	/**
	 * Function to get inventoy terms and conditions
	 * @return <String>
	 */
	function getInventoryTermsandConditions() {
		return getTermsandConditions();
	}

	/**
	 * Function to set data of parent record model to this record
	 * @param Vtiger_Record_Model $parentRecordModel
	 * @return Inventory_Record_Model
	 */
	public function setParentRecordData(Vtiger_Record_Model $parentRecordModel) {
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = $parentRecordModel->getModuleName();

		$data = array();
		$fieldMappingList = $parentRecordModel->getInventoryMappingFields();

		foreach ($fieldMappingList as $fieldMapping) {
			$parentField = $fieldMapping['parentField'];
			$inventoryField = $fieldMapping['inventoryField'];
            $fieldModel = Vtiger_Field_Model::getInstance($parentField,  Vtiger_Module_Model::getInstance($moduleName));
			if ($fieldModel->getPermissions()) {
				$data[$inventoryField] = $parentRecordModel->get($parentField);
			} else {
				$data[$inventoryField] = $fieldMapping['defaultValue'];
			}
		}
		return $this->setData($data);
	}

	/**
	 * Function to get URL for Export the record as PDF
	 * @return <type>
	 */
	public function getExportPDFUrl() {
		return "index.php?module=".$this->getModuleName()."&action=ExportPDF&record=".$this->getId();
	}

	/**
	  * Function to get the send email pdf url
	  * @return <string>
	  */
    public function getSendEmailPDFUrl() {
        return 'module='.$this->getModuleName().'&view=SendEmail&mode=composeMailData&record='.$this->getId();
    }
    
    /**
	 * Function to get this record and details as PDF
	 */
	public function getPDF() {
		$recordId = $this->getId();
		$moduleName = $this->getModuleName();

		$controllerClassName = "Vtiger_". $moduleName ."PDFController";

		$controller = new $controllerClassName($moduleName);
		$controller->loadRecord($recordId);

		$fileName = $moduleName.'_'.getModuleSequenceNumber($moduleName, $recordId);
		$controller->Output($fileName.'.pdf', 'D');
	}

    /**
     * Function to get the pdf file name . This will conver the invoice in to pdf and saves the file
     * @return <String>
     *
     */
    public function getPDFFileName() {
        $moduleName = $this->getModuleName();
		vimport("~modules/$moduleName/$moduleName" . "PDFController.php");
		$controllerClassName = "Vtiger_" . $moduleName . "PDFController";

		$recordId = $this->getId();
		$controller = new $controllerClassName($moduleName);
        $controller->loadRecord($recordId);

        $sequenceNo = getModuleSequenceNumber($moduleName,$recordId);
		$translatedName = vtranslate($moduleName, $moduleName);
        $filePath = "storage/$translatedName"."_".$sequenceNo.".pdf";
        //added file name to make it work in IE, also forces the download giving the user the option to save
        $controller->Output($filePath,'F');
        return $filePath;
    }
}
