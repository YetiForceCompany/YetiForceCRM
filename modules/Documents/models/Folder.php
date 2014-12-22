<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_Folder_Model extends Vtiger_Base_Model {

	/**
	 * Function returns duplicate record status of the module
	 * @return true if duplicate records exists else false
	 */
	public function checkDuplicate() {
		$db = PearDatabase::getInstance();
		$folderName = $this->getName();

		$result = $db->pquery("SELECT 1 FROM vtiger_attachmentsfolder WHERE foldername = ?", array($folderName));
		$num_rows = $db->num_rows($result);
		if ($num_rows > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Function returns whether documents are exist or not in that folder
	 * @return true if exists else false
	 */
	public function hasDocuments() {
		$db = PearDatabase::getInstance();
		$folderId = $this->getId();

		$result = $db->pquery("SELECT 1 FROM vtiger_notes
						INNER JOIN vtiger_attachmentsfolder ON vtiger_attachmentsfolder.folderid = vtiger_notes.folderid
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_notes.notesid
						WHERE vtiger_attachmentsfolder.folderid = ?
						AND vtiger_attachmentsfolder.foldername != 'Default'
						AND vtiger_crmentity.deleted = 0", array($folderId));
		$num_rows = $db->num_rows($result);
		if ($num_rows>0) {
			return true;
		}
		return false;
	}

	/**
	 * Function to add the new folder
	 * @return Documents_Folder_Model
	 */
	public function save() {
		$db = PearDatabase::getInstance();
		$folderName = $this->getName();
		$folderDesc = $this->get('description');

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currentUserId = $currentUserModel->getId();

		$result = $db->pquery("SELECT max(sequence) AS max, max(folderid) AS max_folderid FROM vtiger_attachmentsfolder", array());
		$sequence = $db->query_result($result, 0, 'max') + 1;
		$folderId = $db->query_result($result,0,'max_folderid') + 1;
		$params = array($folderId,$folderName, $folderDesc, $currentUserId, $sequence);

		$db->pquery("INSERT INTO vtiger_attachmentsfolder(folderid,foldername, description, createdby, sequence) VALUES(?, ?, ?, ?, ?)", $params);
		
		$this->set('sequence', $sequence);
		$this->set('createdby', $currentUserId);
		$this->set('folderid',$folderId);
		
		return $this;
	}

	/**
	 * Function to delete existing folder
	 * @return Documents_Folder_Model
	 */
	public function delete() {
		$db = PearDatabase::getInstance();
		$folderId = $this->getId();
		$result = $db->pquery("DELETE FROM vtiger_attachmentsfolder WHERE folderid = ? AND foldername != 'Default'", array($folderId));
		return $this;
	}

	/**
	 * Function return an instance of Folder Model
	 * @return Documents_Folder_Model
	 */
	public static function getInstance() {
		return new self();
	}

	/**
	 * Function returns an instance of Folder Model
	 * @param foldername
	 * @return Documents_Folder_Model
	 */
	public static function getInstanceById($folderId) {
		$db = PearDatabase::getInstance();
		$folderModel = Documents_Folder_Model::getInstance();

		$result = $db->pquery("SELECT * FROM vtiger_attachmentsfolder WHERE folderid = ?", array($folderId));
		$num_rows = $db->num_rows($result);
		if ($num_rows > 0) {
			$values = $db->query_result_rowdata($result, 0);
			$folderModel->setData($values);
		}
		return $folderModel;
	}

	/**
	 * Function returns an instance of Folder Model
	 * @param <Array> row
	 * @return Documents_Folder_Model
	 */
	public static function getInstanceByArray($row) {
		$folderModel = Documents_Folder_Model::getInstance();
		return $folderModel->setData($row);
	}

	/**
	 * Function returns Folder's Delete url
	 * @return <String> - Delete Url
	 */
	public function getDeleteUrl() {
		$folderName = $this->getName();
		return "index.php?module=Documents&action=Folder&mode=delete&foldername=$folderName";
	}

	/**
	 * Function to get the id of the folder
	 * @return <Number>
	 */
	public function getId() {
		return $this->get('folderid');
	}

	/**
	 * Function to get the name of the folder
	 * @return <String>
	 */
	public function getName() {
		return $this->get('foldername');
	}

	/**
	 * Function to get the description of the folder
	 * @return <String>
	 */
	function getDescription() {
		return $this->get('description');
	}
	
	/**
	 * Function to get info array while saving a folder
	 * @return Array  info array
	 */
	public function getInfoArray() {
		return array('folderName' => $this->getName(),'folderid' => $this->getId());
	}

}
?>
