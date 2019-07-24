<?php

/**
 * Customer field map.
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
		'birthday' => 'dob',
		'email' => 'email'
	];

	public static $additionalFields = [
		'website_id' => ''
	];

	public function getCrmBirthday()
	{
		if (!isset($this->data['dob']) || '0000-00-00' === $this->data['dob']) {
			return null;
		}
		return $this->data['dob'];
	}

	public function getWebsite_id()
	{
		return \App\Config::component('Magento', 'websiteId');
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
