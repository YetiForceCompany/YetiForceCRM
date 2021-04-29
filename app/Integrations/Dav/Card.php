<?php
/**
 * CardDav address books file.
 *
 * @package Integration
 *
 * @see https://en.wikipedia.org/wiki/VCard#Properties
 * @see https://tools.ietf.org/id/draft-calconnect-vobject-vformat-00.html
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav;

/**
 * CardDav address books class.
 */
class Card
{
	/**
	 * @var array Address mapping for modules.
	 */
	const ADDRESS_MAPPING = [
		'Contacts' => [
			'WORK' => [
				'addresslevel1a' => ['country'],
				'addresslevel7a' => ['postCode'],
				'addresslevel2a' => ['state'],
				'addresslevel5a' => ['city'],
				'addresslevel8a' => ['street'],
				'localnumbera' => ['localNumber']
			],
			'HOME' => [
				'addresslevel1b' => ['country'],
				'addresslevel7b' => ['postCode'],
				'addresslevel2b' => ['state'],
				'addresslevel5b' => ['city'],
				'addresslevel8b' => ['street'],
				'localnumberb' => ['localNumber']
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
	/**
	 * @var array Mail fields.
	 */
	public static $mailFields = [
		'Contacts' => ['email' => 'WORK', 'secondary_email' => 'HOME'],
		'OSSEmployees' => ['business_mail' => 'WORK', 'private_mail' => 'HOME'],
	];
	/**
	 * @var array Phone fields.
	 */
	public static $telFields = [
		'Contacts' => ['phone' => 'WORK', 'mobile' => 'CELL'],
		'OSSEmployees' => ['business_phone' => 'WORK', 'private_phone' => 'CELL', 'secondary_phone' => 'OTHER'],
	];
	/**
	 * VCard - object.
	 *
	 * @var \Sabre\VObject\Component\VCard
	 */
	private $vcard;
	/**
	 * User record model.
	 *
	 * @var \Users_Record_Model
	 */
	public $user;
	/**
	 * Record data.
	 *
	 * @var \Vtiger_Record_Model
	 */
	private $record = [];

	/**
	 * Load from content.
	 *
	 * @param string                    $content
	 * @param \Vtiger_Record_Model|null $recordModel
	 * @param string|null               $uid
	 *
	 * @return self
	 */
	public static function loadFromContent(string $content, ?\Vtiger_Record_Model $recordModel = null, ?string $uid = null)
	{
		$instance = new self();
		$instance->vcard = \Sabre\VObject\Reader::read($content);
		if ($recordModel && $uid) {
			$instance->records[$uid] = $recordModel;
		}
		return $instance;
	}

	/**
	 * Get VCalendar instance.
	 *
	 * @return \Sabre\VObject\Component\VCalendar
	 */
	public function getVCard()
	{
		return $this->vcard;
	}

	/**
	 * Delete card by crm id.
	 *
	 * @param int $id
	 *
	 * @throws \yii\db\Exception
	 */
	public static function deleteByCrmId(int $id)
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
	 * @throws \yii\db\Exception
	 */
	public static function addChange(int $addressBookId, string $uri, int $operation)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$addressBook = static::getAddressBook($addressBookId);
		$dbCommand->insert('dav_addressbookchanges', [
			'uri' => $uri,
			'synctoken' => (int) $addressBook['synctoken'],
			'addressbookid' => $addressBookId,
			'operation' => $operation
		])->execute();
		$dbCommand->update('dav_addressbooks', [
			'synctoken' => ((int) $addressBook['synctoken']) + 1
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
	 * @param Vtiger_Record_Model            $record
	 * @param \Sabre\VObject\Component\VCard $vcard
	 *
	 * @return void
	 */
	public function setValuesForRecord(\Vtiger_Record_Model $record)
	{
		$this->record = $record;
		if (isset($this->vcard->N)) {
			$head = $this->vcard->N->getParts();
		} elseif (isset($this->vcard->FN)) {
			$head = $this->vcard->FN->getParts();
		}
		$moduleName = $record->getModuleName();
		if ('Contacts' === $moduleName) {
			if (isset($head[1]) && ($fieldModel = $record->getField('firstname'))) {
				$record->set('firstname', $fieldModel->getDBValue(\App\Purifier::purify($head[1])));
			}
			if (isset($head[0]) && ($fieldModel = $record->getField('lastname'))) {
				$record->set('lastname', $fieldModel->getDBValue(\App\Purifier::purify($head[0])));
			}
			if (isset($this->vcard->TITLE) && ($fieldModel = $record->getField('jobtitle'))) {
				$record->set('jobtitle', $fieldModel->getDBValue(\App\Purifier::purify((string) $this->vcard->TITLE)));
			}
			if (isset($this->vcard->BDAY) && 8 === \strlen($this->vcard->BDAY) && ($fieldModel = $record->getField('birthday'))) {
				$record->set('birthday', date('Y-m-d', strtotime($this->vcard->BDAY)));
			}
			if (isset($this->vcard->GENDER) && ($fieldModel = $record->getField('salutationtype'))) {
				$record->set('salutationtype', $fieldModel->getDBValue($this->getCardGender((string) $this->vcard->GENDER)));
			}
		} elseif ('OSSEmployees' === $moduleName) {
			if (isset($head[1]) && ($fieldModel = $record->getField('name'))) {
				$record->set('name', $fieldModel->getDBValue(\App\Purifier::purify($head[1])));
			}
			if (isset($head[0]) && ($fieldModel = $record->getField('last_name'))) {
				$record->set('last_name', $fieldModel->getDBValue(\App\Purifier::purify($head[0])));
			}
			if (isset($this->vcard->BDAY) && ($fieldModel = $record->getField('birth_date'))) {
				$record->set('birth_date', date('Y-m-d', strtotime($this->vcard->BDAY)));
			}
		}
		if (isset($this->vcard->NOTE) && ($fieldModel = $record->getField('description'))) {
			$record->set('description', $fieldModel->getDBValue(\App\Purifier::purify((string) $this->vcard->NOTE)));
		}
		$this->parseTel();
		foreach (self::$mailFields[$moduleName] as $key => $val) {
			if (isset($this->vcard->EMAIL) && ($fieldModel = $record->getField($key))) {
				$record->set($key, $fieldModel->getDBValue($this->getCardMail($val)));
			}
		}
		if (isset($this->vcard->ADR)) {
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
	public function setValuesForCreateRecord(\Vtiger_Record_Model $record)
	{
		if ('Contacts' === $record->getModuleName() && isset($this->vcard->ORG)) {
			$lead = \Vtiger_Record_Model::getCleanInstance('Leads');
			$lead->set('assigned_user_id', $this->user->get('id'));
			$lead->set('leadstatus', 'PLL_PENDING');
			if ($fieldModel = $lead->getField('company')) {
				$lead->set('company', $fieldModel->getDBValue((string) $this->vcard->ORG));
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
	 * Parse card phone.
	 */
	private function parseTel()
	{
		$moduleName = $this->record->getModuleName();
		foreach (self::$telFields[$moduleName] as $key => $type) {
			if (isset($this->vcard->TEL) && ($fieldModel = $this->record->getField($key))) {
				$type = strtoupper($type);
				foreach ($this->vcard->TEL as $t) {
					foreach ($t->parameters() as $p) {
						$vcardType = explode(',', $p->getValue());
						if (strtoupper($vcardType[0]) === $type) {
							$orgPhone = \App\Purifier::purify($t->getValue());
							if ($orgPhone && 'phone' === $fieldModel->getFieldDataType()) {
								$country = null;
								if ($userId = $this->user ? $this->user->getId() : null) {
									$country = \App\Fields\Country::getCountryCode(\App\User::getUserModel($userId)->getDetail('sync_carddav_default_country'));
								}
								$details = $fieldModel->getUITypeModel()->getPhoneDetails($orgPhone, $country);
								if ($key !== $details['fieldName']) {
									$this->record->set($details['fieldName'], $details['number']);
									continue 2;
								}
							}
							$this->record->set($key, $fieldModel->getDBValue($orgPhone));
							continue 2;
						}
					}
				}
			}
		}
	}

	/**
	 * Get card mail.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	private function getCardMail(string $type): string
	{
		\App\Log::trace(__METHOD__ . ' | Start | Type:' . $type);
		foreach ($this->vcard->EMAIL as $e) {
			foreach ($e->parameters() as $p) {
				$vcardType = explode(',', $p->getValue());
				$vcardType = array_reverse($vcardType);
				if (strtoupper($vcardType[0]) === $type) {
					\App\Log::trace(__METHOD__ . ' | End | return: ' . $e->getValue());
					return \App\Purifier::purify($e->getValue());
				}
			}
		}
		\App\Log::trace(__METHOD__ . ' | End | return: ""');
		return '';
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
		$salutation = '';
		switch ($gender) {
			case 'M':
				$salutation = 'Mr.';
				break;
			case 'F':
				$salutation = 'Mrs.';
				break;
		}
		return $salutation;
	}

	/**
	 * Set record addres.
	 *
	 * @param string               $moduleName
	 * @param \Vtiger_Record_Model $record
	 *
	 * @return void
	 */
	public function setRecordAddress(string $moduleName, \Vtiger_Record_Model $record): void
	{
		foreach ($this->vcard->ADR as $property) {
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
	 * @return array
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
