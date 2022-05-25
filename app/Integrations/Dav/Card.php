<?php
/**
 * CardDav address books file.
 *
 * @see https://en.wikipedia.org/wiki/VCard#Properties
 * @see https://tools.ietf.org/id/draft-calconnect-vobject-vformat-00.html
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav;

/**
 * CardDav address books class.
 */
class Card
{
	/** @var array Address mapping for modules. */
	const ADDRESS_MAPPING = [
		'Contacts' => [
			'WORK' => [
				'addresslevel1a' => ['country'],
				'addresslevel7a' => ['postCode'],
				'addresslevel2a' => ['state'],
				'addresslevel5a' => ['city'],
				'addresslevel8a' => ['street'],
				'localnumbera' => ['localNumber'],
			],
			'HOME' => [
				'addresslevel1b' => ['country'],
				'addresslevel7b' => ['postCode'],
				'addresslevel2b' => ['state'],
				'addresslevel5b' => ['city'],
				'addresslevel8b' => ['street'],
				'localnumberb' => ['localNumber'],
			],
		],
		'OSSEmployees' => [
			'WORK' => [
				'country' => ['country'],
				'code' => ['postCode'],
				'state' => ['state'],
				'city' => ['city'],
				'street' => ['street', 'localNumber'],
			],
			'HOME' => [
				'ship_country' => ['country'],
				'ship_code' => ['postCode'],
				'ship_state' => ['state'],
				'ship_city' => ['city'],
				'ship_street' => ['street', 'localNumber'],
			],
		],
	];

	/** @var array Mail fields. */
	const MAIL_FIELDS = [
		'Contacts' => ['email' => 'WORK', 'secondary_email' => 'HOME'],
		'OSSEmployees' => ['business_mail' => 'WORK', 'private_mail' => 'HOME'],
	];

	/** @var array Phone fields. */
	const PHONE_FIELDS = [
		'Contacts' => ['phone' => 'WORK', 'mobile' => 'CELL'],
		'OSSEmployees' => ['business_phone' => 'WORK', 'private_phone' => 'CELL', 'secondary_phone' => 'OTHER'],
	];

	/** @var \Sabre\VObject\Component\VCard Card object. */
	private $card;

	/** @var \Users_Record_Model User record model. */
	public $user;

	/** @var \Vtiger_Record_Model Record model. */
	private $record;

	/**
	 * Load from content.
	 *
	 * @param string                    $content
	 * @param \Vtiger_Record_Model|null $recordModel
	 * @param string|null               $uid
	 *
	 * @return self
	 */
	public static function loadFromContent(string $content, ?\Vtiger_Record_Model $recordModel = null, ?string $uid = null): self
	{
		$instance = new self();
		$instance->card = \Sabre\VObject\Reader::read($content);
		if ($recordModel && $uid) {
			$instance->records[$uid] = $recordModel;
		}
		return $instance;
	}

	/**
	 * Get card instance.
	 *
	 * @return \Sabre\VObject\Component\VCard
	 */
	public function getVCard(): \Sabre\VObject\Component\VCard
	{
		return $this->card;
	}

	/**
	 * Delete card by crm id.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public static function deleteByCrmId(int $id): void
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dataReader = (new \App\Db\Query())->select(['addressbookid'])->from('dav_cards')->where(['crmid' => $id])->createCommand()->query();
		$dbCommand->delete('dav_cards', ['crmid' => $id])->execute();
		while ($addressBookId = $dataReader->readColumn(0)) {
			static::addChange($addressBookId, $id . '.vcf', 3);
		}
		$dataReader->close();
	}

	/**
	 * Add change to address books .
	 *
	 * @param int    $addressBookId
	 * @param string $uri
	 * @param int    $operation
	 *
	 * @return void
	 */
	public static function addChange(int $addressBookId, string $uri, int $operation): void
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$syncToken = (int) static::getAddressBook($addressBookId)['synctoken'];
		$dbCommand->insert('dav_addressbookchanges', [
			'uri' => $uri,
			'synctoken' => $syncToken,
			'addressbookid' => $addressBookId,
			'operation' => $operation,
		])->execute();
		$dbCommand->update('dav_addressbooks', [
			'synctoken' => $syncToken + 1,
		], ['id' => $addressBookId])
			->execute();
	}

	/**
	 * Get address books.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getAddressBook(int $id)
	{
		return (new \App\Db\Query())->from('dav_addressbooks')->where(['id' => $id])->one();
	}

	/**
	 * Set values for record.
	 *
	 * @param \Vtiger_Record_Model $record
	 *
	 * @return void
	 */
	public function setValuesForRecord(\Vtiger_Record_Model $record): void
	{
		$this->record = $record;
		if (isset($this->card->N)) {
			$head = $this->card->N->getParts();
		} elseif (isset($this->card->FN)) {
			$head = $this->card->FN->getParts();
		}
		$moduleName = $record->getModuleName();
		if ('Contacts' === $moduleName) {
			if (isset($head[1]) && ($fieldModel = $record->getField('firstname'))) {
				$record->set('firstname', $fieldModel->getDBValue(\App\Purifier::purify($head[1])));
			}
			if (isset($head[0]) && ($fieldModel = $record->getField('lastname'))) {
				$record->set('lastname', $fieldModel->getDBValue(\App\Purifier::purify($head[0])));
			}
			if (isset($this->card->TITLE) && ($fieldModel = $record->getField('jobtitle'))) {
				$record->set('jobtitle', $fieldModel->getDBValue(\App\Purifier::purify((string) $this->card->TITLE)));
			}
			if (isset($this->card->BDAY) && 8 === \strlen($this->card->BDAY) && $record->getField('birthday')) {
				$record->set('birthday', date('Y-m-d', strtotime($this->card->BDAY)));
			}
			if (isset($this->card->GENDER) && ($fieldModel = $record->getField('salutationtype'))) {
				$record->set('salutationtype', $fieldModel->getDBValue($this->getCardGender((string) $this->card->GENDER)));
			}
		} elseif ('OSSEmployees' === $moduleName) {
			if (isset($head[1]) && ($fieldModel = $record->getField('name'))) {
				$record->set('name', $fieldModel->getDBValue(\App\Purifier::purify($head[1])));
			}
			if (isset($head[0]) && ($fieldModel = $record->getField('last_name'))) {
				$record->set('last_name', $fieldModel->getDBValue(\App\Purifier::purify($head[0])));
			}
			if (isset($this->card->BDAY) && $record->getField('birth_date')) {
				$record->set('birth_date', date('Y-m-d', strtotime($this->card->BDAY)));
			}
		}
		if (isset($this->card->NOTE) && ($fieldModel = $record->getField('description'))) {
			$record->set('description', $fieldModel->getDBValue(\App\Purifier::purify((string) $this->card->NOTE)));
		}
		$this->parsePhone();
		$this->parseMail();
		if (isset($this->card->ADR)) {
			$this->setRecordAddress($moduleName, $record);
		}
	}

	/**
	 * Set values for create record.
	 *
	 * @param \Vtiger_Record_Model $record
	 *
	 * @return void
	 */
	public function setValuesForCreateRecord(\Vtiger_Record_Model $record): void
	{
		if ('Contacts' === $record->getModuleName() && isset($this->card->ORG)) {
			$lead = \Vtiger_Record_Model::getCleanInstance('Leads');
			$lead->set('assigned_user_id', $this->user->get('id'));
			$lead->set('leadstatus', 'PLL_PENDING');
			if ($fieldModel = $lead->getField('company')) {
				$lead->set('company', $fieldModel->getDBValue((string) $this->card->ORG));
			}
			$lead->save();
			$fieldModel = current($record->getModule()->getReferenceFieldsForModule('Leads'));
			if ($fieldModel) {
				$record->set($fieldModel->getFieldName(), $lead->getId());
			}
		}
		$this->setValuesForRecord($record);
		$record->set('assigned_user_id', $this->user->get('id'));
	}

	/**
	 * Parse phone.
	 *
	 * @return void
	 */
	private function parsePhone(): void
	{
		$country = null;
		if ($userId = $this->user ? $this->user->getId() : null) {
			$country = \App\Fields\Country::getCountryCode(\App\User::getUserModel($userId)->getDetail('sync_carddav_default_country'));
		}
		$usedTypes = [];
		$moduleName = $this->record->getModuleName();
		foreach (self::PHONE_FIELDS[$moduleName] as $key => $type) {
			if (isset($this->card->TEL) && ($fieldModel = $this->record->getField($key))) {
				$type = strtoupper($type);
				foreach ($this->card->TEL as $t) {
					$types = [];
					foreach ($t->parameters() as $p) {
						$cardType = explode(',', $p->getValue());
						$types[] = strtoupper($cardType[0]);
					}
					if (!$types && !\in_array('WORK', $usedTypes)) {
						$types[] = 'WORK';
					}
					if (\in_array($type, $types)) {
						$orgPhone = \App\Purifier::purify($t->getValue());
						if ($orgPhone && 'phone' === $fieldModel->getFieldDataType()) {
							$details = $fieldModel->getUITypeModel()->getPhoneDetails($orgPhone, $country);
							if ($key !== $details['fieldName']) {
								$this->record->set($details['fieldName'], $details['number']);
								continue;
							}
							$orgPhone = $details['number'];
						}
						$this->record->set($key, $fieldModel->getDBValue($orgPhone));
						$usedTypes = array_merge($usedTypes, $types);
					}
				}
			}
		}
	}

	/**
	 * Parse email.
	 *
	 * @return void
	 */
	private function parseMail(): void
	{
		$moduleName = $this->record->getModuleName();
		$usedTypes = [];
		foreach (self::MAIL_FIELDS[$moduleName] as $key => $type) {
			if (isset($this->card->EMAIL) && ($fieldModel = $this->record->getField($key))) {
				$type = strtoupper($type);
				foreach ($this->card->EMAIL as $e) {
					$types = [];
					foreach ($e->parameters() as $p) {
						$cardType = explode(',', $p->getValue());
						$types[] = strtoupper(array_reverse($cardType)[0]);
					}
					if (!$types && !\in_array('WORK', $usedTypes)) {
						$types[] = 'WORK';
					}
					if (\in_array($type, $types)) {
						$this->record->set($key, $fieldModel->getDBValue(\App\Purifier::purify($e->getValue())));
						$usedTypes = array_merge($usedTypes, $types);
					}
				}
			}
		}
	}

	/**
	 * Get card gender).
	 *
	 * @param string $gender
	 *
	 * @return string
	 */
	private function getCardGender(string $gender): string
	{
		switch ($gender) {
			case 'M':
				$salutation = 'Mr.';
				break;
			case 'F':
				$salutation = 'Mrs.';
				break;
			default:
				$salutation = '';
				break;
		}
		return $salutation;
	}

	/**
	 * Set record address.
	 *
	 * @param string               $moduleName
	 * @param \Vtiger_Record_Model $record
	 *
	 * @return void
	 */
	public function setRecordAddress(string $moduleName, \Vtiger_Record_Model $record): void
	{
		foreach ($this->card->ADR as $property) {
			if ($typeOfAddress = $this->getTypeOfAddress($property)) {
				$address = $this->convertAddress($property->getParts());
				foreach (static::ADDRESS_MAPPING[$moduleName][$typeOfAddress] ?? [] as $fieldName => $fieldsInVCard) {
					$fieldsForJoin = [];
					foreach ($fieldsInVCard as $val) {
						$fieldsForJoin[] = $address[$val];
					}
					if ($fieldModel = $record->getField($fieldName)) {
						$record->set($fieldName, $fieldModel->getDBValue(implode(' ', $fieldsForJoin)));
					}
				}
			}
		}
	}

	/**
	 * Convert address.
	 *
	 * @param array $addressFromVCard
	 *
	 * @return string[]
	 */
	private function convertAddress(array $addressFromVCard): array
	{
		return [
			'country' => \App\Fields\Country::findCountryName(\App\Purifier::purify($addressFromVCard[6])),
			'postCode' => \App\Purifier::purify($addressFromVCard[5]),
			'state' => \App\Purifier::purify($addressFromVCard[4]),
			'city' => \App\Purifier::purify($addressFromVCard[3]),
			'street' => \App\Purifier::purify(trim($addressFromVCard[2])),
			'localNumber' => \App\Purifier::purify($addressFromVCard[1]),
			'postOfficeBox' => \App\Purifier::purify($addressFromVCard[0]),
		];
	}

	/**
	 * Get type of address.
	 *
	 * @param mixed $property
	 *
	 * @return string|null
	 */
	private function getTypeOfAddress(\Sabre\VObject\Property $property): ?string
	{
		$typeOfAddress = null;
		foreach ($property->parameters as $parameter) {
			$type = $parameter->jsonSerialize()[0];
			if ('WORK' === $type || 'HOME' == $type) {
				$typeOfAddress = $type;
				break;
			}
		}
		return $typeOfAddress;
	}
}
