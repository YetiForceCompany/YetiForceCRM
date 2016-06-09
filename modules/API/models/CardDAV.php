<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class API_CardDAV_Model
{

	const ADDRESSBOOK_NAME = 'YFAddressBook';
	const PRODID = 'YetiForceCRM';

	public $pdo = false;
	public $log = false;
	public $user = false;
	public $addressBookId = false;
	public $davUsers = [];
	protected $crmRecords = [];
	public $mailFields = [
		'Contacts' => ['email' => 'WORK', 'secondary_email' => 'HOME'],
		'OSSEmployees' => ['business_mail' => 'WORK', 'private_mail' => 'HOME'],
	];
	public $telFields = [
		'Contacts' => ['phone' => 'WORK', 'mobile' => 'CELL'],
		'OSSEmployees' => ['business_phone' => 'WORK', 'private_phone' => 'CELL'],
	];

	function __construct()
	{
		$dbconfig = vglobal('dbconfig');
		$this->pdo = new PDO('mysql:host=' . $dbconfig['db_server'] . ';dbname=' . $dbconfig['db_name'] . ';charset=utf8', $dbconfig['db_username'], $dbconfig['db_password']);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// Autoloader
		require_once 'libraries/SabreDAV/autoload.php';
	}

	public function cardDavCrm2Dav()
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$this->syncCrmRecord('Contacts');
		$this->syncCrmRecord('OSSEmployees');
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function syncCrmRecord($module)
	{
		$db = PearDatabase::getInstance();
		$create = $deletes = $updates = 0;
		$result = $this->getCrmRecordsToSync($module);
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$record = $db->raw_query_result_rowdata($result, $i);
			foreach ($this->davUsers as $key => $user) {
				$this->addressBookId = $user->get('addressbooksid');
				$current_user = vglobal('current_user');
				$current_user = $user;
				if (Users_Privileges_Model::isPermitted($module, 'DetailView', $record['crmid'])) {
					$card = $this->getCardDetail($record['crmid']);
					if ($card == false) {
						//Creating
						$this->createCard($module, $record);
						$create++;
					} else {
						// Updating
						$this->updateCard($module, $record, $card);
						$updates++;
					}
				}
			}
			$this->markComplete($module, $record['crmid']);
		}
		$this->log->info("syncCrmRecord $module | create: $create | deletes: $deletes | updates: $updates");
	}

	public function cardDav2Crm()
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		foreach ($this->davUsers as $key => $user) {
			$this->addressBookId = $user->get('addressbooksid');
			$this->user = $user;
			$current_user = vglobal('current_user');
			$current_user = $user;
			$this->syncAddressBooks();
		}
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function syncAddressBooks()
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$result = $this->getDavCardsToSync();
		$create = $deletes = $updates = 0;
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$card = $db->raw_query_result_rowdata($result, $i);
			if (!$card['crmid']) {
				//Creating
				$this->createRecord('Contacts', $card);
				$create++;
			} elseif (!isRecordExists($card['crmid']) || !Users_Privileges_Model::isPermitted($card['setype'], 'DetailView', $card['crmid'])) {
				// Deleting
				$this->deletedCard($card);
				$deletes++;
			} else {
				$crmLMT = strtotime($card['modifiedtime']);
				$cardLMT = $card['lastmodified'];
				if ($crmLMT < $cardLMT) {
					// Updating
					$recordModel = Vtiger_Record_Model::getInstanceById($card['crmid']);
					$this->updateRecord($recordModel, $card);
					$updates++;
				}
			}
		}
		$this->log->info("cardDavDav2Crm | create: $create | deletes: $deletes | updates: $updates");
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function createCard($moduleName, $record)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start CRM ID:' . $record['crmid']);
		$vcard = new Sabre\VObject\Component\VCard();
		$vcard->PRODID = self::PRODID;
		if ($moduleName == 'Contacts') {
			$name = $record['firstname'] . ' ' . $record['lastname'];
			$vcard->N = [ $record['lastname'], $record['firstname']];
			$org = Vtiger_Functions::getCRMRecordLabel($record['parentid']);
			if ($org != '') {
				$vcard->ORG = $org;
			}
			if (!empty($record['jobtitle'])) {
				$vcard->TITLE = $record['jobtitle'];
			}
		}
		if ($moduleName == 'OSSEmployees') {
			$name = $record['name'] . ' ' . $record['last_name'];
			$vcard->N = [ $record['last_name'], $record['name']];
			$vcard->ORG = Vtiger_CompanyDetails_Model::getInstanceById()->get('organizationname');
		}
		$vcard->add('FN', $name);
		if (!empty($record['description'])) {
			$vcard->add('NOTE', $record['description']);
		}
		foreach ($this->telFields[$moduleName] as $key => $val) {
			if (!empty($record[$key])) {
				$vcard->add('TEL', $record[$key], ['type' => explode(',', $val)]);
			}
		}
		foreach ($this->mailFields[$moduleName] as $key => $val) {
			if (!empty($record[$key])) {
				$vcard->add('EMAIL', $record[$key], ['type' => explode(',', $val)]);
			}
		}

		$cardUri = $record['crmid'] . '.vcf';
		$cardData = Sabre\DAV\StringUtil::ensureUTF8($vcard->serialize());
		$etag = md5($cardData);
		$modifiedtime = strtotime($record['modifiedtime']);
		$stmt = $this->pdo->prepare('INSERT INTO dav_cards (carddata, uri, lastmodified, addressbookid, size, etag, crmid) VALUES (?, ?, ?, ?, ?, ?, ?)');
		$stmt->execute([
			$cardData,
			$cardUri,
			$modifiedtime,
			$this->addressBookId,
			strlen($cardData),
			$etag,
			$record['crmid'],
		]);
		$this->addChange($cardUri, 1);
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function updateCard($moduleName, $record, $card)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start CRM ID:' . $record['crmid']);
		$vcard = Sabre\VObject\Reader::read($card['carddata']);
		$vcard->PRODID = self::PRODID;
		unset($vcard->TEL);
		unset($vcard->EMAIL);
		unset($vcard->REV);
		if ($moduleName == 'Contacts') {
			$name = $record['firstname'] . ' ' . $record['lastname'];
			$vcard->N = [ $record['lastname'], $record['firstname']];
			$org = Vtiger_Functions::getCRMRecordLabel($record['parentid']);
			if (!empty($org))
				$vcard->ORG = $org;
			if (!empty($record['jobtitle'])) {
				$vcard->TITLE = $record['jobtitle'];
			}
		}
		if ($moduleName == 'OSSEmployees') {
			$name = $record['name'] . ' ' . $record['last_name'];
			$vcard->N = [ $record['last_name'], $record['name']];
			$vcard->ORG = Vtiger_CompanyDetails_Model::getInstanceById()->get('organizationname');
		}
		$vcard->FN = $name;
		if (!empty($record['description'])) {
			$vcard->NOTE = $record['description'];
		}
		foreach ($this->telFields[$moduleName] as $key => $val) {
			if (!empty($record[$key])) {
				$vcard->add('TEL', $record[$key], ['type' => explode(',', $val)]);
			}
		}
		foreach ($this->mailFields[$moduleName] as $key => $val) {
			if (!empty($record[$key])) {
				$vcard->add('EMAIL', $record[$key], ['type' => explode(',', $val)]);
			}
		}

		$cardData = Sabre\DAV\StringUtil::ensureUTF8($vcard->serialize());
		$etag = md5($cardData);
		$modifiedtime = strtotime($record['modifiedtime']);
		$stmt = $this->pdo->prepare('UPDATE dav_cards SET carddata = ?, lastmodified = ?, size = ?, etag = ?, crmid = ? WHERE id = ?;');
		$stmt->execute([
			$cardData,
			$modifiedtime,
			strlen($cardData),
			$etag,
			$record['crmid'],
			$card['id']
		]);
		$this->addChange($card['uri'], 2);
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function deletedCard($card)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start Card ID:' . $card['id']);
		$this->addChange($card['crmid'] . '.vcf', 3);
		$stmt = $this->pdo->prepare('DELETE FROM dav_cards WHERE id = ?;');
		$stmt->execute([
			$card['id']
		]);
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function createRecord($module, $card)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start Card ID' . $card['id']);
		$vcard = Sabre\VObject\Reader::read($card['carddata']);
		if (isset($vcard->ORG)) {
			$lead = Vtiger_Record_Model::getCleanInstance('Leads');
			$lead->set('assigned_user_id', $this->user->get('id'));
			$lead->set('company', (string) $vcard->ORG);
			$lead->set('lastname', (string) $vcard->ORG);
			$lead->set('leadstatus', 'LBL_REQUIRES_VERIFICATION');
			$lead->set('vat_id', '');
			$lead->save();
			$leadId = $lead->getId();
		}
		$head = $vcard->N->getParts();

		$record = Vtiger_Record_Model::getCleanInstance($module);
		$record->set('assigned_user_id', $this->user->get('id'));
		if ($module == 'Contacts') {
			$record->set('firstname', $head[1]);
			$record->set('lastname', $head[0]);
			$record->set('jobtitle', $vcard->TITLE);
		}
		if ($module == 'OSSEmployees') {
			$record->set('name', $head[1]);
			$record->set('last_name', $head[0]);
		}
		$record->set('description', $vcard->NOTE);
		if ($leadId != '') {
			$record->set('parent_id', $leadId);
		}
		foreach ($this->telFields[$module] as $key => $val) {
			$record->set($key, $this->getCardTel($vcard, $val));
		}
		foreach ($this->mailFields[$module] as $key => $val) {
			$record->set($key, $this->getCardMail($vcard, $val));
		}
		$record->save();

		$stmt = $this->pdo->prepare('UPDATE dav_cards SET crmid = ? WHERE id = ?;');
		$stmt->execute([
			$record->getId(),
			$card['id']
		]);
		$stmt = $this->pdo->prepare('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?;');
		$stmt->execute([
			date('Y-m-d H:i:s', $card['lastmodified']),
			$record->getId()
		]);
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function updateRecord($record, $card)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start Card ID:' . $card['id']);
		$vcard = Sabre\VObject\Reader::read($card['carddata']);
		$head = $vcard->N->getParts();
		$module = $record->getModuleName();
		$record->set('mode', 'edit');
		if ($module == 'Contacts') {
			$record->set('firstname', $head[1]);
			$record->set('lastname', $head[0]);
			$record->set('jobtitle', $vcard->TITLE);
		}
		if ($module == 'OSSEmployees') {
			$record->set('name', $head[1]);
			$record->set('last_name', $head[0]);
		}
		if ($leadId != '') {
			$record->set('parent_id', $leadId);
		}
		$record->set('description', $vcard->NOTE);
		foreach ($this->telFields[$module] as $key => $val) {
			$record->set($key, $this->getCardTel($vcard, $val));
		}
		foreach ($this->mailFields[$module] as $key => $val) {
			$record->set($key, $this->getCardMail($vcard, $val));
		}
		$record->save();

		$stmt = $this->pdo->prepare('UPDATE dav_cards SET crmid = ? WHERE id = ?;');
		$stmt->execute([
			$record->getId(),
			$card['id']
		]);
		$stmt = $this->pdo->prepare('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?;');
		$stmt->execute([
			date('Y-m-d H:i:s', $card['lastmodified']),
			$record->getId()
		]);
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function getCrmRecordsToSync($module)
	{
		$db = PearDatabase::getInstance();
		if ($module == 'Contacts')
			$query = 'SELECT crmid, parentid, firstname, lastname, phone, mobile, email, secondary_email, jobtitle, vtiger_crmentity.modifiedtime '
				. 'FROM vtiger_contactdetails '
				. 'INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid '
				. 'WHERE vtiger_crmentity.deleted=0 AND vtiger_contactdetails.contactid > 0 AND vtiger_contactdetails.dav_status = 1;';
		elseif ($module == 'OSSEmployees')
			$query = 'SELECT crmid, name, last_name, business_phone, private_phone, business_mail, private_mail, vtiger_crmentity.modifiedtime '
				. 'FROM vtiger_ossemployees '
				. 'INNER JOIN vtiger_crmentity ON vtiger_ossemployees.ossemployeesid = vtiger_crmentity.crmid '
				. 'WHERE vtiger_crmentity.deleted=0 AND vtiger_ossemployees.ossemployeesid > 0 AND vtiger_ossemployees.dav_status = 1;';
		$result = $db->query($query);
		return $result;
	}

	public function getCardDetail($crmid)
	{
		$db = PearDatabase::getInstance();
		$sql = "SELECT * FROM dav_cards WHERE addressbookid = ? AND crmid = ?;";
		$result = $db->pquery($sql, [$this->addressBookId, $crmid]);
		return $db->num_rows($result) > 0 ? $db->raw_query_result_rowdata($result, 0) : false;
	}

	public function getDavCardsToSync()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT dav_cards.*, vtiger_crmentity.modifiedtime, vtiger_crmentity.setype FROM dav_cards LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = dav_cards.crmid WHERE addressbookid = ?';
		$result = $db->pquery($query, [$this->addressBookId]);
		return $result;
	}

	public function getCardTel($vcard, $type)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start | Type:' . $type);
		if (!isset($vcard->TEL)) {
			$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End | return: ""');
			return '';
		}
		foreach ($vcard->TEL as $t) {
			foreach ($t->parameters() as $k => $p) {
				$vcardType = $p->getValue();
				$vcardType = strtoupper(trim(str_replace('VOICE', '', $vcardType), ','));
				if ($vcardType == strtoupper($type) && $t->getValue() != '') {
					$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End | return: ' . $t->getValue());
					return $t->getValue();
				}
			}
		}
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End | return: ""');
		return '';
	}

	public function getCardMail($vcard, $type)
	{
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | Start | Type:' . $type);
		if (!isset($vcard->EMAIL)) {
			$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End | return: ""');
			return '';
		}
		foreach ($vcard->EMAIL as $e) {
			foreach ($e->parameters() as $k => $p) {
				$vcardType = $p->getValue();
				$vcardType = trim(str_replace('pref', '', $vcardType), ',');
				$vcardType = strtoupper(trim(str_replace('INTERNET', '', $vcardType), ','));
				if ($vcardType == strtoupper($type) && $vcardType != '') {
					$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End | return: ' . $e->getValue());
					return $e->getValue();
				}
			}
		}
		$this->log->debug(__CLASS__ . '::' . __METHOD__ . ' | End | return: ""');
		return '';
	}

	/**
	 * Adds a change record to the addressbookchanges table.
	 *
	 * @param mixed $addressBookId
	 * @param string $objectUri
	 * @param int $operation 1 = add, 2 = modify, 3 = delete
	 * @return void
	 */
	protected function addChange($objectUri, $operation)
	{
		/*
		  $stmt = $this->pdo->prepare('DELETE FROM dav_addressbookchanges WHERE uri = ? AND addressbookid = ?;');
		  $stmt->execute([
		  $objectUri,
		  $this->addressBookId
		  ]);
		 */
		$stmt = $this->pdo->prepare('INSERT INTO dav_addressbookchanges  (uri, synctoken, addressbookid, operation) SELECT ?, synctoken, ?, ? FROM dav_addressbooks WHERE id = ?');
		$stmt->execute([
			$objectUri,
			$this->addressBookId,
			$operation,
			$this->addressBookId
		]);
		$stmt = $this->pdo->prepare('UPDATE dav_addressbooks SET synctoken = synctoken + 1 WHERE id = ?');
		$stmt->execute([
			$this->addressBookId
		]);
	}

	protected function markComplete($moduleName, $crmid)
	{
		if ($moduleName == 'Contacts')
			$query = 'UPDATE vtiger_contactdetails SET dav_status = ? WHERE contactid = ?;';
		elseif ($moduleName == 'OSSEmployees')
			$query = 'UPDATE vtiger_ossemployees SET dav_status = ? WHERE ossemployeesid = ?;';
		if (!$query)
			return;
		$stmt = $this->pdo->prepare($query);
		$stmt->execute([ 0, $crmid]);
	}
}
