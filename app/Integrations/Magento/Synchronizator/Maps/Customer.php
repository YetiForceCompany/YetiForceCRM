<?php

/**
 * Product field map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

class Customer extends Base
{
	private $synchronizator;

	public function setSynchronizator($synchronizator)
	{
		$this->synchronizator = $synchronizator;
	}

	/**
	 * {@inheritdoc}
	 */
	public static $mappedFields = [
		'firstname' => 'firstname',
		'lastname' => 'lastname',
		'middlename' => 'middlename',
		'group_id' => 'group_id',
		'birthday' => 'dob',
		'contacts_gender' => 'gender',
		'email' => 'email'
	];

	public static $contacts_gender = [
		'1' => 'PLL_MALE',
		'2' => 'PLL_FEMALE',
		'3' => 'PLL_NOT_SPECIFIED',
	];

	/**
	 * {@inheritdoc}
	 */
	public static $additionalFieldsCrm = [
		'leadsource' => 'PLL_MAGENTO',
	];

	public static $additionalFields = [
		'website_id' => ''
	];

	/**
	 * {@inheritdoc}
	 */
	public static $fieldsType = [
		'contacts_gender' => 'map',
	];

	public function getCrmGroup_id()
	{
		return $this->synchronizator->getGroupName($this->data['group_id']);
	}

	public function getCrmBirthday()
	{
		if (!isset($this->data['dob']) || '0000-00-00' === $this->data['dob']) {
			return null;
		}
		return $this->data['dob'];
	}

	public function getGroup_id()
	{
		return $this->synchronizator->getGroupId($this->dataCrm['group_id']);
	}

	public function getWebsite_id()
	{
		return \App\Config::component('Magento', 'storeId');
	}

	/**
	 * Return parsed data in Magento format.
	 *
	 * @param bool $onEdit
	 *
	 * @return array
	 */
	public function getData(bool $onEdit = false): array
	{
		$data = [];
		foreach ($this->getFields($onEdit) as $fieldCrm => $field) {
			$data = \array_merge_recursive($data, $this->getFieldValueCrm($fieldCrm, true));
		}
		foreach ($this->getAdditionalFields() as $name => $value) {
			$data = \array_merge_recursive($data, $this->getFieldStructure($name, !empty($value) ? $value : $this->getFieldValueCrm($name, false)));
		}
		return ['customer' => $data];
	}
}
