<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

require_once('include/database/PearDatabase.php');
require_once("modules/Users/Users.php");
require_once("include/Webservices/WebServiceError.php");
require_once 'include/utils/utils.php';
require_once 'include/utils/UserInfoUtil.php';
require_once 'include/utils/VtlibUtils.php';

class WebservicesUtils
{

	/**
	 * Function used to get the lead related Notes and Attachments with other entities Account, Contact and Potential
	 * @param integer $id - leadid
	 * @param integer $relatedId -  related entity id (accountid / contactid)
	 */
	public static function vtws_getRelatedNotesAttachments($id, $relatedId)
	{
		$adb = PearDatabase::getInstance();
		$db = \App\Db::getInstance();

		$sql = 'SELECT notesid FROM vtiger_senotesrel WHERE crmid=?';
		$result = $adb->pquery($sql, [$id]);
		if (!$result->rowCount()) {
			return false;
		}
		while ($noteId = $adb->getSingleValue($result)) {
			$db->createCommand()->insert('vtiger_senotesrel', ['crmid' => $relatedId, 'notesid' => $noteId])->execute();
		}

		$sql = 'SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid=?';
		$result = $adb->pquery($sql, [$id]);
		if (!$result->rowCount()) {
			return false;
		}
		while ($attachmentId = $adb->getSingleValue($result)) {
			$db->createCommand()->insert('vtiger_seattachmentsrel', ['crmid' => $relatedId, 'attachmentsid' => $attachmentId])->execute();
		}
		return true;
	}

	/**
	 * Function used to save the lead related products with other entities Account, Contact and Potential
	 * $leadid - leadid
	 * $relatedid - related entity id (accountid/contactid/potentialid)
	 * $setype - related module(Accounts/Contacts)
	 */
	public static function vtws_saveLeadRelatedProducts($leadId, $relatedId, $setype)
	{
		$db = \App\Db::getInstance();
		$dataReader = (new \App\Db\Query())->select(['productid'])
				->from('vtiger_seproductsrel')
				->where(['crmid' => $leadId])
				->createCommand()->query();
		if ($dataReader->count() === 0) {
			return false;
		}
		while ($productId = $dataReader->readColumn(0)) {
			$resultNew = $db->createCommand()->insert('vtiger_seproductsrel', [
					'crmid' => $relatedId,
					'productid' => $productId,
					'setype' => $setype,
					'rel_created_user' => \App\User::getCurrentUserId(),
					'rel_created_time' => date('Y-m-d H:i:s')
				])->execute();
			if ($resultNew === 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Function used to save the lead related services with other entities Account, Contact and Potential
	 * $leadid - leadid
	 * $relatedid - related entity id (accountid/contactid/potentialid)
	 * $setype - related module(Accounts/Contacts)
	 */
	public static function vtws_saveLeadRelations($leadId, $relatedId, $setype)
	{
		$db = \App\Db::getInstance();
		$dataReader = (new App\Db\Query())->from('vtiger_crmentityrel')->where(['crmid' => $leadId])
				->createCommand()->query();
		if ($dataReader->count() === 0) {
			return false;
		}
		while ($row = $dataReader->read()) {
			$resultNew = $db->createCommand()->insert('vtiger_crmentityrel', [
					'crmid' => $relatedId,
					'module' => $setype,
					'relcrmid' => $row['relcrmid'],
					'relmodule' => $row['relmodule']
				])->execute();
			if ($resultNew === 0) {
				return false;
			}
		}
		$dataReader = (new App\Db\Query())->from('vtiger_crmentityrel')->where(['relcrmid' => $leadId])
				->createCommand()->query();
		if ($dataReader->count() === 0) {
			return false;
		}
		while ($row = $dataReader->read()) {
			$resultNew = $db->createCommand()->insert('vtiger_crmentityrel', [
					'crmid' => $relatedId,
					'module' => $setype,
					'relcrmid' => $row['crmid'],
					'relmodule' => $row['module']
				])->execute();
			if ($resultNew === 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 * vtws_getFieldfromFieldId
	 * @param int $fieldId
	 * @param Vtiger_Module_Model $moduleModel
	 * @return null|Vtiger_Field_Model
	 */
	public static function vtws_getFieldfromFieldId($fieldId, Vtiger_Module_Model $moduleModel)
	{
		foreach ($moduleModel->getFields() as $field) {
			if ($fieldId == $field->getId()) {
				return $field;
			}
		}
		return null;
	}

	/**
	 * Function used to get the lead related activities with other entities Account and Contact
	 * @param integer $leadId - lead entity id
	 * @param integer $accountId - related account id
	 * @param integer $contactId -  related contact id
	 * @param integer $relatedId - related entity id to which the records need to be transferred
	 */
	public static function vtws_getRelatedActivities($leadId, $accountId, $contactId, $relatedId)
	{

		if (empty($leadId) || empty($relatedId) || (empty($accountId) && empty($contactId))) {
			throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED, "Failed to move related Activities/Emails");
		}
		$db = \App\Db::getInstance();
		if (!empty($accountId)) {
			$db->createCommand()->update('vtiger_activity', ['link' => $accountId], ['link' => $leadId])->execute();
		}
		if (!empty($contactId)) {
			$db->createCommand()->update('vtiger_activity', ['link' => $contactId], ['link' => $leadId])->execute();
		}
		return true;
	}

	/**
	 * Function used to save the lead related Campaigns with Contact
	 * @param $leadid - leadid
	 * @param $relatedid - related entity id (contactid/accountid)
	 * @return Boolean true on success, false otherwise.
	 */
	public static function vtws_saveLeadRelatedCampaigns($leadId, $relatedId)
	{
		$db = \App\Db::getInstance();
		$rowCount = $db->createCommand()->update('vtiger_campaign_records', [
				'crmid' => $relatedId
				], ['crmid' => $leadId]
			)->execute();
		if ($rowCount == 0) {
			return false;
		}
		return true;
	}

	/**
	 * Function used to transfer all the lead related records to given Entity(Contact/Account) record
	 * @param $leadid - leadid
	 * @param $relatedid - related entity id (contactid/accountid)
	 * @param $setype - related module(Accounts/Contacts)
	 */
	public static function vtws_transferLeadRelatedRecords($leadId, $relatedId, $seType)
	{

		if (empty($leadId) || empty($relatedId) || empty($seType)) {
			throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED, "Failed to move related Records");
		}
		static::vtws_getRelatedNotesAttachments($leadId, $relatedId);
		static::vtws_saveLeadRelatedProducts($leadId, $relatedId, $seType);
		static::vtws_saveLeadRelations($leadId, $relatedId, $seType);
		static::vtws_saveLeadRelatedCampaigns($leadId, $relatedId);
		static::vtws_transferComments($leadId, $relatedId);
		static::vtws_transferRelatedRecords($leadId, $relatedId);
	}

	/**
	 * The function transfers the comments
	 * @param int $sourceRecordId
	 * @param int $destinationRecordId
	 */
	public static function vtws_transferComments($sourceRecordId, $destinationRecordId)
	{
		if (\App\Module::isModuleActive('ModComments')) {
			CRMEntity::getInstance('ModComments');
			ModComments::transferRecords($sourceRecordId, $destinationRecordId);
		}
	}

	/**
	 * The function transfers related records
	 * @param int $sourceRecordId
	 * @param int $destinationRecordId
	 */
	public static function vtws_transferRelatedRecords($sourceRecordId, $destinationRecordId)
	{
		$db = \App\Db::getInstance();
		//PBXManager
		$db->createCommand()->update('vtiger_pbxmanager', ['customer' => $destinationRecordId], ['customer' => $sourceRecordId])->execute();
		//OSSPasswords
		$db->createCommand()->update('vtiger_osspasswords', ['linkto' => $destinationRecordId], ['linkto' => $sourceRecordId])->execute();
		//Contacts
		$db->createCommand()->update('vtiger_contactdetails', ['parentid' => $destinationRecordId], ['parentid' => $sourceRecordId])->execute();
		//OutsourcedProducts
		$db->createCommand()->update('vtiger_outsourcedproducts', ['parent_id' => $destinationRecordId], ['parent_id' => $sourceRecordId])->execute();
		//OSSOutsourcedServices
		$db->createCommand()->update('vtiger_ossoutsourcedservices', ['parent_id' => $destinationRecordId], ['parent_id' => $sourceRecordId])->execute();
		//OSSTimeControl
		$db->createCommand()->update('vtiger_osstimecontrol', ['link' => $destinationRecordId], ['link' => $sourceRecordId])->execute();
		//OSSMailView
		$db->createCommand()->update('vtiger_ossmailview_relation', ['crmid' => $destinationRecordId], ['crmid' => $sourceRecordId])->execute();
		//CallHistory
		$db->createCommand()->update('vtiger_callhistory', ['destination' => $destinationRecordId], ['destination' => $sourceRecordId])->execute();
		//LettersIn
		$db->createCommand()->update('vtiger_lettersin', ['relatedid' => $destinationRecordId], ['relatedid' => $sourceRecordId])->execute();
		//LettersOut
		$db->createCommand()->update('vtiger_lettersout', ['relatedid' => $destinationRecordId], ['relatedid' => $sourceRecordId])->execute();
	}
}
