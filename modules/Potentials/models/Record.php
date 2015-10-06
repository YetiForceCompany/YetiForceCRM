<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Potentials_Record_Model extends Vtiger_Record_Model
{

	function getCreateInvoiceUrl()
	{
		$invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');
		return 'index.php?module=' . $invoiceModuleModel->getName() . '&view=' . $invoiceModuleModel->getEditViewName() . '&account_id=' . $this->get('related_to') .
			'&potentialid=' . $this->getId() . '&sourceModule=' . $this->getModuleName() . '&sourceRecord=' . $this->getId() . '&relationOperation=true';
	}

	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl() . '&process=' . $this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return <String>
	 */
	function getCreateTaskUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl() . '&process=' . $this->getId();
	}

	/**
	 * Function to get List of Fields which are related from Contacts to Inventyory Record
	 * @return <array>
	 */
	public function getInventoryMappingFields()
	{
		return array(
			array('parentField' => 'related_to', 'inventoryField' => 'account_id', 'defaultValue' => ''),
		);
	}

	/**
	 * Function returns the url for create quote
	 * @return <String>
	 */
	public function getCreateQuoteUrl()
	{
		$quoteModuleModel = Vtiger_Module_Model::getInstance('Quotes');
		return $quoteModuleModel->getCreateRecordUrl() . '&sourceRecord=' . $this->getId() . '&sourceModule=' . $this->getModuleName() . '&potential_id=' . $this->getId() . '&relationOperation=true';
	}

	public function recalculatePotentials($PotentialsID)
	{
		$log = vglobal('log');
		$log->debug("Entering recalculatePotentials($PotentialsID) method ...");
		if ($PotentialsID == NULL) {
			return false;
		}
		$CalculationsStatus = 'Rejected';
		$QuotesStatus = 'Rejected';
		$SalesOrderStatus = 'Cancelled';
		$InvoiceStatus = 'Cancel';
		$db = PearDatabase::getInstance();

		$sql = "UPDATE vtiger_potential 
				SET sum_calculations = (SELECT SUM(total) as total FROM vtiger_calculations 
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_calculations.calculationsid
					WHERE deleted = 0 AND calculationsstatus <> ? AND potentialid = vtiger_potential.potentialid), 
				sum_salesorders = (SELECT SUM(total) as total FROM vtiger_salesorder 
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_salesorder.salesorderid
						WHERE deleted = 0 AND sostatus <> ? AND potentialid = vtiger_potential.potentialid), 
				sum_invoices = (SELECT SUM(total) as total FROM vtiger_invoice 
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
						WHERE deleted = 0 AND invoicestatus <> ? AND potentialid = vtiger_potential.potentialid), 
				sum_quotes = (SELECT SUM(total) as total FROM vtiger_quotes 
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_quotes.quoteid
						WHERE deleted = 0 AND quotestage <> ? AND potentialid = vtiger_potential.potentialid),
				average_profit_so = (SELECT ((SUM(total)-SUM(total_purchase))/SUM(total_purchase))*100 AS pro FROM 
				vtiger_salesorder INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_salesorder.salesorderid WHERE deleted = 0 AND sostatus <> 'Cancelled' AND vtiger_salesorder.accountid = vtiger_potential.related_to )
				WHERE potentialid = ?;";
		$db->pquery($sql, array($CalculationsStatus, $SalesOrderStatus, $InvoiceStatus, $QuotesStatus, $PotentialsID), true);
		$log->debug("Exiting recalculatePotentials($PotentialsID) method ...");
	}

	public function createSalesOpportunitiesFromRecords($from_module, $recordIds)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->info("Entering Into createSalesOpportunitiesFromRecords( $from_module, $recordIds )");
		$db = PearDatabase::getInstance();

		$numRecords = 0;
		$modulesSchema = array();
		$modulesSchema['OutsourcedProducts'] = array(
			'potentialId' => 'potential',
			'num' => 'asset_no',
			'closingdate' => 'dateinservice',
			'relateProduct' => false,
			'theSame' => array('potentialname', 'assigned_user_id', 'related_to', 'forecast_amount'),
			'fixed' => array('sales_stage' => 'Accepted for processing', 'opportunity_type' => 'Existing Business'),
		);
		$modulesSchema['Assets'] = array(
			'potentialId' => 'potential',
			'num' => 'asset_no',
			'closingdate' => 'dateinservice',
			'relateProduct' => true,
			'theSame' => array('potentialname', 'assigned_user_id', 'related_to', 'forecast_amount'),
			'fixed' => array('sales_stage' => 'Accepted for processing', 'opportunity_type' => 'Existing Business'),
			'updateField' => array('pot_renewal', 'vtiger_assets', 'assetsid'),
		);
		$modulesSchema['OSSOutsourcedServices'] = array(
			'potentialId' => 'potential',
			'num' => 'osssoldservices_no',
			'closingdate' => 'dateinservice',
			'relateProduct' => false,
			'theSame' => array('potentialname', 'assigned_user_id', 'related_to', 'forecast_amount'),
			'fixed' => array('sales_stage' => 'Accepted for processing', 'opportunity_type' => 'Existing Business'),
		);
		$modulesSchema['OSSSoldServices'] = array(
			'potentialId' => 'potential',
			'num' => 'asset_no',
			'closingdate' => 'dateinservice',
			'relateProduct' => true,
			'theSame' => array('potentialname', 'assigned_user_id', 'related_to', 'forecast_amount'),
			'fixed' => array('sales_stage' => 'Accepted for processing', 'opportunity_type' => 'Existing Business'),
			'updateField' => array('pot_renewal', 'vtiger_osssoldservices', 'osssoldservicesid'),
		);

		if (array_key_exists($from_module, $modulesSchema)) {
			$schema = $modulesSchema[$from_module];
			foreach ($recordIds as $recordId) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $from_module);
				$potentialId = $recordModel->get($schema['potentialId']);
				if ($potentialId != 0 && $potentialId != '') {
					$potentialsRecordModel = Vtiger_Record_Model::getInstanceById($potentialId, 'Potentials');
					$record = Vtiger_Record_Model::getCleanInstance('Potentials');
					foreach ($schema['theSame'] as $name) {
						$record->set($name, $potentialsRecordModel->get($name));
						if ($name == 'assigned_user_id') {
							$assigned_user_id = $potentialsRecordModel->get($name);
							$ownerIdInfo = getRecordOwnerId($assigned_user_id);
							if (!empty($ownerIdInfo['Users'])) {
								$usersPrivileges = Users_Privileges_Model::getInstanceById($assigned_user_id);
								if ($usersPrivileges->status != 'Active') {
									$record->set($name, $current_user->id);
								}
							}
						}
					}
					foreach ($schema['fixed'] as $key => $val) {
						$record->set($key, $val);
					}
					if ($schema['closingdate']) {
						$closingdate = $recordModel->get($schema['closingdate']);
						if (strtotime($closingdate) < strtotime(date("Y-m-d"))) {
							$closingdate = date("Y-m-d", strtotime("+30 day", strtotime(date("Y-m-d"))));
						}
						$record->set('closingdate', $closingdate);
					}
					$record->save();
					if ($record->getId() != '') {
						$numRecords++;
						$newId = $record->getId();
						if (array_key_exists('updateField', $schema)) {
							$updateField = $recordModel->get($schema['updateField'][0]);
							if ($updateField == '' || $updateField == 0) {
								$db->pquery('UPDATE ' . $schema['updateField'][1] . ' SET ' . $schema['updateField'][0] . ' = ? WHERE ' . $schema['updateField'][2] . ' = ?', array($newId, $recordId));
							}
						}
						$product = $recordModel->get('product');
						if ($schema['relateProduct'] && $product != '' && $product != 0) {
							$db->pquery('INSERT INTO vtiger_seproductsrel (crmid, productid, setype) VALUES (?,?,?);', array($newId, $product, 'Potentials'));
						}
						$content = vtranslate('LBL_GENERATING_COMMENT', 'Potentials') . ' ' . vtranslate($from_module, $from_module) . ': ' . $recordModel->get($schema['num']);
						$rekord = Vtiger_Record_Model::getCleanInstance('ModComments');
						$rekord->set('commentcontent', $content);
						$rekord->set('assigned_user_id', $current_user->id);
						$rekord->set('related_to', $newId);
						$rekord->save();
					}
				}
			}
		}
		return $numRecords;
		$log->debug("Exiting createSalesOpportunitiesFromRecords() method ...");
	}
}
