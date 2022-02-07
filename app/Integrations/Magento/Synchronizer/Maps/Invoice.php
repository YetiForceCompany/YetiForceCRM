<?php

/**
 * Invoice field map.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer\Maps;

/**
 * Invoice map class.
 */
class Invoice extends Inventory
{
	/** {@inheritdoc} */
	protected $moduleName = 'FInvoice';
	/** {@inheritdoc} */
	public static $additionalFieldsCrm = [
		'sum_tax' => '',
		'sum_total' => '',
		'sum_gross' => '',
		'sum_discount' => '',
		'sum_net' => '',
		'finvoice_type' => 'PLL_DOMESTIC_INVOICE',
	];
	/** {@inheritdoc} */
	public static $mappedFields = [
		'subject' => 'increment_id',
		'finvoice_status' => 'state',
		'payment_methods' => 'payment|method',
		'issue_time' => 'created_at',
		'saledate' => 'created_at',
	];
	/**
	 *{@inheritdoc}
	 */
	public static $mappedFieldsInv = [
		'price' => 'price',
		'qty' => 'qty_invoiced',
		'name' => 'product_id',
		'discount' => 'discount_invoiced',
	];
	/** {@inheritdoc} */
	public static $fieldsType = [
		'finvoice_status' => 'map',
		'payment_methods' => 'mapAndAddNew',
		'addresslevel1a' => 'country',
		'addresslevel1b' => 'country',
		'issue_time' => 'date',
		'saledate' => 'date',
	];
	/**
	 * Invoice status map.
	 *
	 * @var array
	 */
	public static $finvoice_status = [
		'1' => 'PLL_AWAITING_REALIZATION',
		'2' => 'PLL_ACCEPTED',
		'3' => 'PLL_CANCELLED',
	];
	/**
	 * Payment method value map.
	 *
	 * @var array
	 */
	public static $payment_methods = [
		'redsys' => 'PLL_REDSYS',
		'banktransfer' => 'PLL_TRANSFER',
		'cashondelivery' => 'PLL_CASH_ON_DELIVERY',
		'paypal_express' => 'PLL_PAYPAL_EXPRESS',
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
		if (!empty($parsedData['phone_a'])) {
			$parsedData = $this->parsePhone('phone_a', $parsedData);
		}
		if (!empty($parsedData['phone_b'])) {
			$parsedData = $this->parsePhone('phone_b', $parsedData);
		}
		return $this->dataCrm = $parsedData;
	}

	/** {@inheritdoc} */
	public function getAddressDataByType(string $addressType)
	{
		if ('shipping' === $addressType) {
			$data = $this->data['extension_attributes']['shipping_assignments'][0]['shipping']['address'] ?? [];
		} else {
			$data = $this->data['billing_address'] ?? [];
		}
		return $data;
	}
}
