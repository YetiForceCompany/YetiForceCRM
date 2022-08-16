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

require_once 'modules/Users/Users.php';
require_once 'include/Webservices/WebServiceError.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'include/Webservices/Utils.php';
require_once 'include/utils/VtlibUtils.php';

class WebservicesUtils
{
	/**
	 * Function used to get the lead related Notes and Attachments with other entities Account, Contact and Potential.
	 *
	 * @param int $id        - leadid
	 * @param int $relatedId -  related entity id (accountid / contactid)
	 */
	public static function vtwsGetRelatedNotesAttachments($id, $relatedId)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = (new \App\Db\Query())->select(['notesid'])->from('vtiger_senotesrel')->where(['crmid' => $id])->createCommand()->query();
		if (!$dataReader->count()) {
			return false;
		}
		while ($row = $dataReader->readColumn(0)) {
			$dbCommand->insert('vtiger_senotesrel', ['crmid' => $relatedId, 'notesid' => $row])->execute();
		}
		$dataReader->close();

		$dataReader = (new \App\Db\Query())->select(['attachmentsid'])->from('vtiger_seattachmentsrel')->where(['crmid' => $id])->createCommand()->query();
		if (!$dataReader->count()) {
			return false;
		}
		while ($row = $dataReader->readColumn(0)) {
			$dbCommand->insert('vtiger_seattachmentsrel', ['crmid' => $relatedId, 'attachmentsid' => $row])->execute();
		}
		$dataReader->close();

		return true;
	}

	/**
	 * Function used to save the lead related products with other entities Account, Contact and Potential.
	 *
	 * @param int    $leadId
	 * @param int    $relatedId - related entity id (accountid/contactid/potentialid)
	 * @param string $setype    - related module(Accounts/Contacts).
	 */
	public static function vtwsSaveLeadRelatedProducts($leadId, $relatedId, $setype)
	{
		$db = \App\Db::getInstance();
		$dataReader = (new \App\Db\Query())->select(['productid'])
			->from('vtiger_seproductsrel')
			->where(['crmid' => $leadId])
			->createCommand()->query();
		if (0 === $dataReader->count()) {
			return false;
		}
		while ($productId = $dataReader->readColumn(0)) {
			$resultNew = $db->createCommand()->insert('vtiger_seproductsrel', [
				'crmid' => $relatedId,
				'productid' => $productId,
				'setype' => $setype,
				'rel_created_user' => \App\User::getCurrentUserId(),
				'rel_created_time' => date('Y-m-d H:i:s'),
			])->execute();
			if (0 === $resultNew) {
				return false;
			}
		}
		$dataReader->close();

		return true;
	}

	/**
	 * Function used to save the lead related services with other entities Account, Contact and Potential.
	 *
	 * @param int    $leadId
	 * @param int    $relatedId - related entity id (accountid/contactid/potentialid)
	 * @param string $setype    - related module(Accounts/Contacts).
	 */
	public static function vtwsSaveLeadRelations($leadId, $relatedId, $setype)
	{
		$db = \App\Db::getInstance();
		$dataReader = (new App\Db\Query())->from('vtiger_crmentityrel')->where(['crmid' => $leadId])
			->createCommand()->query();
		if (0 === $dataReader->count()) {
			return false;
		}
		while ($row = $dataReader->read()) {
			$resultNew = $db->createCommand()->insert('vtiger_crmentityrel', [
				'crmid' => $relatedId,
				'module' => $setype,
				'relcrmid' => $row['relcrmid'],
				'relmodule' => $row['relmodule'],
			])->execute();
			if (0 === $resultNew) {
				return false;
			}
		}
		$dataReader->close();
		$dataReader = (new App\Db\Query())->from('vtiger_crmentityrel')->where(['relcrmid' => $leadId])
			->createCommand()->query();
		if (0 === $dataReader->count()) {
			return false;
		}
		while ($row = $dataReader->read()) {
			$resultNew = $db->createCommand()->insert('vtiger_crmentityrel', [
				'crmid' => $relatedId,
				'module' => $setype,
				'relcrmid' => $row['crmid'],
				'relmodule' => $row['module'],
			])->execute();
			if (0 === $resultNew) {
				return false;
			}
		}
		$dataReader->close();

		return true;
	}

	/**
	 * vtwsGetFieldfromFieldId.
	 *
	 * @param int                 $fieldId
	 * @param Vtiger_Module_Model $moduleModel
	 *
	 * @return Vtiger_Field_Model|null
	 */
	public static function vtwsGetFieldfromFieldId($fieldId, Vtiger_Module_Model $moduleModel)
	{
		foreach ($moduleModel->getFields() as $field) {
			if ($fieldId == $field->getId()) {
				return $field;
			}
		}

		return null;
	}

	/**
	 * Function used to get the lead related activities with other entities Account and Contact.
	 *
	 * @param int $leadId    - lead entity id
	 * @param int $accountId - related account id
	 * @param int $contactId -  related contact id
	 * @param int $relatedId - related entity id to which the records need to be transferred
	 */
	public static function vtwsGetRelatedActivities($leadId, $accountId, $contactId, $relatedId)
	{
		if (empty($leadId) || empty($relatedId) || (empty($accountId) && empty($contactId))) {
			throw new WebServiceException('LEAD_RELATEDLIST_UPDATE_FAILED', 'Failed to move related Activities/Emails');
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
	 * Function used to save the lead related Campaigns with Contact.
	 *
	 * @param int $leadId
	 * @param int $relatedId - related entity id (contactid/accountid)
	 *
	 * @return bool true on success, false otherwise
	 */
	public static function vtwsSaveLeadRelatedCampaigns($leadId, $relatedId)
	{
		$db = \App\Db::getInstance();
		$rowCount = $db->createCommand()->update('vtiger_campaign_records', [
			'crmid' => $relatedId,
		], ['crmid' => $leadId]
			)->execute();
		if (0 == $rowCount) {
			return false;
		}

		return true;
	}

	/**
	 * Function used to transfer all the lead related records to given Entity(Contact/Account) record.
	 *
	 * @param int    $leadId
	 * @param int    $relatedId - related entity id (contactid/accountid)
	 * @param string $seType    - related module(Accounts/Contacts)
	 */
	public static function vtwsTransferLeadRelatedRecords($leadId, $relatedId, $seType)
	{
		if (empty($leadId) || empty($relatedId) || empty($seType)) {
			throw new WebServiceException('LEAD_RELATEDLIST_UPDATE_FAILED', 'Failed to move related Records');
		}
		static::vtwsGetRelatedNotesAttachments($leadId, $relatedId);
		static::vtwsSaveLeadRelatedProducts($leadId, $relatedId, $seType);
		static::vtwsSaveLeadRelations($leadId, $relatedId, $seType);
		static::vtwsSaveLeadRelatedCampaigns($leadId, $relatedId);
		static::vtwsTransferComments($leadId, $relatedId);
		static::vtwsTransferRelatedRecords($leadId, $relatedId);
	}

	/**
	 * The function transfers the comments.
	 *
	 * @param int $sourceRecordId
	 * @param int $destinationRecordId
	 */
	public static function vtwsTransferComments($sourceRecordId, $destinationRecordId)
	{
		if (\App\Module::isModuleActive('ModComments')) {
			CRMEntity::getInstance('ModComments');
			ModComments::transferRecords($sourceRecordId, $destinationRecordId);
		}
	}

	/**
	 * The function transfers related records.
	 *
	 * @param int $sourceRecordId
	 * @param int $destinationRecordId
	 */
	public static function vtwsTransferRelatedRecords($sourceRecordId, $destinationRecordId)
	{
		$db = \App\Db::getInstance();
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
		//SQuoteEnquiries
		$db->createCommand()->update('u_yf_squoteenquiries', ['accountid' => $destinationRecordId], ['accountid' => $sourceRecordId])->execute();
		//Reservations
		$db->createCommand()->update('vtiger_reservations', ['link' => $destinationRecordId], ['link' => $sourceRecordId])->execute();
	}
}
