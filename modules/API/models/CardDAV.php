<?php

/**
 * Api CardDAV Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_CardDAV_Model
{
	const ADDRESSBOOK_NAME = 'YFAddressBook';
	const PRODID = 'YetiForceCRM';

	public $pdo = false;
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

	public function __construct()
	{
		$dbConfig = \App\Db::getConfig('base');
		$this->pdo = new PDO($dbConfig['dsn'] . ';charset=' . $dbConfig['charset'], $dbConfig['username'], $dbConfig['password']);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function cardDavCrm2Dav()
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		$this->syncCrmRecord('Contacts');
		$this->syncCrmRecord('OSSEmployees');
		\App\Log::trace(__METHOD__ . ' | End');
	}

	public function syncCrmRecord($moduleName)
	{
		$create = $deletes = $updates = 0;
		$query = $this->getCrmRecordsToSync($moduleName);
		if (!$query) {
			return;
		}
		$dataReader = $query->createCommand()->query();
		while ($record = $dataReader->read()) {
			foreach ($this->davUsers as $key => $user) {
				$this->addressBookId = $user->get('addressbooksid');
				$orgUserId = App\User::getCurrentUserId();
				App\User::setCurrentUserId($user->get('id'));
				if (\App\Privilege::isPermitted($moduleName, 'DetailView', $record['crmid'])) {
					$card = $this->getCardDetail($record['crmid']);
					if ($card === false) {
						//Creating
						$this->createCard($moduleName, $record);
						++$create;
					} else {
						// Updating
						$this->updateCard($moduleName, $record, $card);
						++$updates;
					}
				}
				App\User::setCurrentUserId($orgUserId);
			}
			$this->markComplete($moduleName, $record['crmid']);
		}
		$dataReader->close();
		\App\Log::trace("syncCrmRecord $moduleName | create: $create | deletes: $deletes | updates: $updates");
	}

	public function cardDav2Crm()
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		foreach ($this->davUsers as $key => $user) {
			$this->addressBookId = $user->get('addressbooksid');
			$this->user = $user;
			$this->syncAddressBooks();
		}
		\App\Log::trace(__METHOD__ . ' | End');
	}

	public function syncAddressBooks()
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		$dataReader = $this->getDavCardsToSync()->createCommand()->query();
		$create = $deletes = $updates = 0;
		while ($card = $dataReader->read()) {
			if (!$card['crmid']) {
				//Creating
				$this->createRecord('Contacts', $card);
				++$create;
			} elseif (!\App\Record::isExists($card['crmid']) || !\App\Privilege::isPermitted($card['setype'], 'DetailView', $card['crmid'])) {
				// Deleting
				$this->deletedCard($card);
				++$deletes;
			} else {
				$crmLMT = strtotime($card['modifiedtime']);
				$cardLMT = $card['lastmodified'];
				if ($crmLMT < $cardLMT) {
					// Updating
					$recordModel = Vtiger_Record_Model::getInstanceById($card['crmid']);
					$this->updateRecord($recordModel, $card);
					++$updates;
				}
			}
		}
		$dataReader->close();
		\App\Log::trace("cardDavDav2Crm | create: $create | deletes: $deletes | updates: $updates");
		\App\Log::trace(__METHOD__ . ' | End');
	}

	public function createCard($moduleName, $record)
	{
		\App\Log::trace(__METHOD__ . ' | Start CRM ID:' . $record['crmid']);

		$vcard = new Sabre\VObject\Component\VCard();
		$vcard->PRODID = self::PRODID;
		if ($moduleName === 'Contacts') {
			$name = $record['firstname'] . ' ' . $record['lastname'];
			$vcard->N = [$record['lastname'], $record['firstname']];
			$org = vtlib\Functions::getCRMRecordLabel($record['parentid']);
			if ($org != '') {
				$vcard->ORG = $org;
			}
			if (!empty($record['jobtitle'])) {
				$vcard->TITLE = $record['jobtitle'];
			}
		} elseif ($moduleName === 'OSSEmployees') {
			$name = $record['name'] . ' ' . $record['last_name'];
			$vcard->N = [$record['last_name'], $record['name']];
			$vcard->ORG = App\Company::getInstanceById()->get('name');
		}
		$vcard->add('FN', trim($name));
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
		$vcard = $this->setCardAddres($vcard, $moduleName, $record);

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

		\App\Log::trace(__METHOD__ . ' | End');
	}

	public function updateCard($moduleName, $record, $card)
	{
		\App\Log::trace(__METHOD__ . ' | Start CRM ID:' . $record['crmid']);
		$vcard = Sabre\VObject\Reader::read($card['carddata']);
		$vcard->PRODID = self::PRODID;

		$vcard = $this->cleanForUpdate($vcard);

		if ($moduleName === 'Contacts') {
			$name = $record['firstname'] . ' ' . $record['lastname'];
			$vcard->N = [$record['lastname'], $record['firstname']];
			$org = vtlib\Functions::getCRMRecordLabel($record['parentid']);
			if (!empty($org)) {
				$vcard->ORG = $org;
			}
			if (!empty($record['jobtitle'])) {
				$vcard->TITLE = $record['jobtitle'];
			}
		}
		if ($moduleName === 'OSSEmployees') {
			$name = $record['name'] . ' ' . $record['last_name'];
			$vcard->N = [$record['last_name'], $record['name']];
			$vcard->ORG = App\Company::getInstanceById()->get('name');
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
		$vcard = $this->setCardAddres($vcard, $moduleName, $record);

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
			$card['id'],
		]);
		$this->addChange($card['uri'], 2);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	public function deletedCard($card)
	{
		\App\Log::trace(__METHOD__ . ' | Start Card ID:' . $card['id']);
		$this->addChange($card['crmid'] . '.vcf', 3);
		$stmt = $this->pdo->prepare('DELETE FROM dav_cards WHERE id = ?;');
		$stmt->execute([
			$card['id'],
		]);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	public function createRecord($moduleName, $card)
	{
		\App\Log::trace(__METHOD__ . ' | Start Card ID' . $card['id']);
		$vcard = Sabre\VObject\Reader::read($card['carddata']);
		if (isset($vcard->ORG)) {
			$lead = Vtiger_Record_Model::getCleanInstance('Leads');
			$lead->set('assigned_user_id', $this->user->get('id'));
			$lead->set('company', \App\Purifier::purify((string) $vcard->ORG));
			$lead->set('lastname', \App\Purifier::purify((string) $vcard->ORG));
			$lead->set('leadstatus', 'PLL_PENDING');
			$lead->set('vat_id', '');
			$lead->save();
			$leadId = $lead->getId();
		}
		$head = $vcard->N->getParts();

		$record = Vtiger_Record_Model::getCleanInstance($moduleName);
		$record->set('assigned_user_id', $this->user->get('id'));
		if ($moduleName === 'Contacts') {
			$record->set('firstname', \App\Purifier::purify($head[1]));
			$record->set('lastname', \App\Purifier::purify($head[0]));
			$record->set('jobtitle', \App\Purifier::purify($vcard->TITLE));
		} elseif ($moduleName === 'OSSEmployees') {
			$record->set('name', \App\Purifier::purify($head[1]));
			$record->set('last_name', \App\Purifier::purify($head[0]));
		}
		$record->set('description', \App\Purifier::purify($vcard->NOTE));
		if ($leadId) {
			$record->set('parent_id', $leadId);
		}
		foreach ($this->telFields[$moduleName] as $key => $val) {
			$record->set($key, $this->getCardTel($vcard, $val));
		}
		foreach ($this->mailFields[$moduleName] as $key => $val) {
			$record->set($key, $this->getCardMail($vcard, $val));
		}
		if (isset($vcard->ADR)) {
			$this->setRecordAddres($vcard, $moduleName, $record);
		}
		$record->save();

		$stmt = $this->pdo->prepare('UPDATE dav_cards SET crmid = ? WHERE id = ?;');
		$stmt->execute([
			$record->getId(),
			$card['id'],
		]);
		$stmt = $this->pdo->prepare('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?;');
		$stmt->execute([
			date('Y-m-d H:i:s', $card['lastmodified']),
			$record->getId(),
		]);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Update record.
	 *
	 * @param Vtiger_Record_Model $record
	 * @param array               $card
	 */
	public function updateRecord(Vtiger_Record_Model $record, $card)
	{
		\App\Log::trace(__METHOD__ . ' | Start Card ID:' . $card['id']);
		$vcard = Sabre\VObject\Reader::read($card['carddata']);
		$head = $vcard->N->getParts();
		$moduleName = $record->getModuleName();
		if ($moduleName === 'Contacts') {
			$record->set('firstname', \App\Purifier::purify($head[1]));
			$record->set('lastname', \App\Purifier::purify($head[0]));
			$record->set('jobtitle', \App\Purifier::purify($vcard->TITLE));
		} elseif ($moduleName === 'OSSEmployees') {
			$record->set('name', \App\Purifier::purify($head[1]));
			$record->set('last_name', \App\Purifier::purify($head[0]));
		}
		$record->set('description', \App\Purifier::purify($vcard->NOTE));
		foreach ($this->telFields[$moduleName] as $key => $val) {
			$record->set($key, $this->getCardTel($vcard, $val));
		}
		foreach ($this->mailFields[$moduleName] as $key => $val) {
			$record->set($key, $this->getCardMail($vcard, $val));
		}
		if (isset($vcard->ADR)) {
			$this->setRecordAddres($vcard, $moduleName, $record);
		}
		$record->save();

		$stmt = $this->pdo->prepare('UPDATE dav_cards SET crmid = ? WHERE id = ?;');
		$stmt->execute([
			$record->getId(),
			$card['id'],
		]);
		$stmt = $this->pdo->prepare('UPDATE vtiger_crmentity SET modifiedtime = ? WHERE crmid = ?;');
		$stmt->execute([
			date('Y-m-d H:i:s', $card['lastmodified']),
			$record->getId(),
		]);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	public function getCrmRecordsToSync($moduleName)
	{
		if ($moduleName == 'Contacts') {
			return (new App\Db\Query())->select([
						'vtiger_crmentity.crmid', 'vtiger_contactdetails.parentid', 'vtiger_contactdetails.firstname',
						'vtiger_contactdetails.lastname', 'vtiger_contactdetails.phone', 'vtiger_contactdetails.mobile', 'vtiger_contactdetails.email',
						'vtiger_contactdetails.secondary_email', 'vtiger_contactdetails.jobtitle',
						'vtiger_crmentity.modifiedtime', 'vtiger_contactaddress.*',
					])->from('vtiger_contactdetails')
						->innerJoin('vtiger_crmentity', 'vtiger_contactdetails.contactid = vtiger_crmentity.crmid')
						->innerJoin('vtiger_contactaddress', 'vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid')
						->where(['vtiger_contactdetails.dav_status' => 1, 'vtiger_crmentity.deleted' => 0]);
		} elseif ($moduleName == 'OSSEmployees') {
			return (new App\Db\Query())->select([
						'vtiger_crmentity.crmid', 'vtiger_ossemployees.name', 'vtiger_ossemployees.last_name',
						'vtiger_ossemployees.business_phone', 'vtiger_ossemployees.private_phone', 'vtiger_ossemployees.business_mail',
						'vtiger_ossemployees.private_mail', 'vtiger_crmentity.modifiedtime',
					])->from('vtiger_ossemployees')
						->innerJoin('vtiger_crmentity', 'vtiger_ossemployees.ossemployeesid = vtiger_crmentity.crmid')
						->where(['vtiger_ossemployees.dav_status' => 1, 'vtiger_crmentity.deleted' => 0]);
		}
	}

	public function getCardDetail($crmid)
	{
		return (new App\Db\Query())->from('dav_cards')->where(['addressbookid' => $this->addressBookId, 'crmid' => $crmid])->one();
	}

	public function getDavCardsToSync()
	{
		return (new App\Db\Query())->select(['dav_cards.*', 'vtiger_crmentity.modifiedtime', 'vtiger_crmentity.setype'])
			->from('dav_cards')
			->leftJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = dav_cards.crmid')
			->where(['dav_cards.addressbookid' => $this->addressBookId]);
	}

	/**
	 * Get card phone.
	 *
	 * @param Sabre\VObject\Component $vcard
	 * @param string                  $type
	 *
	 * @return string
	 */
	public function getCardTel(Sabre\VObject\Component $vcard, $type)
	{
		\App\Log::trace(__METHOD__ . ' | Start | Type:' . $type);
		if (!isset($vcard->TEL)) {
			\App\Log::trace(__METHOD__ . ' | End | return: ""');

			return '';
		}
		foreach ($vcard->TEL as $t) {
			foreach ($t->parameters() as $k => $p) {
				$vcardType = $p->getValue();
				$vcardType = strtoupper(trim(str_replace('VOICE', '', $vcardType), ','));
				if ($vcardType == strtoupper($type) && $t->getValue() != '') {
					\App\Log::trace(__METHOD__ . ' | End | return: ' . $t->getValue());

					return \App\Purifier::purify($t->getValue());
				}
			}
		}
		\App\Log::trace(__METHOD__ . ' | End | return: ""');

		return '';
	}

	/**
	 * Get card mail.
	 *
	 * @param Sabre\VObject\Component $vcard
	 * @param string                  $type
	 *
	 * @return string
	 */
	public function getCardMail(Sabre\VObject\Component $vcard, $type)
	{
		\App\Log::trace(__METHOD__ . ' | Start | Type:' . $type);
		if (!isset($vcard->EMAIL)) {
			\App\Log::trace(__METHOD__ . ' | End | return: ""');

			return '';
		}
		foreach ($vcard->EMAIL as $e) {
			foreach ($e->parameters() as $k => $p) {
				$vcardType = $p->getValue();
				$vcardType = trim(str_replace('pref', '', $vcardType), ',');
				$vcardType = strtoupper(trim(str_replace('INTERNET', '', $vcardType), ','));
				if ($vcardType == strtoupper($type) && $vcardType != '') {
					\App\Log::trace(__METHOD__ . ' | End | return: ' . $e->getValue());

					return \App\Purifier::purify($e->getValue());
				}
			}
		}
		\App\Log::trace(__METHOD__ . ' | End | return: ""');

		return '';
	}

	/**
	 * Adds a change record to the addressbookchanges table.
	 *
	 * @param mixed  $addressBookId
	 * @param string $objectUri
	 * @param int    $operation     1 = add, 2 = modify, 3 = delete
	 */
	protected function addChange($objectUri, $operation)
	{
		$stmt = $this->pdo->prepare('INSERT INTO dav_addressbookchanges  (uri, synctoken, addressbookid, operation) SELECT ?, synctoken, ?, ? FROM dav_addressbooks WHERE id = ?');
		$stmt->execute([
			$objectUri,
			$this->addressBookId,
			$operation,
			$this->addressBookId,
		]);
		$stmt = $this->pdo->prepare('UPDATE dav_addressbooks SET synctoken = synctoken + 1 WHERE id = ?');
		$stmt->execute([
			$this->addressBookId,
		]);
	}

	protected function markComplete($moduleName, $crmid)
	{
		if ($moduleName == 'Contacts') {
			$query = 'UPDATE vtiger_contactdetails SET dav_status = ? WHERE contactid = ?;';
		} elseif ($moduleName == 'OSSEmployees') {
			$query = 'UPDATE vtiger_ossemployees SET dav_status = ? WHERE ossemployeesid = ?;';
		}
		if (!$query) {
			return;
		}
		$stmt = $this->pdo->prepare($query);
		$stmt->execute([0, $crmid]);
	}

	/**
	 * Set card addres.
	 *
	 * @param Sabre\VObject\Component $vcard
	 * @param string                  $moduleName
	 * @param array                   $record
	 *
	 * @return \Sabre\VObject\Component
	 */
	public function setCardAddres(Sabre\VObject\Component $vcard, $moduleName, $record)
	{
		$adr1 = $adr2 = [];

		if ($moduleName === 'Contacts') {
			if (!empty($record['addresslevel5a'])) {
				$street = $record['addresslevel8a'] . ' ' . $record['buildingnumbera'];
				if (!empty($record['localnumbera'])) {
					$street .= '/' . $record['localnumbera'];
				}
				$adr1 = ['', '',
					$street,
					$record['addresslevel5a'],
					$record['addresslevel2a'],
					$record['addresslevel7a'],
					$record['addresslevel1a'],
				];
			}
			if (!empty($record['addresslevel5b'])) {
				$street = $record['addresslevel8b'] . ' ' . $record['buildingnumberb'];
				if (!empty($record['localnumberb'])) {
					$street .= '/' . $record['localnumberb'];
				}
				$adr2 = ['', '',
					$street,
					$record['addresslevel5b'],
					$record['addresslevel2b'],
					$record['addresslevel7b'],
					$record['addresslevel1b'],
				];
			}
		} elseif ($moduleName == 'OSSEmployees') {
			if (!empty($record['city'])) {
				$adr1 = ['', '',
					$record['street'],
					$record['city'],
					$record['state'],
					$record['code'],
					$record['country'],
				];
			}
			if (!empty($record['ship_city'])) {
				$adr2 = ['', '',
					$record['ship_street'],
					$record['ship_city'],
					$record['ship_state'],
					$record['ship_code'],
					$record['ship_country'],
				];
			}
		}

		if (!empty($adr1)) {
			$vcard->add('ADR', $adr1, ['type' => 'WORK']);
		}
		if (!empty($adr2)) {
			$vcard->add('ADR', $adr2, ['type' => 'HOME']);
		}

		return $vcard;
	}

	/**
	 * Set record addres.
	 *
	 * @param Sabre\VObject\Component $vcard
	 * @param string                  $moduleName
	 * @param Vtiger_Record_Model     $record
	 */
	public function setRecordAddres(Sabre\VObject\Component $vcard, $moduleName, Vtiger_Record_Model $record)
	{
		foreach ($vcard->ADR as $property) {
			$type = false;
			foreach ($property->parameters as $parameter) {
				$value = strtoupper($parameter->getValue());
				if ($value == 'WORK') {
					$type = true;
					$contactsPostFix = 'a';
					$employeesSufFix = '';
				} elseif ($value == 'HOME') {
					$type = true;
					$contactsPostFix = 'b';
					$employeesSufFix = 'ship_';
				}
			}
			if ($type) {
				$adr = $property->getParts();
				$street = $adr[1] . ' ' . $adr[2];
				if ($moduleName === 'Contacts') {
					$record->set('addresslevel1' . $contactsPostFix, \App\Purifier::purify($adr[6])); //country
					$record->set('addresslevel7' . $contactsPostFix, \App\Purifier::purify($adr[5])); //code
					$record->set('addresslevel2' . $contactsPostFix, \App\Purifier::purify($adr[4])); //state
					$record->set('addresslevel5' . $contactsPostFix, \App\Purifier::purify($adr[3])); //city
					$record->set('addresslevel8' . $contactsPostFix, \App\Purifier::purify(trim($street))); //street
				} elseif ($moduleName === 'OSSEmployees') {
					$record->set($employeesSufFix . 'country', \App\Purifier::purify($adr[6])); //country
					$record->set($employeesSufFix . 'code', \App\Purifier::purify($adr[5])); //code
					$record->set($employeesSufFix . 'state', \App\Purifier::purify($adr[4])); //state
					$record->set($employeesSufFix . 'city', \App\Purifier::purify($adr[3])); //city
					$record->set($employeesSufFix . 'street', \App\Purifier::purify(trim($street))); //street
				}
			}
		}
	}

	/**
	 * Clean for update.
	 *
	 * @param Sabre\VObject\Component $vcard
	 *
	 * @return \Sabre\VObject\Component
	 */
	public function cleanForUpdate(Sabre\VObject\Component $vcard)
	{
		unset($vcard->REV, $vcard->TEL, $vcard->EMAIL, $vcard->ADR);

		return $vcard;
	}
}
