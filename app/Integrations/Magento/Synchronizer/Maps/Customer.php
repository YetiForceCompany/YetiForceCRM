<?php

/**
 * Customer map file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer\Maps;

/**
 * Customer map  class.
 */
class Customer extends Base
{
	/** {@inheritdoc} */
	protected $moduleName = 'Accounts';
	/** {@inheritdoc} */
	public static $mappedFields = [
		'firstname' => 'firstname',
		'lastname' => 'lastname',
		'birthday' => 'dob',
		'email' => 'email',
		'salutationtype' => 'gender',
		'gender' => 'gender',
	];

	/** {@inheritdoc} */
	public static $additionalFieldsCrm = [
		'leadsource' => 'Magento',
	];

	/** {@inheritdoc} */
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
		return $this->dataCrm = $parsedData;
	}
}
