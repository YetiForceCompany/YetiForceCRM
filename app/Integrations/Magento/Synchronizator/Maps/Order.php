<?php

/**
 * Order map file.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

/**
 * Order map class.
 */
class Order extends Inventory
{
	/**
	 * {@inheritdoc}
	 */
	protected $moduleName = 'SSingleOrders';
	/**
	 * {@inheritdoc}
	 */
	public static $additionalFieldsCrm = [
		'sum_tax' => '',
		'sum_total' => '',
		'sum_gross' => '',
		'sum_discount' => '',
		'sum_net' => '',
		'payment_status' => '',
		'ssingleorders_source' => 'PLL_MAGENTO',
		'leadsource' => 'Magento',
	];

	/**
	 * {@inheritdoc}
	 */
	public static $mappedFields = [
		'subject' => 'increment_id',
		'ssingleorders_status' => 'status',
		'date_start' => 'created_at',
		'attention' => 'customer_note',
		'ssingleorders_method_payments' => 'payment|method',
		'status_magento' => 'status',
		'sale_date' => 'created_at',
		'birthday' => 'customer_dob',
		'salutationtype' => 'customer_gender',
		'email' => 'customer_email',
		'firstname' => 'customer_firstname',
		'lastname' => 'customer_lastname',
		'parent_id' => 'relation_parent_id',
	];

	/**
	 * Inventory fields.
	 *
	 * @var array
	 */
	public static $mappedFieldsInv = [
		'price' => 'price',
		'qty' => 'qty_ordered',
		'name' => 'product_id',
		'discount' => 'discount_amount',
	];

	/**
	 * {@inheritdoc}
	 */
	public static $fieldsType = [
		'addresslevel1a' => 'country',
		'addresslevel1b' => 'country',
		'date_start' => 'date',
		'ssingleorders_method_payments' => 'map',
		'sale_date' => 'date',
		'status_magento' => 'map',
		'parent_id' => 'parentRecord',
	];

	/**
	 * Ssingleorders_status field map.
	 *
	 * @var array
	 */
	public static $ssingleorders_status = [
		'processing' => 'PLL_IN_REALIZATION',
		'fraud' => 'PLL_FRAUD',
		'pending_payment' => 'PLL_PENDING_PAYMENT',
		'payment_review' => 'PLL_PAYMENT_REVIEW',
		'pending' => 'PLL_PENDING',
		'holded' => 'PLL_HOLDED',
		'complete' => 'PLL_ACCEPTED',
		'closed' => 'PLL_CLOSED',
		'canceled' => 'PLL_CANCELLED',
	];

	/**
	 * Payment method value map.
	 *
	 * @var array
	 */
	public static $ssingleorders_method_payments = [
		'banktransfer' => 'PLL_TRANSFER',
		'cashondelivery' => 'PLL_CASH_ON_DELIVERY',
	];

	/**
	 * status_magento field map.
	 *
	 * @var array
	 */
	public static $status_magento = [
		'canceled' => 'PLL_CANCELED',
		'closed' => 'PLL_CLOSED',
		'complete' => 'PLL_COMPLETE',
		'fraud' => 'PLL_FRAUD',
		'holded' => 'PLL_HOLDED',
		'payment_review' => 'PLL_PAYMENT_REVIEW',
		'paypal_canceled_reversal' => 'PLL_PAYPAL_CANCELED_REVERSAL',
		'paypal_reversed' => 'PLL_PAYPAL_REVERSED',
		'pending' => 'PLL_PENDING',
		'pending_payment' => 'PLL_PENDING_PAYMENT',
		'pending_paypal' => 'PLL_PENDING_PAYPAL',
		'pending_payupl' => 'PLL_PENDING_PAYUPL',
		'processing' => 'PLL_PROCESSING',
		'processing2' => 'PLL_PROCESSING2',
	];

	/**
	 * Parse additional inventory data.
	 *
	 * @return array
	 */
	public function addAdditionalInvData(): array
	{
		$additionalData = [];
		$additionalAmount = $this->data['grand_total'] - $this->data['subtotal'] - $this->data['discount_amount'] - $this->data['shipping_amount'];
		if (!empty($additionalAmount)) {
			if ('paypal_express' === $this->data['payment']['method']) {
				$serviceId = $this->synchronizer->config->get('payment_paypal_service_id');
			} elseif ('cashondelivery' === $this->data['payment']['method']) {
				$serviceId = $this->synchronizer->config->get('payment_cash_service_id');
			}
			if (!empty($serviceId)) {
				$additionalData = [
					'discountmode' => 1,
					'taxmode' => 1,
					'currency' => $this->dataCrm['currency_id'],
					'name' => $serviceId,
					'unit' => '',
					'subunit' => '',
					'qty' => 1,
					'price' => $additionalAmount,
					'discountparam' => '{"aggregationType":"individual","individualDiscountType":"amount","individualDiscount":0}',
					'purchase' => 0,
					'taxparam' => '{"aggregationType":"individual","individualTax":0}',
					'comment1' => ''
				];
			}
		}
		return $additionalData;
	}

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
		return $this->dataCrm = $parsedData;
	}

	/**
	 * {@inheritdoc}
	 */
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
