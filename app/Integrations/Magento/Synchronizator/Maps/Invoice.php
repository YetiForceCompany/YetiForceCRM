<?php

/**
 * Invoice field map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

class Invoice extends Inventory
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
		'finvoice_type' => 'PLL_DOMESTIC_INVOICE',
	];

	/**
	 * {@inheritdoc}
	 */
	public static $mappedFields = [
		'subject' => 'increment_id',
		'finvoice_status' => 'state',
		'finvoice_formpayment' => 'payment|method',
		'issue_time' => 'created_at',
		'addresslevel1a' => 'billing_address|country_id',
		'addresslevel2a' => 'billing_address|region',
		'addresslevel5a' => 'billing_address|city',
		'addresslevel7a' => 'billing_address|postcode',
		'addresslevel8a' => 'billing_address|street',
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

	/**
	 * {@inheritdoc}
	 */
	public static $fieldsType = [
		'finvoice_status' => 'map',
		'addresslevel8a' => 'implode',
		'addresslevel1a' => 'country',
		'issue_time' => 'date',
		'finvoice_formpayment' => 'map'
	];

	/**
	 * Payment method value map.
	 *
	 * @var array
	 */
	public static $finvoice_formpayment = [
		'banktransfer' => 'PLL_TRANSFER',
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
}
