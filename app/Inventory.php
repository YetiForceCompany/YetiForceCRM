<?php
/**
 * The file contains an inventory class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace App;

/**
 * Inventory class. Calculate net, gross, margins.
 */
class Inventory
{
	/**
	 * Quantity.
	 *
	 * @var float
	 */
	public $quantity = 0;

	/**
	 * Quantity.
	 *
	 * @var float
	 */
	public $price = 0;

	/**
	 * Tax param.
	 *
	 * @var array
	 */
	public $taxParam = [];

	/**
	 * Discount param.
	 *
	 * @var array
	 */
	public $discountParam = [];

	/**
	 * Quantity.
	 *
	 * @var float
	 */
	public $purchase = 0;

	/**
	 * The number of decimal digits to round to.
	 *
	 * @var int
	 */
	public $precision = 8;

	/**
	 * Construct.
	 *
	 * @param array $item
	 */
	public function __construct(array $item = [])
	{
		$this->setFromArray($item);
	}

	/**
	 * Set from array.
	 *
	 * @param array $item
	 *
	 * @return self
	 */
	public function setFromArray(array $item): self
	{
		$this->quantity = $item['qty'] ?? 0;
		$this->price = $item['price'] ?? 0;
		$this->taxParam = \App\Json::isEmpty($item['taxparam'] ?? '') ? [] : \App\Json::decode($item['taxparam']);
		$this->discountParam = \App\Json::isEmpty($item['discountparam'] ?? '') ? [] : \App\Json::decode($item['discountparam']);
		$this->purchase = $item['purchase'] ?? 0;
		return $this;
	}

	/**
	 * Set the number of decimal places.
	 *
	 * @param int $precision
	 *
	 * @return self
	 */
	public function setPrecision(int $precision): self
	{
		$this->precision = $precision;
		return $this;
	}

	/**
	 * Get total price.
	 *
	 * @return float
	 */
	public function getTotalPrice(): float
	{
		return $this->roundMethod($this->quantity * $this->price);
	}

	/**
	 * Get net price.
	 *
	 * @return float
	 */
	public function getNetPrice(): float
	{
		return $this->roundMethod($this->getTotalPrice() - $this->getDiscount());
	}

	/**
	 * Get gross price.
	 *
	 * @return float
	 */
	public function getGross(): float
	{
		return $this->roundMethod($this->getNetPrice() + $this->getTax());
	}

	/**
	 * Get discount.
	 *
	 * @return float
	 */
	public function getDiscount(): float
	{
		$returnVal = 0.0;
		if (!empty($this->discountParam)) {
			$aggregationType = $this->discountParam['aggregationType'];
			$discountType = $this->discountParam["{$aggregationType}DiscountType"] ?? 'percentage';
			$discount = $this->discountParam["{$aggregationType}Discount"];
			$totalPrice = $this->getTotalPrice();
			if ('amount' === $discountType) {
				$returnVal = $totalPrice - $discount;
			} elseif ('percentage' === $discountType) {
				$returnVal = $totalPrice - ($totalPrice * $discount / 100.00);
			}
		}
		return $this->roundMethod($returnVal);
	}

	/**
	 * Get tax.
	 *
	 * @return float
	 */
	public function getTax(): float
	{
		$returnVal = 0.0;
		if (!empty($this->taxParam)) {
			$aggregationType = $this->taxParam['aggregationType'];
			$returnVal = $this->getNetPrice() * $this->taxParam["{$aggregationType}Tax"] / 100.00;
		}
		return $this->roundMethod($returnVal);
	}

	/**
	 * Get margin.
	 *
	 * @return float
	 */
	public function getMargin(): float
	{
		return $this->roundMethod($this->getNetPrice() - $this->purchase);
	}

	/**
	 * Get a percentage margin.
	 *
	 * @return float
	 */
	public function getMarginPercent(): float
	{
		return empty($this->purchase) ? 0.0 : $this->roundMethod(100.0 * $this->getMargin() / $this->purchase);
	}

	/**
	 * Round method.
	 *
	 * @param float $value
	 *
	 * @return float
	 */
	private function roundMethod(float $value): float
	{
		return round($value, $this->precision);
	}
}
