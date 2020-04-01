<?php

/**
 * Customer field map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

class Customer extends Base
{
	/**
	 * {@inheritdoc}
	 */
	public static $mappedFields = [
		'firstname' => 'firstname',
		'lastname' => 'lastname',
		'birthday' => 'dob',
		'email' => 'email',
		'salutationtype' => 'gender',
	];
	/**
	 * {@inheritdoc}
	 */
	public static $fieldsType = [
		'salutationtype' => 'map',
		'addresslevel1a' => 'country',
		'addresslevel1b' => 'country',
	];
	/**
	 * {@inheritdoc}
	 */
	public static $additionalFieldsCrm = [
		'leadsource' => 'Magento',
	];
	/**
	 * Contacts_gender map.
	 *
	 * @var string[]
	 */
	public static $salutationtype = [
		'1' => 'Mr.',
		'2' => 'Mrs.',
	];
	/**
	 * Mapped billing fields.
	 *
	 * @var string[]
	 */
	public static $billingFields = [
		'addresslevel1a' => 'country_id',
		'addresslevel2a' => 'region|region',
		'addresslevel5a' => 'city',
		'addresslevel7a' => 'postcode',
		'addresslevel8a' => 'street|0',
		'buildingnumbera' => 'street|1',
		'first_name_a' => 'firstname',
		'last_name_a' => 'lastname',
		'phone_a' => 'telephone',
		'vat_id_a' => 'vat_id',
		'company_name_a' => 'company',
		'phone' => 'telephone',
		'mobile' => 'fax',
	];
	/**
	 * Mapped shipping fields.
	 *
	 * @var string[]
	 */
	public static $shippingFields = [
		'addresslevel1b' => 'country_id',
		'addresslevel2b' => 'region|region',
		'addresslevel5b' => 'city',
		'addresslevel7b' => 'postcode',
		'addresslevel8b' => 'street|0',
		'buildingnumberb' => 'street|1',
		'first_name_b' => 'firstname',
		'last_name_b' => 'lastname',
		'phone_b' => 'telephone',
		'vat_id_b' => 'vat_id',
		'company_name_b' => 'company',
		'phone' => 'telephone',
		'mobile' => 'fax',
	];

	/**
	 * {@inheritdoc}
	 */
	public function getDataCrm(bool $onEdit = false): array
	{
		$parsedData = parent::getDataCrm($onEdit);
		if (!empty($shippingAddress = $this->getAddressDataCrm('shipping'))) {
			$parsedData = \array_replace_recursive($parsedData, $shippingAddress);
		}
		if (!empty($billingAddress = $this->getAddressDataCrm('billing'))) {
			$parsedData = \array_replace_recursive($parsedData, $billingAddress);
		}
		if (!empty($parsedData['phone'])) {
			$parsedData = $this->parsePhone('phone', $parsedData);
		}
		if (!empty($parsedData['mobile'])) {
			$parsedData = $this->parsePhone('mobile', $parsedData);
		}
		return $parsedData;
	}

	/**
	 * Return address data.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getAddressDataCrm(string $type): array
	{
		$addressData = [];
		foreach ($this->getAddressFieldsCrm($type) as $fieldNameCrm => $fieldName) {
			$addressData[$fieldNameCrm] = $this->getAddressFieldValue($type, $fieldNameCrm, $fieldName);
		}
		return $addressData;
	}

	/**
	 * Return address fields crm.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getAddressFieldsCrm(string $type): array
	{
		$fieldsType = $type . 'Fields';
		if (!empty(self::${$fieldsType})) {
			$fields = self::${$fieldsType};
		}
		return $fields ?? [];
	}

	/**
	 * Get address field value from Magento.
	 *
	 * @param string $type
	 * @param string $fieldNameCrm
	 * @param string $fieldName
	 *
	 * @return array|mixed
	 */
	public function getAddressFieldValue(string $type, string $fieldNameCrm, string $fieldName)
	{
		$methodName = 'getCrm' . \ucfirst($fieldNameCrm);
		if (!empty($fieldParsed = $this->getAddressDataByType($type))) {
			if (\method_exists($this, $methodName)) {
				$fieldParsed = $this->{$methodName}();
			} else {
				$fieldLevels = explode('|', $fieldName);
				if (($elements = \count($fieldLevels)) > 1) {
					foreach ($fieldLevels as $level => $fieldLevel) {
						if (isset($fieldParsed[$fieldLevel])) {
							$fieldParsed = $fieldParsed[$fieldLevel];
						} else {
							if ($elements !== $level + 1) {
								$fieldParsed = '';
							}
							break;
						}
					}
				} else {
					$fieldParsed = $fieldParsed[$fieldName] ?? '';
				}
				if (null !== $fieldParsed && isset(static::$fieldsType[$fieldNameCrm])) {
					switch (static::$fieldsType[$fieldNameCrm]) {
						case 'implode':
							$fieldParsed = implode(', ', $fieldParsed);
							break;
						case 'country':
							$fieldParsed = \App\Fields\Country::getCountryName($fieldParsed);
							break;
					}
				} else {
					$fieldParsed = \is_array($fieldParsed) ? null : $fieldParsed;
				}
			}
		} else {
			$fieldParsed = '';
		}
		return $fieldParsed;
	}

	/**
	 * Get given type address data.
	 *
	 * @param string $addressType
	 *
	 * @return array
	 */
	public function getAddressDataByType(string $addressType)
	{
		$data = [];
		$addressName = 'default_' . $addressType;
		foreach ($this->data['addresses'] as $address) {
			if (isset($this->data[$addressName]) && $address['id'] == $this->data[$addressName]) {
				$data = $address;
			}
		}
		return $data ?? current($this->data['addresses']) ?? [];
	}

	/**
	 * Get birthday.
	 *
	 * @return string|null
	 */
	public function getCrmBirthday()
	{
		if (!isset($this->data['dob']) || '0000-00-00' === $this->data['dob']) {
			return null;
		}
		return $this->data['dob'];
	}
}
