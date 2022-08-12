<?php

/**
 * Api CardDAV Model Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_CardDAV_Model
{
	const ADDRESSBOOK_NAME = 'YFAddressBook';
	const PRODID = 'YetiForceCRM';

	public $pdo = false;

	/**
	 * @var bool|Users_Record_Model
	 */
	public $user = false;
	public $addressBookId = false;

	/**
	 * @var Users_Record_Model[]
	 */
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
	protected static $cache = [];

	public function __construct()
	{
		$dbConfig = \App\Config::db('base');
		$this->pdo = new PDO($dbConfig['dsn'] . ';charset=' . $dbConfig['charset'], $dbConfig['username'], $dbConfig['password']);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Sync from CRM to DAV.
	 *
	 * @return string
	 */
	public function crm2Dav(): string
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		$log = 'Contacts: ' . $this->syncCrmRecord('Contacts');
		$log .= ' & OSSEmployees: ' . $this->syncCrmRecord('OSSEmployees');
		\App\Log::trace(__METHOD__ . ' | End');
		return $log;
	}

	/**
	 * Sync from CRM to DAV for one module.
	 *
	 * @param mixed $moduleName
	 *
	 * @return string
	 */
	public function syncCrmRecord($moduleName)
	{
		$create = $updates = 0;
		$query = $this->getCrmRecordsToSync($moduleName);
		if (!$query) {
			return;
		}
		$dataReader = $query->createCommand()->query();
		while ($record = $dataReader->read()) {
			foreach ($this->davUsers as $userId => $user) {
				$this->addressBookId = $user->get('addressbooksid');
				$orgUserId = App\User::getCurrentUserId();
				App\User::setCurrentUserId($userId);
				switch ($user->get('sync_carddav')) {
					case 'PLL_BASED_CREDENTIALS':
						$isPermitted = \App\Privilege::isPermitted($moduleName, 'DetailView', $record['crmid']);
						break;
					case 'PLL_OWNER_PERSON':
						$isPermitted = (int) $record['smownerid'] === $userId || \in_array($userId, \App\Fields\SharedOwner::getById($record['crmid']));
						break;
					case 'PLL_OWNER_PERSON_GROUP':
						$shownerIds = \App\Fields\SharedOwner::getById($record['crmid']);
						$isPermitted = (int) $record['smownerid'] === $userId || \in_array($record['smownerid'], $user->get('groups')) || \in_array($userId, $shownerIds) || \count(array_intersect($shownerIds, $user->get('groups'))) > 0;
						break;
					case 'PLL_OWNER':
					default:
						$isPermitted = (int) $record['smownerid'] === $userId;
						break;
				}
				if ($isPermitted) {
					$card = $this->getCardDetail($record['crmid']);
					if (false === $card) {
						//Creating
						$this->createCard($moduleName, $record);
						++$create;
					} else {
						// Updating
						$this->updateCard($moduleName, $record, $card);
						++$updates;
					}
					self::$cache[$userId][$record['crmid']] = true;
				}
				App\User::setCurrentUserId($orgUserId);
			}
			$this->markComplete($moduleName, $record['crmid']);
		}
		$dataReader->close();
		\App\Log::trace("AddressBooks end - CRM >> DAV ({$moduleName}) | create: {$create} | updates: {$updates}", __METHOD__);
		return $create + $updates;
	}

	/**
	 * Sync from DAV to CRM.
	 *
	 * @return int
	 */
	public function dav2Crm(): int
	{
		\App\Log::trace(__METHOD__ . ' | Start');
		$i = 0;
		foreach ($this->davUsers as $user) {
			$this->addressBookId = $user->get('addressbooksid');
			$this->user = $user;
			$i += $this->syncAddressBooks();
		}
		\App\Log::trace(__METHOD__ . ' | End');
		return $i;
	}

	/**
	 * Sync from DAV to CRM for one user.
	 *
	 * @return int
	 */
	public function syncAddressBooks(): int
	{
		\App\Log::trace('AddressBooks start', __METHOD__);
		$dataReader = $this->getDavCardsToSync()->createCommand()->query();
		$create = $deletes = $updates = $skipped = 0;
		while ($card = $dataReader->read()) {
			if (!$card['crmid']) {
				//Creating
				if ($this->createRecord('Contacts', $card)) {
					++$create;
				} else {
					++$skipped;
				}
			} else {
				$userId = (int) $this->user->getId();
				if (isset(self::$cache[$userId][$card['crmid']])) {
					continue;
				}
				switch ($this->user->get('sync_carddav')) {
					case 'PLL_BASED_CREDENTIALS':
						$isPermitted = \App\Privilege::isPermitted($card['setype'], 'DetailView', $card['crmid']);
						break;
					case 'PLL_OWNER_PERSON':
						$isPermitted = (int) $card['smownerid'] === $userId || \in_array($userId, \App\Fields\SharedOwner::getById($card['crmid']));
						break;
					case 'PLL_OWNER_PERSON_GROUP':
						$shownerIds = \App\Fields\SharedOwner::getById($card['crmid']);
						$isPermitted = (int) $card['smownerid'] === $userId || \in_array($card['smownerid'], $this->user->get('groups')) || \in_array($userId, $shownerIds) || \count(array_intersect($shownerIds, $this->user->get('groups'))) > 0;
						break;
					case 'PLL_OWNER':
					default:
						$isPermitted = (int) $card['smownerid'] === $userId;
						break;
				}
				if (!\App\Record::isExists($card['crmid']) || !$isPermitted) {
					// Deleting
					$this->deletedCard($card);
					++$deletes;
				} elseif (strtotime($card['modifiedtime']) < $card['lastmodified']) {
					// Updating
					$this->updateRecord(Vtiger_Record_Model::getInstanceById($card['crmid'], $card['setype']), $card);
					++$updates;
				}
			}
		}
		$dataReader->close();
		\App\Log::trace("AddressBooks end - DAV >> CRM | create: {$create} | deletes: {$deletes} | updates: {$skipped} | skipped: {$updates}", __METHOD__);
		return $create + $deletes + $updates;
	}

	public function createCard($moduleName, $record)
	{
		\App\Log::trace(__METHOD__ . ' | Start CRM ID:' . $record['crmid']);

		$vcard = new Sabre\VObject\Component\VCard();
		$vcard->PRODID = self::PRODID;
		if ('Contacts' === $moduleName) {
			$name = $record['firstname'] . ' ' . $record['lastname'];
			$vcard->N = [$record['lastname'], $record['firstname']];
			$org = vtlib\Functions::getCRMRecordLabel($record['parentid']);
			if ('' != $org) {
				$vcard->ORG = $org;
			}
			if (!empty($record['jobtitle'])) {
				$vcard->TITLE = $record['jobtitle'];
			}
		} elseif ('OSSEmployees' === $moduleName) {
			$name = $record['name'] . ' ' . $record['last_name'];
			$vcard->N = [$record['last_name'], $record['name']];
			$vcard->ORG = $record['company_name'];
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
			\strlen($cardData),
			$etag,
			$record['crmid'],
		]);
		\App\Integrations\Dav\Card::addChange($this->addressBookId, $cardUri, 1);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	public function updateCard($moduleName, $record, $card)
	{
		\App\Log::trace(__METHOD__ . ' | Start CRM ID:' . $record['crmid']);
		$vcard = Sabre\VObject\Reader::read($card['carddata']);
		$vcard->PRODID = self::PRODID;

		$vcard = $this->cleanForUpdate($vcard);

		if ('Contacts' === $moduleName) {
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
		if ('OSSEmployees' === $moduleName) {
			$name = $record['name'] . ' ' . $record['last_name'];
			$vcard->N = [$record['last_name'], $record['name']];
			$vcard->ORG = $record['company_name'];
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
			\strlen($cardData),
			$etag,
			$record['crmid'],
			$card['id'],
		]);
		\App\Integrations\Dav\Card::addChange($this->addressBookId, $card['uri'], 2);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	public function deletedCard($card)
	{
		\App\Log::trace(__METHOD__ . ' | Start Card ID:' . $card['id']);
		\App\Integrations\Dav\Card::addChange($this->addressBookId, $card['crmid'] . '.vcf', 3);
		$stmt = $this->pdo->prepare('DELETE FROM dav_cards WHERE id = ?;');
		$stmt->execute([
			$card['id'],
		]);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Create record.
	 *
	 * @param string $moduleName
	 * @param array  $card
	 *
	 * @return void
	 */
	public function createRecord(string $moduleName, array $card)
	{
		\App\Log::trace(__METHOD__ . ' | Start Card ID' . $card['id']);
		$record = Vtiger_Record_Model::getCleanInstance($moduleName);
		$cartInstance = \App\Integrations\Dav\Card::loadFromContent($card['carddata']);
		$cartInstance->user = $this->user;
		if (empty($cartInstance->getVCard()->N) && empty($cartInstance->getVCard()->FN)) {
			\App\Log::error("Not found N and FN part in vcard: Id: {$card['id']}, Addressbookid: {$card['addressbookid']}, Data: \n{$card['carddata']}", __CLASS__);
			return false;
		}
		$cartInstance->setValuesForCreateRecord($record);
		$record->save();
		$this->updateIdAndModifiedTime($record, $card);
		\App\Log::trace(__METHOD__ . ' | End');
		return true;
	}

	/**
	 * Update record.
	 *
	 * @param Vtiger_Record_Model $record
	 * @param array               $card
	 */
	public function updateRecord(Vtiger_Record_Model $record, array $card)
	{
		\App\Log::trace(__METHOD__ . ' | Start Card ID:' . $card['id']);
		$cartInstance = \App\Integrations\Dav\Card::loadFromContent($card['carddata']);
		$cartInstance->user = $this->user;
		$cartInstance->setValuesForRecord($record);
		$record->save();
		$this->updateIdAndModifiedTime($record, $card);
		\App\Log::trace(__METHOD__ . ' | End');
	}

	/**
	 * Update ID and modified time.
	 *
	 * @param Vtiger_Record_Model $record
	 * @param array               $card
	 *
	 * @return void
	 */
	protected function updateIdAndModifiedTime(Vtiger_Record_Model $record, array $card)
	{
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
	}

	public function getCrmRecordsToSync($moduleName)
	{
		if ('Contacts' == $moduleName) {
			return (new App\Db\Query())->select([
				'vtiger_crmentity.crmid', 'vtiger_crmentity.smownerid', 'vtiger_contactdetails.parentid', 'vtiger_contactdetails.firstname',
				'vtiger_contactdetails.lastname', 'vtiger_contactdetails.phone', 'vtiger_contactdetails.mobile', 'vtiger_contactdetails.email',
				'vtiger_contactdetails.secondary_email', 'vtiger_contactdetails.jobtitle',
				'vtiger_crmentity.modifiedtime', 'vtiger_contactaddress.*',
			])->from('vtiger_contactdetails')
				->innerJoin('vtiger_crmentity', 'vtiger_contactdetails.contactid = vtiger_crmentity.crmid')
				->innerJoin('vtiger_contactaddress', 'vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid')
				->where(['vtiger_contactdetails.dav_status' => 1, 'vtiger_crmentity.deleted' => 0]);
		}
		if ('OSSEmployees' == $moduleName) {
			return (new App\Db\Query())->select([
				'vtiger_crmentity.crmid', 'vtiger_crmentity.smownerid', 'vtiger_ossemployees.name', 'vtiger_ossemployees.last_name',
				'vtiger_ossemployees.business_phone', 'vtiger_ossemployees.private_phone', 'vtiger_ossemployees.business_mail',
				'vtiger_ossemployees.private_mail', 'vtiger_crmentity.modifiedtime', 'u_#__multicompany.company_name',
			])->from('vtiger_ossemployees')
				->innerJoin('vtiger_crmentity', 'vtiger_ossemployees.ossemployeesid = vtiger_crmentity.crmid')
				->innerJoin('u_#__multicompany', 'vtiger_ossemployees.multicompanyid = u_#__multicompany.multicompanyid')
				->where(['vtiger_ossemployees.dav_status' => 1, 'vtiger_crmentity.deleted' => 0]);
		}
	}

	public function getCardDetail($crmid)
	{
		return (new App\Db\Query())->from('dav_cards')->where(['addressbookid' => $this->addressBookId, 'crmid' => $crmid])->one();
	}

	public function getDavCardsToSync()
	{
		return (new App\Db\Query())->select(['dav_cards.*', 'vtiger_crmentity.modifiedtime', 'vtiger_crmentity.smownerid', 'vtiger_crmentity.setype'])
			->from('dav_cards')
			->leftJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = dav_cards.crmid')
			->where(['dav_cards.addressbookid' => $this->addressBookId]);
	}

	protected function markComplete($moduleName, $crmid)
	{
		if ('Contacts' == $moduleName) {
			$query = 'UPDATE vtiger_contactdetails SET dav_status = ? WHERE contactid = ?;';
		} elseif ('OSSEmployees' == $moduleName) {
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
		if ('Contacts' === $moduleName) {
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
		} elseif ('OSSEmployees' == $moduleName) {
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
	 * Clean for update.
	 *
	 * @param Sabre\VObject\Component $vcard
	 *
	 * @return \Sabre\VObject\Component
	 */
	public function cleanForUpdate(Sabre\VObject\Component $vcard)
	{
		$vcard->REV = null;
		$vcard->TEL = null;
		$vcard->EMAIL = null;
		$vcard->ADR = null;
		return $vcard;
	}
}
