<?php
/**
 * CardDav address books class file.
 *
 * @package   Integrations
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav;

/**
 * CardDav class.
 */
class Card
{
	/**
	 * Address mapping for modules.
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
				'street' => ['localNumber', 'street'],
			],
			'HOME' => [
				'ship_country' => ['country'],
				'ship_code' => ['postCode'],
				'ship_state' => ['state'],
				'ship_city' => ['city'],
				'ship_street' => ['localNumber', 'street'],
			],
		],
	];

	/**
	 * Mail fields.
	 *
	 * @var array
	 */
	public $mailFields = [
		'Contacts' => ['email' => 'WORK', 'secondary_email' => 'HOME'],
		'OSSEmployees' => ['business_mail' => 'WORK', 'private_mail' => 'HOME'],
	];

	/**
	 * $Phone fields.
	 *
	 * @var array
	 */
	public $telFields = [
		'Contacts' => ['phone' => 'WORK', 'mobile' => 'CELL'],
		'OSSEmployees' => ['business_phone' => 'WORK', 'private_phone' => 'CELL'],
	];

	/**
	 * VCard - object.
	 *
	 * @var \Sabre\VObject\Component\VCard
	 */
	private $vcard;

	/**
	 * Record model instance.
	 *
	 * @var \Vtiger_Record_Model[]
	 */
	private $records = [];

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
		$vcard = $this->vcard;
		$head = $vcard->N->getParts();
		$moduleName = $record->getModuleName();
		if ('Contacts' === $moduleName) {
			$record->setFromDisplayFormat('firstname', \App\Purifier::purify($head[1]));
			$record->setFromDisplayFormat('lastname', \App\Purifier::purify($head[0]));
			$record->setFromDisplayFormat('jobtitle', \App\Purifier::purify((string) $vcard->TITLE));
		} elseif ('OSSEmployees' === $moduleName) {
			$record->setFromDisplayFormat('name', \App\Purifier::purify($head[1]));
			$record->setFromDisplayFormat('last_name', \App\Purifier::purify($head[0]));
		}
		$record->setFromDisplayFormat('description', \App\Purifier::purify((string) $vcard->NOTE));
		foreach ($this->telFields[$moduleName] as $key => $val) {
			$record->setFromDisplayFormat($key, $this->getCardTel($vcard, $val));
		}
		foreach ($this->mailFields[$moduleName] as $key => $val) {
			$record->setFromDisplayFormat($key, $this->getCardMail($vcard, $val));
		}
		if (isset($vcard->ADR)) {
			$this->setRecordAddres($vcard, $moduleName, $record);
		}
	}

	/**
	 * Set values for create record.
	 *
	 * @param \Vtiger_Record_Model $record
	 * @param int                  $userId
	 *
	 * @return void
	 */
	public function setValuesForCreateRecord(\Vtiger_Record_Model $record, int $userId)
	{
		if ('Contacts' === $record->getModuleName() && isset($this->vcard->ORG)) {
			$lead = $this->createLeadFromVCard($userId);
			$fieldModel = current($record->getModule()->getReferenceFieldsForModule('Leads'));
			if ($fieldModel) {
				$record->set($fieldModel->getFieldName(), $lead->getId());
			}
		}
		$this->setValuesForRecord($record);
		$record->set('assigned_user_id', $userId);
	}

	/**
	 * Create lead from vCard.
	 *
	 * @param int $userId
	 *
	 * @return \Vtiger_Record_Model
	 */
	public function createLeadFromVCard(int $userId): \Vtiger_Record_Model
	{
		$lead = \Vtiger_Record_Model::getCleanInstance('Leads');
		$lead->set('assigned_user_id', $userId);
		$lead->set('leadstatus', 'PLL_PENDING');
		$lead->set('vat_id', '');
		$lead->setFromDisplayFormat('company', \App\Purifier::purify((string) $this->vcard->ORG));
		$lead->save();
		return $lead;
	}

	/**
	 * Get card phone.
	 *
	 * @param Sabre\VObject\Component $vcard
	 * @param string                  $type
	 *
	 * @return string
	 */
	public function getCardTel(\Sabre\VObject\Component $vcard, string $type): string
	{
		\App\Log::trace(__METHOD__ . ' | Start | Type:' . $type);
		if (!isset($vcard->TEL)) {
			\App\Log::trace(__METHOD__ . ' | End | return: ""');
			return '';
		}
		$type = strtoupper($type);
		foreach ($vcard->TEL as $t) {
			foreach ($t->parameters() as $p) {
				$vcardType = strtoupper(trim(str_replace('VOICE', '', $p->getValue()), ','));
				if ($vcardType === $type && !empty($t->getValue())) {
					$phone = \App\Purifier::purify($t->getValue());
					if (\App\Config::main('phoneFieldAdvancedVerification', false) && !($phone = \App\Fields\Phone::getProperNumber($phone, ($this->user ? $this->user->getId() : null)))) {
						$phone = '';
					}
					\App\Log::trace(__METHOD__ . ' | End | return: ' . $phone);

					return $phone;
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
	public function getCardMail(\Sabre\VObject\Component $vcard, string $type): string
	{
		\App\Log::trace(__METHOD__ . ' | Start | Type:' . $type);
		if (!isset($vcard->EMAIL)) {
			\App\Log::trace(__METHOD__ . ' | End | return: ""');
			return '';
		}
		foreach ($vcard->EMAIL as $e) {
			foreach ($e->parameters() as $p) {
				$vcardType = $p->getValue();
				$vcardType = trim(str_replace('pref', '', $vcardType), ',');
				$vcardType = strtoupper(trim(str_replace('INTERNET', '', $vcardType), ','));
				if ($vcardType == strtoupper($type) && '' != $vcardType) {
					\App\Log::trace(__METHOD__ . ' | End | return: ' . $e->getValue());

					return \App\Purifier::purify($e->getValue());
				}
			}
		}
		\App\Log::trace(__METHOD__ . ' | End | return: ""');
		return '';
	}

	/**
	 * Set record addres.
	 *
	 * @param \Sabre\VObject\Component $vcard
	 * @param string                   $moduleName
	 * @param Vtiger_Record_Model      $record
	 */
	public function setRecordAddres(\Sabre\VObject\Component $vcard, string $moduleName, \Vtiger_Record_Model $record)
	{
		foreach ($vcard->ADR as $property) {
			$typeOfAddress = $this->getTypeOfAddress($property);
			if ($typeOfAddress) {
				$address = $this->convertAddress($property->getParts());
				foreach (static::ADDRESS_MAPPING[$moduleName][$typeOfAddress] ?? [] as $fieldInCrm => $fieldsInVCard) {
					$fieldsForJoin = [];
					foreach ($fieldsInVCard as $val) {
						$fieldsForJoin[] = $address[$val];
					}
					$record->setFromDisplayFormat($fieldInCrm, implode(' ', $fieldsForJoin));
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
	private function getTypeOfAddress($property): ?string
	{
		$typeOfAddress = null;
		foreach ($property->parameters as $parameter) {
			$value = strtoupper($parameter->getValue());
			if ('WORK' === $value || 'HOME' == $value) {
				$typeOfAddress = $value;
				break;
			}
		}
		return $typeOfAddress;
	}
}
