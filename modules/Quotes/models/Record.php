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
 * Quotes Record Model Class
 */
class Quotes_Record_Model extends Inventory_Record_Model {

	public function getCreateInvoiceUrl() {
		$invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');

		return "index.php?module=".$invoiceModuleModel->getName()."&view=".$invoiceModuleModel->getEditViewName()."&quote_id=".$this->getId();
	}

	public function getCreateSalesOrderUrl() {
		$salesOrderModuleModel = Vtiger_Module_Model::getInstance('SalesOrder');

		return "index.php?module=".$salesOrderModuleModel->getName()."&view=".$salesOrderModuleModel->getEditViewName()."&quote_id=".$this->getId();
	}

	/**
	 * Function to get this record and details as PDF
	 */
	public function getPDF() {
		$recordId = $this->getId();
		$moduleName = $this->getModuleName();

		$controller = new Vtiger_QuotePDFController($moduleName);
		$controller->loadRecord($recordId);

		$fileName = $moduleName.'_'.getModuleSequenceNumber($moduleName, $recordId);
		$controller->Output($fileName.'.pdf', 'D');
	}

}