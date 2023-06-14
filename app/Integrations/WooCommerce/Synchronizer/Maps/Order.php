<?php

/**
 * WooCommerce order synchronization map file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\WooCommerce\Synchronizer\Maps;

/**
 * WooCommerce order synchronization map class.
 */
class Order extends Inventory
{
	/** {@inheritdoc} */
	protected $moduleName = 'SSingleOrders';
	/** {@inheritdoc} */
	protected $fieldMap = [
		'subject' => ['name' => 'id', 'direction' => 'yf'],
		'parent_id' => ['name' => 'parent_id', 'fn' => 'findByRelationship', 'direction' => 'yf'],
		'currency_id' => ['name' => 'currency', 'fn' => 'convertCurrency', 'direction' => 'yf'],
		'ssingleorders_status' => ['name' => 'status', 'map' => [
			'pending' => 'PLL_NEW',
			'processing' => 'PLL_PROCESSING',
			'on-hold' => 'PLL_ON_HOLD',
			'completed' => 'PLL_COMPLETE',
			'cancelled' => 'PLL_CANCELLED',
			'refunded' => 'PLL_REFUNDED',
			'failed' => 'PLL_FAILED',
			'trash' => 'LBL_ENTITY_STATE_TRASH',
		]],
		'payment_methods' => ['name' => 'payment_method', 'fn' => 'convertPaymentMethod', 'direction' => 'yf'],
		'accountid' => [
			'name' => 'customer_id',
			'fn' => 'addRelationship',
			'moduleName' => 'Accounts',
			'direction' => 'yf',
			'onlyCreate' => true
		],
		'description' => ['name' => 'customer_note', 'direction' => 'yf'],
		'createdtime' => ['name' => 'date_created_gmt', 'fn' => 'convertDateTime', 'direction' => 'yf'],
	];
	/** {@inheritdoc} */
	protected $invFieldMap = [
		'name' => ['name' => 'product_id', 'fn' => 'findProduct', 'moduleName' => 'Products',  'direction' => 'yf'],
		'qty' => 'quantity',
		'price' => 'price',
		'taxparam' => ['name' => 'total_tax', 'fn' => 'convertTax', 'direction' => 'yf'],
		'comment1' => ['name' => 'meta_data', 'fn' => 'convertInvDesc', 'direction' => 'yf'],
	];
	/** {@inheritdoc} */
	protected $defaultDataYf = [
		'fieldMap' => [
			'ssingleorders_source' => 'PLL_WOOCOMMERCE'
		],
		'invFieldMap' => [
			'discountmode' => 1,
			'discountparam' => '{"aggregationType":"individual","individualDiscountType":"amount","individualDiscount":0}',
		]
	];
	/** @var \App\Integrations\WooCommerce\Synchronizer\OrdersPayment Payment method model instance */
	protected $payment;
	/** @var \App\Integrations\WooCommerce\Synchronizer\Product Product method model instance */
	protected $product;
	/** @var \App\Integrations\WooCommerce\Synchronizer\Maps\Account Account model instance */
	protected $account;

	/** {@inheritdoc} */
	public function saveInApi(): void
	{
		if ($this->dataApi['id']) {
			$this->synchronizer->controller->getConnector()->request('PUT', "orders/{$this->dataApi['id']}", $this->dataApi);
		} else {
			throw new \App\Exceptions\AppException('Record doesn\'t exist');
		}
	}

	/**
	 * Convert payment method in YF.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return string
	 */
	protected function convertPaymentMethod($value, array $field, bool $fromApi)
	{
		if (null === $this->payment) {
			$this->payment = new \App\Integrations\WooCommerce\Synchronizer\OrdersPayment($this->synchronizer->controller);
		}
		return $this->payment->map[$value] ?? $value;
	}

	/**
	 * Find relationship in YF by API ID.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return int
	 */
	protected function findProduct($value, array $field, bool $fromApi): int
	{
		if ($this->dataApi['variation_id']) {
			$value = $this->dataApi['variation_id'];
		}
		$id = $this->findByRelationship($value, $field, $fromApi);
		if (!$id) {
			if (null === $this->product) {
				$this->product = new \App\Integrations\WooCommerce\Synchronizer\Product($this->synchronizer->controller);
			}
			$id = $this->product->importById($value);
		}
		return $id;
	}

	/**
	 * Get additional inventory data.
	 *
	 * @return array
	 */
	protected function getAdditionalInvDataYf(): array
	{
		$additionalData = [];
		if (!empty($this->dataApi['shipping_lines'])) {
			$serviceId = $this->synchronizer->config->get('shipping_service_id');
		}
		if (!empty($serviceId) && \App\Record::isExists($serviceId)) {
			foreach ($this->dataApi['shipping_lines'] as $shipping) {
				$price = (float) $shipping['total'];
				$tax = $price ? ((float) $shipping['total_tax'] * 100 / $price) : 0;
				$additionalData = [
					'discountmode' => 1,
					'taxmode' => 1,
					'currency' => $this->dataYf['currency_id'],
					'name' => $serviceId,
					'unit' => '',
					'subunit' => '',
					'qty' => 1,
					'price' => $price,
					'discountparam' => '{"aggregationType":"individual","individualDiscountType":"amount","individualDiscount":0}',
					'purchase' => 0,
					'taxparam' => '{"aggregationType":"individual","individualTax":' . $tax . '}',
					'comment1' => $shipping['method_title'],
				];
			}
		}
		return $additionalData;
	}
}
