<?php

/**
 * Order field map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

class Order extends Inventory
{
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
		'addresslevel1a' => 'billing_address|country_id',
		'addresslevel2a' => 'billing_address|region',
		'addresslevel5a' => 'billing_address|city',
		'addresslevel7a' => 'billing_address|postcode',
		'addresslevel8a' => 'billing_address|street',
		'email' => 'billing_address|email',
		//'shipping_mobile' => 'billing_address|telephone', //incorrect validation
		'first_name' => 'billing_address|firstname',
		'last_name' => 'billing_address|lastname',
		'shipping_addresslevel1a' => 'extension_attributes|shipping_assignments|0|shipping|address|country_id',
		'shipping_addresslevel2a' => 'extension_attributes|shipping_assignments|0|shipping|address|region',
		'shipping_addresslevel5a' => 'extension_attributes|shipping_assignments|0|shipping|address|city',
		'shipping_addresslevel7a' => 'extension_attributes|shipping_assignments|0|shipping|address|postcode',
		'shipping_addresslevel8a' => 'extension_attributes|shipping_assignments|0|shipping|address|street',
		'shipping_email' => 'extension_attributes|shipping_assignments|0|shipping|address|email',
		//'shipping_mobile' => 'extension_attributes|shipping_assignments|0|shipping|address|telephone', //incorrect validation
		'shipping_first_name' => 'extension_attributes|shipping_assignments|0|shipping|address|firstname',
		'shipping_last_name' => 'extension_attributes|shipping_assignments|0|shipping|address|lastname',
		'contactid' => 'customer_id',
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
		'addresslevel8a' => 'implode',
		'shipping_addresslevel8a' => 'implode',
		'addresslevel1a' => 'country',
		'shipping_addresslevel1a' => 'country',
		'date_start' => 'date',
		'ssingleorders_method_payments' => 'map'
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
		'redsys' => 'PLL_REDSYS',
		'banktransfer' => 'PLL_TRANSFER',
	];
}
