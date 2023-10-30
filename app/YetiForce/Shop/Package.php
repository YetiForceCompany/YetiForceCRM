<?php
/**
 * YetiForce shop product price Package file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Klaudia Łozowska <k.lozowska@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\YetiForce\Shop;

/**
 * YetiForce shop product price Package class.
 */
class Package
{
	private const PAYMENT_FREQ_MAP = [
		'monthly' => 'M',
		'yearly' => 'Y',
	];

	/** @var string Package ID */
	private string $id;

	/** @var string Name. */
	private string $name;

	/** @var string Label. */
	private ?string $label;

	/** @var int Price net. */
	private $priceNet;

	/** @var float Price gross. */
	private $priceGross;

	/** @var string Currency code */
	private ?string $currencyCode;

	/** @var string Payment frequency. */
	private ?string $paymentFrequency;

	/** @var int[] User Terms. */
	private int $max = 0;

	/**
	 * Construct.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->id = $data['id'] ?? '';
		$this->priceNet = (int) ($data['priceNet'] ?? 0) / 100;
		$this->priceGross = (int) ($data['priceGross'] ?? 0) / 100;
		$this->currencyCode = $data['currencyCode'] ?? '';
		$this->paymentFrequency = $data['paymentFrequency'] ?? '';
		$this->name = $data['packageType']['name'] ?? '';
		$this->label = $data['packageType']['label'] ?? '';
		$this->name = $data['packageType']['name'] ?? '';
		$this->max = $data['packageType']['maxUsers'] ?? 0;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get net price
	 *
	 * @param bool $format
	 * @return int|float|string
	 */
	public function getPriceNet(bool $format = false)
	{
		return $format ? number_format($this->priceNet, 2, '.', '') : $this->priceNet;
	}

	/**
	 * Get gross price
	 *
	 * @param bool $format
	 * @return int|float|string
	 */
	public function getPriceGross(bool $format = false)
	{
		return $format ? number_format($this->priceGross, 2, '.', '') : $this->priceGross;
	}

	/**
	 * Check if package is available.
	 *
	 * @return bool
	 */
	public function isAvailable(): bool
	{
		return !$this->max || \App\User::getNumberOfUsers() <= $this->max;
	}

	/**
	 * Get label.
	 *
	 * @return ?string
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	/**
	 * Get currency Code
	 *
	 * @return string
	 */
	public function getCurrencyCode(): string
	{
		return $this->currencyCode;
	}

	/**
	 * Get payment frequency
	 *
	 * @return string
	 */
	public function getPaymentFrequency(): string
	{
		return $this->paymentFrequency;
	}

	/**
	 * Get short name payment frequency
	 *
	 * @return string
	 */
	public function getPaymentFrequencyShort(): string
	{
		$value = $this->getPaymentFrequency();
		return self::PAYMENT_FREQ_MAP[$value] ?? $value;
	}

	/**
	 * Get full name for payment frequency
	 *
	 * @return string
	 */
	public function getPaymentFrequencyLabel(): string
	{
		return 'LBL_SHOP_PAYMENT_FREQUENCY_' . $this->getPaymentFrequencyShort();
	}

	/**
	 * Get price.
	 *
	 * @return int
	 */
	public function getPrice(): int
	{
		return $this->getPriceNet();
	}

	/**
	 * Get package ID.
	 *
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}
}
