<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class API_CardDAV_Model {
	const ADDRESSBOOK_NAME = 'YetiForceCRM';
	
	public $pdo = false;
	public $log = false;
	public $user = false;
	public $addressBookId = false;
	public $mailFields = [
		'Contacts' => ['WORK'=>'email','HOME'=>'secondary_email'],
		'OSSEmployees' => ['WORK'=>'business_mail','HOME'=>'private_mail'],
	];
	public $telFields = [
		'Contacts' => ['WORK'=>'phone','WORK,CELL'=>'mobile'],
		'OSSEmployees' => ['WORK'=>'business_phone','CELL'=>'private_phone'],
	];

	function __construct($user,$log) {
		$dbconfig = vglobal('dbconfig');
		$this->pdo = new PDO('mysql:host='.$dbconfig['db_server'].';dbname='.$dbconfig['db_name'].';charset=utf8', $dbconfig['db_username'], $dbconfig['db_password']);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$this->user = $user;
		$this->log = $log;
		global $current_user;
		$current_user = $user;
		// Autoloader
		require_once 'libraries/SabreDAV/autoload.php';
	}

	public function cardDavCrm2Dav() {
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		//$syncStatus = $this->checkUnsynchronisedData();
		//var_dump($syncStatus);
		$result = $this->getCrmRecordsToSync('Contacts');
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$record = $db->raw_query_result_rowdata($result, $i);
			$card = $this->getCardDetail($record['crmid']);
			if ($card == false){
				$this->createCard('Contacts',$record);
			}else{
				$crmLMT = strtotime($record['modifiedtime']);
				$cardLMT = $card['lastmodified'];
				if($crmLMT > $cardLMT)
					$this->updateCard('Contacts',$record, $card);
			}
		}
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function cardDavDav2Crm() {
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | Start');
		$db = PearDatabase::getInstance();
		$result = $this->getDavCardsToSync();
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$card = $db->raw_query_result_rowdata($result, $i);
			if (!$card['crmid']){
				$this->createRecord('Contacts',$card);
			}elseif(!isRecordExists($card['crmid'])){
				$this->deletedCard($card);
			}else{
				$recordModel = Vtiger_Record_Model::getInstanceById($card['crmid']);
				$crmLMT = strtotime($recordModel->get('modifiedtime'));
				$cardLMT = $card['lastmodified'];
				if($crmLMT < $cardLMT)
					$this->updateRecord($recordModel, $card);
			}
		}
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | End');
	}

	/**
	 * Verify if there is any unsynchronised data
	 * @param type $user
	 */
	public function checkUnsynchronisedData() {
		$db = PearDatabase::getInstance();
		$sql = "SELECT * FROM dav_cards WHERE addressbookid = ? AND status == ?;";
		$result = $db->pquery($sql, [$this->addressBookId, API_DAV_Model::SYNC_REDY]);
		return $db->num_rows($result) == 0 ? true : false;
	}

	public function getAddressBookId() {
		$db = PearDatabase::getInstance();
		$sql = "SELECT dav_addressbooks.id FROM dav_addressbooks INNER JOIN dav_principals ON dav_principals.uri = dav_addressbooks.principaluri WHERE dav_principals.userid = ? AND dav_addressbooks.uri = ?;";
		$result = $db->pquery($sql, [$this->user->getId(), self::ADDRESSBOOK_NAME]);
		$this->addressBookId = $db->query_result_raw($result, 0, 'id');
	}

	public function getCardDetail($crmid) {
		$db = PearDatabase::getInstance();
		$sql = "SELECT * FROM dav_cards WHERE addressbookid = ? AND crmid = ?;";
		$result = $db->pquery($sql, [$this->addressBookId, $crmid]);
		return $db->num_rows($result) > 0 ? $db->raw_query_result_rowdata($result, 0) : false;
	}
	
	public function createCard($moduleName,$record) {
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | Start CRM ID:'.$record['crmid']);
		$name = $record['firstname'].' '.$record['lastname'];
		$vcard = new Sabre\VObject\Component\VCard();
		$vcard->PRODID = 'YetiForceCRM';
		$vcard->add('N', $name);
		$vcard->add('FN', [ $record['lastname'], $record['firstname']]);
		$vcard->add('ORG', Vtiger_Functions::getCRMRecordLabel($record['parentid']));
		$vcard->add('TEL', $record['phone'], ['type' => 'WORK']);
		$vcard->add('TEL', $record['mobile'], ['type' => ['WORK', 'CELL']]);
		$vcard->add('EMAIL', $record['email'], ['type' => 'WORK']);
		$vcard->add('EMAIL', $record['secondary_email'], ['type' => 'HOME']);
		
		$cardUri = str_replace(' ', '_', $name).'.vcf';
        $cardData = Sabre\DAV\StringUtil::ensureUTF8($vcard->serialize());
		$etag = md5($cardData);
		
		$stmt = $this->pdo->prepare('INSERT INTO dav_cards (carddata, uri, lastmodified, addressbookid, size, etag, crmid, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
		$stmt->execute([
			$cardData,
			$cardUri,
			time(),
			$this->addressBookId,
			strlen($cardData),
			$etag,
			$record['crmid'],
			API_DAV_Model::SYNC_COMPLETED,
		]);
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function updateCard($moduleName, $record, $card) {
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | Start CRM ID:'.$record['crmid']);
		$name = $record['firstname'] . ' ' . $record['lastname'];
		$vcard = Sabre\VObject\Reader::read($card['carddata']);
		$vcard->PRODID = 'YetiForceCRM';
		$vcard->N = [ $record['lastname'], $record['firstname']];
		$vcard->FN = $name;
		$vcard->ORG = Vtiger_Functions::getCRMRecordLabel($record['parentid']);
		unset($vcard->TEL);
		unset($vcard->EMAIL);
		$vcard->add('TEL', $record['phone'], ['type' => 'WORK']);
		$vcard->add('TEL', $record['mobile'], ['type' => ['WORK', 'CELL']]);
		$vcard->add('EMAIL', $record['email'], ['type' => 'WORK']);
		$vcard->add('EMAIL', $record['secondary_email'], ['type' => 'HOME']);
		
        $cardData = Sabre\DAV\StringUtil::ensureUTF8($vcard->serialize());
		$etag = md5($cardData);

		$stmt = $this->pdo->prepare('UPDATE dav_cards SET carddata = ?, lastmodified = ?, size = ?, etag = ?, crmid = ?, status = ? WHERE id = ?;');
		$stmt->execute([
			$cardData,
			time(),
			strlen($cardData),
			$etag,
			$record['crmid'],
			API_DAV_Model::SYNC_COMPLETED,
			$card['id']
		]);
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | End');
	}
	public function deletedCard($card) {
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | Start Card ID:'.$card['id']);
		// 
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | End');
	}
	
	public function createRecord($module, $card) {
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | Start Card ID'.$card['id']);
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

		$rekord = Vtiger_Record_Model::getCleanInstance($module);
		$rekord->set('assigned_user_id', $this->user->get('id'));
		$rekord->set('firstname', $head[1]);
		$rekord->set('lastname', $head[0]);
		if ($leadId != '') {
			$rekord->set('parent_id', $leadId);
		}
		foreach ($this->telFields[$module] as $key => $val) {
			$rekord->set($val, $this->getCardTel($vcard, $key));
		}
		foreach ($this->mailFields[$module] as $key => $val) {
			$rekord->set($val, $this->getCardMail($vcard, $key));
		}
		$rekord->save();

		$stmt = $this->pdo->prepare('UPDATE dav_cards SET crmid = ?, status = ? WHERE id = ?;');
		$stmt->execute([
			$rekord->getId(),
			API_DAV_Model::SYNC_COMPLETED,
			$card['id']
		]);
		$stmt = $this->pdo->prepare('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?;');
		$stmt->execute([
			date('Y-m-d H:i:s', $card['lastmodified']),
			$rekord->getId()
		]);
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function updateRecord($rekord, $card) {
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | Start Card ID:'.$card['id']);
		$vcard = Sabre\VObject\Reader::read($card['carddata']);
		$head = $vcard->N->getParts();
		$module = $rekord->getModuleName();
		
		$rekord->set('assigned_user_id', $this->user->get('id'));
		$rekord->set('firstname', $head[1]);
		$rekord->set('lastname', $head[0]);
		if ($leadId != '') {
			$rekord->set('parent_id', $leadId);
		}
		foreach ($this->telFields[$module] as $key => $val) {
			$rekord->set($val, $this->getCardTel($vcard, $key));
		}
		foreach ($this->mailFields[$module] as $key => $val) {
			$rekord->set($val, $this->getCardMail($vcard, $key));
		}
		$rekord->save();

		$stmt = $this->pdo->prepare('UPDATE dav_cards SET crmid = ?, status = ? WHERE id = ?;');
		$stmt->execute([
			$rekord->getId(),
			API_DAV_Model::SYNC_COMPLETED,
			$card['id']
		]);
		$stmt = $this->pdo->prepare('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?;');
		$stmt->execute([
			date('Y-m-d H:i:s', $card['lastmodified']),
			$rekord->getId()
		]);
		$this->log->debug( __CLASS__ . '::' . __METHOD__ . ' | End');
	}

	public function getCrmRecordsToSync($module) {
		$db = PearDatabase::getInstance();
		if($module == 'Contacts')
			$query = 'SELECT crmid, parentid, firstname, lastname, vtiger_crmentity.modifiedtime, phone, mobile, email, secondary_email FROM vtiger_contactdetails INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted=0 AND vtiger_contactdetails.contactid > 0';

		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $this->user);
		if ($securityParameter != '')
			$query.= ' ' . $securityParameter;
		$result = $db->query($query);
		return $result;
	}

	public function getDavCardsToSync() {
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM dav_cards WHERE addressbookid = ?';
		$result = $db->pquery($query,[$this->addressBookId]);
		return $result;
	}

	public function getCardTel($vcard,$type) {
		foreach ($vcard->TEL as $t) {
			foreach ($t->parameters() as $k => $p) {
				if($p->getValue() == $type){
					return $t->getValue();
				}
			}
		}
		return '';
	}
	public function getCardMail($vcard,$type) {
		foreach ($vcard->EMAIL as $e) {
			foreach ($e->parameters() as $k => $p) {
				if($p->getValue() == $type){
					return $e->getValue();
				}
			}
		}
		return '';
	}
}