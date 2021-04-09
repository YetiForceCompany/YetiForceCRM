<?php

/**
 * Order map file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer\Maps;

/**
 * Order map class.
 */
class Order extends Inventory
{
	/** {@inheritdoc} */
	protected $moduleName = 'SSingleOrders';
	/** {@inheritdoc} */
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

	/** {@inheritdoc} */
	public static $mappedFields = [
		'subject' => 'increment_id',
		'ssingleorders_status' => 'state',
		'date_start' => 'created_at',
		'attention' => 'customer_note',
		'payment_methods' => 'payment|method',
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

	/** {@inheritdoc} */
	public static $fieldsType = [
		'addresslevel1a' => 'country',
		'addresslevel1b' => 'country',
		'date_start' => 'date',
		'payment_methods' => 'mapAndAddNew',
		'sale_date' => 'date',
		'status_magento' => 'mapAndAddNew',
		'ssingleorders_status' => 'mapAndAddNew',
		'parent_id' => 'parentRecord',
	];

	/**
	 * Magento state mapping to CRM statuses.
	 *
	 * @var string[]
	 */
	public static $ssingleorders_status = [
		'new' => 'PLL_NEW',
		'processing' => 'PLL_PROCESSING',
		'payment_review' => 'PLL_PAYMENT_REVIEW',
		'holded' => 'PLL_ON_HOLD',
		'complete' => 'PLL_COMPLETE',
		'closed' => 'PLL_CLOSED',
		'canceled' => 'PLL_CANCELLED',
	];
	/**
	 * status_magento field map.
	 *
	 * @var array
	 */
	public static $status_magento = [
		'pending' => 'PLL_PENDING',
		'pending_payment' => 'PLL_PENDING_PAYMENT',
		'pending_paypal' => 'PLL_PENDING_PAYPAL',
		'paid' => 'PLL_PAID',
		'processing' => 'PLL_PROCESSING',
		'paypal_reversed' => 'PLL_PAYPAL_REVERSED',
		'payment_review' => 'PLL_PAYMENT_REVIEW',
		'fraud' => 'PLL_FRAUD',
		'holded' => 'PLL_ON_HOLD',
		'send' => 'PLL_SEND',
		'complete' => 'PLL_COMPLETE',
		'closed' => 'PLL_CLOSED',
		'paypal_canceled_reversal' => 'PLL_PAYPAL_CANCELED_REVERSAL',
		'canceled' => 'PLL_CANCELLED',
	];

	/**
	 * Mapping statuses from the CRM to Magento.
	 * It is used when updating data in Magento.
	 *
	 * @var array
	 */
	public static $statusForMagento = [
		'PLL_NEW' => ['state' => 'new', 'status' => 'pending'],
		'PLL_PAYMENT_REVIEW' => ['state' => 'payment_review', 'status' => 'payment_review'],
		'PLL_ON_HOLD' => ['state' => 'holded', 'status' => 'holded'],
		'PLL_PROCESSING' => ['state' => 'processing', 'status' => 'processing'],
		'PLL_COMPLETE' => ['state' => 'complete', 'status' => 'complete'],
		'PLL_CLOSED' => ['state' => 'closed', 'status' => 'closed'],
		'PLL_CANCELLED' => ['state' => 'canceled', 'status' => 'canceled'],
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

	/**
	 * Parse additional inventory data.
	 *
	 * @return array
	 */
	public function addAdditionalInvData(): array
	{
		$additionalData = [];
		$additionalAmount = $this->data['grand_total'] - $this->data['subtotal_incl_tax'] - $this->data['discount_amount'] - $this->data['shipping_incl_tax'];
		if (!empty($additionalAmount)) {
			if ('paypal_express' === $this->data['payment']['method']) {
				$serviceId = $this->synchronizer->config->get('payment_paypal_service_id');
			} elseif ('cashondelivery' === $this->data['payment']['method']) {
				$serviceId = $this->synchronizer->config->get('payment_cash_service_id');
			}
			if (!empty($serviceId)) {
				$recordModel = \Vtiger_Record_Model::getInstanceById($serviceId, 'Services');
				$taxes = \Vtiger_Inventory_Model::getGlobalTaxes();
				$tax = 0;
				if (isset($taxes[$recordModel->get('taxes')])) {
					$tax = $taxes[$recordModel->get('taxes')]['value'];
					$additionalAmount = round($additionalAmount * 100 / (100 + $tax), 4);
				}
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
					'taxparam' => '{"aggregationType":"individual","individualTax":' . $tax . '}',
					'comment1' => ''
				];
			}
		}
		return $additionalData;
	}

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

	/**
	 * Data update in magento.
	 *
	 * @return array
	 */
	public function getUpdateData(): array
	{
		if (empty(self::$statusForMagento[$this->dataCrm['ssingleorders_status']])) {
			return [];
		}
		return [
			'entity' => array_merge([
				'entity_id' => $this->dataCrm['magento_id'],
				'increment_id' => $this->dataCrm['subject']
			],
				self::$statusForMagento[$this->dataCrm['ssingleorders_status']]
			)
		];
	}
}
