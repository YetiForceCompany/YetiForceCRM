<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	public function getCreateEventUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl() . '&link=' . $this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return <String>
	 */
	public function getCreateTaskUrl()
	{
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl() . '&link=' . $this->getId();
	}

	/**
	 * Function to get List of Fields which are related from Contacts to Inventory Record
	 * @return <array>
	 */
	public function getInventoryMappingFields()
	{
		return array(
			array('parentField' => 'parent_id', 'inventoryField' => 'account_id', 'defaultValue' => ''),
			array('parentField' => 'buildingnumbera', 'inventoryField' => 'buildingnumbera', 'defaultValue' => ''),
			array('parentField' => 'localnumbera', 'inventoryField' => 'localnumbera', 'defaultValue' => ''),
			array('parentField' => 'addresslevel1a', 'inventoryField' => 'addresslevel1a', 'defaultValue' => ''),
			array('parentField' => 'addresslevel2a', 'inventoryField' => 'addresslevel2a', 'defaultValue' => ''),
			array('parentField' => 'addresslevel3a', 'inventoryField' => 'addresslevel3a', 'defaultValue' => ''),
			array('parentField' => 'addresslevel4a', 'inventoryField' => 'addresslevel4a', 'defaultValue' => ''),
			array('parentField' => 'addresslevel5a', 'inventoryField' => 'addresslevel5a', 'defaultValue' => ''),
			array('parentField' => 'addresslevel6a', 'inventoryField' => 'addresslevel6a', 'defaultValue' => ''),
			array('parentField' => 'addresslevel7a', 'inventoryField' => 'addresslevel7a', 'defaultValue' => ''),
			array('parentField' => 'addresslevel8a', 'inventoryField' => 'addresslevel8a', 'defaultValue' => ''),
			array('parentField' => 'buildingnumberb', 'inventoryField' => 'buildingnumberb', 'defaultValue' => ''),
			array('parentField' => 'localnumberb', 'inventoryField' => 'localnumberb', 'defaultValue' => ''),
			array('parentField' => 'addresslevel1b', 'inventoryField' => 'addresslevel1b', 'defaultValue' => ''),
			array('parentField' => 'addresslevel2b', 'inventoryField' => 'addresslevel2b', 'defaultValue' => ''),
			array('parentField' => 'addresslevel3b', 'inventoryField' => 'addresslevel3b', 'defaultValue' => ''),
			array('parentField' => 'addresslevel4b', 'inventoryField' => 'addresslevel4b', 'defaultValue' => ''),
			array('parentField' => 'addresslevel5b', 'inventoryField' => 'addresslevel5b', 'defaultValue' => ''),
			array('parentField' => 'addresslevel6b', 'inventoryField' => 'addresslevel6b', 'defaultValue' => ''),
			array('parentField' => 'addresslevel7b', 'inventoryField' => 'addresslevel7b', 'defaultValue' => ''),
			array('parentField' => 'addresslevel8b', 'inventoryField' => 'addresslevel8b', 'defaultValue' => ''),
		);
	}

	/**
	 * Function to get Image Details
	 * @return <array> Image Details List
	 */
	public function getImageDetails()
	{
		$db = PearDatabase::getInstance();
		$imageDetails = array();
		$recordId = $this->getId();

		if ($recordId) {
			$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
						WHERE vtiger_crmentity.setype = 'Contacts Image' and vtiger_seattachmentsrel.crmid = ?";

			$result = $db->pquery($sql, array($recordId));

			$imageId = $db->query_result($result, 0, 'attachmentsid');
			$imagePath = $db->query_result($result, 0, 'path');
			$imageName = $db->query_result($result, 0, 'name');

			//decode_html - added to handle UTF-8 characters in file names
			$imageOriginalName = decode_html($imageName);

			if (!empty($imageName)) {
				$imageDetails[] = array(
					'id' => $imageId,
					'orgname' => $imageOriginalName,
					'path' => $imagePath . $imageId,
					'name' => $imageName
				);
			}
		}
		return $imageDetails;
	}
}
