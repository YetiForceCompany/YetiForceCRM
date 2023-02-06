<?php

/**
 * WooCommerce inventory synchronization map abstract file.
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
 * WooCommerce inventory synchronization map abstract class.
 */
abstract class Inventory extends Base
{
	/** @var array Inventory mapped fields. */
	protected $invFieldMap = [];
	/** @var array Inventory data from YetiForce. */
	protected $invDataYf = [];

	/**
	 * Get inventory data from/for YetiForce.
	 *
	 * @return array
	 */
	public function getInvDataYf(): array
	{
		$this->invDataYf = [];
		foreach ($this->dataApi['line_items'] as $item) {
			$self = clone $this;
			$self->setDataApi($item);
			$invDataYf = $self->getDataYf('invFieldMap');
			$invDataYf['discountmode'] = 1;
			$invDataYf['discountparam'] = '{"aggregationType":"individual","individualDiscountType":"amount","individualDiscount":0}';
			$invDataYf['currency'] = $this->dataYf['currency_id'];
			$this->invDataYf[] = $invDataYf;
		}
		if (method_exists($this, 'getAdditionalInvDataYf') && ($invDataYf = $this->getAdditionalInvDataYf())) {
			$this->invDataYf[] = $invDataYf;
		}
		return $this->invDataYf;
	}

	/**
	 * Create/update product in YF.
	 *
	 * @return void
	 */
	public function saveInYf(): void
	{
		if ($invDataYf = $this->getInvDataYf()) {
			$this->recordModel->initInventoryData($invDataYf, false);
		}
		parent::saveInYf();
	}

	/**
	 * Convert currency.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return int|string int (YF) or string (API)
	 */
	protected function convertCurrency($value, array $field, bool $fromApi)
	{
		if ($fromApi) {
			$currency = \App\Fields\Currency::getIdByCode($value);
			if (empty($currency)) {
				$currency = \App\Fields\Currency::addCurrency($value);
			}
		} else {
			$currency = \App\Fields\Currency::getById($value)['currency_code'];
		}
		return $currency;
	}

	/**
	 * Convert tax in YF.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return int|string int (YF) or string (API)
	 */
	protected function convertTax(float $value, array $field, bool $fromApi)
	{
		$this->dataYf['taxmode'] = 1;
		$total = (float) $this->dataApi['total'];
		$tax = $total ? ($value * 100 / $total) : 0;
		return '{"aggregationType":"individual","individualTax":' . $tax . '}';
	}

	/**
	 * Convert inventory description in YF.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return string
	 */
	protected function convertInvDesc($value, array $field, bool $fromApi): string
	{
		if (empty($value)) {
			return '';
		}
		$desc = '';
		foreach ($value as $value) {
			$desc .= "{$value['display_key']}: {$value['display_value']}\n";
		}
		return nl2br(trim($desc));
	}
}
