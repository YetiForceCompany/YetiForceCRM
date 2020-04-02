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
	public static $additionalFieldsCrm = [
		'leadsource' => 'Magento',
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
}
