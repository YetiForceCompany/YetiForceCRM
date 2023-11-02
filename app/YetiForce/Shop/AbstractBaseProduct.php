<?php
/**
 * YetiForce shop AbstractBaseProduct file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Klaudia Łozowska <k.lozowska@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\YetiForce\Shop;

/**
 * YetiForce shop AbstractBaseProduct class.
 */
abstract class AbstractBaseProduct
{
	private const DEFAULT_CURRENCY = 'EUR';

	/**
	 * Product ID.
	 *
	 * @var string
	 */
	protected string $id;

	/**
	 * Product label.
	 *
	 * @var string
	 */
	protected string $label;

	/**
	 * Product name.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Is the product active.
	 *
	 * @var bool
	 */
	protected bool $active;

	/**
	 * Is the product featured.
	 *
	 * @var bool
	 */
	protected bool $featured;

	/**
	 * Product category.
	 *
	 * @var string
	 */
	protected string $category;

	/**
	 * Product website.
	 *
	 * @var string
	 */
	protected string $website;

	/**
	 * Price packages.
	 *
	 * @var Package[]
	 */
	protected array $packages = [];

	/**
	 * Fit package.
	 *
	 * @var Package
	 */
	protected $package;

	/**
	 * Expiration date.
	 *
	 * @var string|null
	 */
	protected ?string $expirationDate;

	/**
	 * Introduction / short description.
	 *
	 * @var string
	 */
	protected string $introduction;

	/**
	 * Description.
	 *
	 * @var string
	 */
	protected string $description;

	/**
	 * Image.
	 *
	 * @var string
	 */
	protected ?string $image;

	/** @var bool Disabled product */
	protected bool $disabled = false;

	/** @var bool Status */
	private bool $status = false;

	/**
	 * Constructor.
	 *
	 * @param string $productName
	 */
	public function __construct(string $productName)
	{
		$this->status = false;
		$this->name = $productName;

		$statusData = \App\YetiForce\Register::getProduct($productName);
		if ($statusData) {
			$expiresAt = $statusData['expiresAt'];
			$this->expirationDate = (new \DateTime($expiresAt, new \DateTimeZone('GMT')))->setTimezone(new \DateTimeZone(\App\Fields\DateTime::getTimeZone()))->format('Y-m-d');
			$this->status = strtotime($this->expirationDate) >= strtotime(date('Y-m-d'));
		} else {
			$this->expirationDate = null;
		}
	}

	/**
	 * Get subscription status.
	 *
	 * @return bool
	 */
	public function getStatus(): bool
	{
		return $this->status;
	}

	/**
	 * Check if the product is configured correctly.
	 *
	 * @return bool
	 */
	public function isConfigured(): bool
	{
		return true;
	}

	/**
	 * Construct.
	 *
	 * @param array $data
	 *
	 * @return static
	 */
	public static function fromArray(array $data)
	{
		$name = $data['name'] ?? '';
		$self = new static($name);
		$self->label = \App\Purifier::purifyByType($data['label'] ?? '', \App\Purifier::TEXT);
		$self->id = $data['id'] ?? '';
		$self->featured = $data['featured'] ?? false;
		$self->category = $data['category'] ?? '';
		$self->website = $data['website'] ?? '';
		$self->introduction = \App\Purifier::purifyByType($data['shortDescription'] ?? '', \App\Purifier::TEXT);
		$self->description = \App\Purifier::decodeHtml(\App\Purifier::purifyByType($data['description'] ?? '', \App\Purifier::HTML));
		$self->image = $data['imageUrl'] ?? '';

		$packages = [];
		$currencyCode = \App\Fields\Currency::getDefault()['currency_code'];
		foreach ($data['packages'] as $packageData) {
			$package = new Package($packageData);
			if ($package->isAvailable()) {
				$packages[$package->getCurrencyCode()][] = $package;
			}
		}
		if (isset($packages[$currencyCode])) {
			$self->packages = $packages[$currencyCode];
		} elseif (isset($packages[self::DEFAULT_CURRENCY])) {
			$self->packages = $packages[self::DEFAULT_CURRENCY];
		} elseif ($packages) {
			$self->packages = current($packages);
		}

		return $self;
	}

	/**
	 * Get product ID.
	 *
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Get product label.
	 *
	 * @return string
	 */
	public function getLabel(): string
	{
		return $this->label;
	}

	/**
	 * Get product name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get category.
	 *
	 * @return string
	 */
	public function getCategory(): string
	{
		return $this->category;
	}

	/**
	 * Price packages.
	 *
	 * @return Package[]
	 */
	public function getPackages(): array
	{
		return $this->packages;
	}

	/**
	 * Get product price.
	 *
	 * @return int
	 */
	public function getPrice(): int
	{
		return $this->package->getPrice();
	}

	/**
	 * Get currency code.
	 *
	 * @return string
	 */
	public function getCurrencyCode(): string
	{
		return $this->package->getCurrencyCode();
	}

	/**
	 * Get fit package.
	 *
	 * @return Package|null
	 */
	public function getFitPackage(): ?Package
	{
		if (!$this->package) {
			$packages = $this->getPackages();
			usort($packages, fn ($a, $b) => $a->getPriceNet() <=> $b->getPriceNet());
			$this->package = current($packages);
		}

		return $this->package;
	}

	/**
	 * Get product description.
	 *
	 * @return string
	 */
	public function getIntroduction(): string
	{
		return $this->introduction;
	}

	/**
	 * Get product description.
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * Get product image.
	 *
	 * @return ?string
	 */
	public function getImage(): ?string
	{
		return $this->image ?? '';
	}

	/**
	 * The period for which the service is purchased.
	 *
	 * @return string
	 */
	public function getPaymentFrequency(): string
	{
		return $this->package->getPaymentFrequency();
	}

	/**
	 * Get short period name for which the service is purchased.
	 *
	 * @return string
	 */
	public function getPaymentFrequencyShort(): string
	{
		return $this->package->getPaymentFrequencyShort();
	}

	public function isExpired(): bool
	{
		return isset($this->expirationDate) && !$this->status;
	}

	/**
	 * Get variable product.
	 *
	 * @return array
	 */
	public function getVariable(): array
	{
		return array_merge([
			'cmd' => '_xclick-subscriptions',
			'no_shipping' => 1,
			'no_note' => 1,
			'src' => 1,
			'sra' => 1,
			't3' => 'M',
			'p3' => 1,
			'item_name' => $this->name,
			'currency_code' => $this->getCurrencyCode(),
			'on0' => 'Package',
			'os0' => $this->package->getName(),
			'a3' => $this->package->getPriceGross()
		], \App\YetiForce\Shop::getVariablePayments());
	}

	/**
	 * Show alert in marketplace.
	 *
	 * @param bool $require
	 *
	 * @return array
	 */
	public function getAlertMessage(bool $require = true): string
	{
		$message = '';
		$status = $this->getStatus();
		if ($this->disabled) {
			$message = 'LBL_FUNCTIONALITY_NOT_AVAILABLE';
		} elseif ($this->isExpired()) {
			$message = 'LBL_SUBSCRIPTION_HAS_EXPIRED';
		} elseif ($status && !$this->isConfigured()) {
			$message = 'LBL_FUNCTIONALITY_HAS_NOT_YET_BEEN_ACTIVATED';
		} elseif ($require && !$status) {
			$message = 'LBL_PAID_FUNCTIONALITY';
		}

		return $message;
	}

	/**
	 * Analyze the configuration.
	 *
	 * @return array
	 */
	public function analyzeConfiguration(): array
	{
		return [];
	}

	/**
	 * Product modal additional buttons.
	 *
	 * @return \Vtiger_Link_Model[]
	 */
	public function getAdditionalButtons(): array
	{
		return [];
	}

	/**
	 * Switch button to activate/deactivate service.
	 *
	 * @return \Vtiger_Link_Model|null
	 */
	public function getSwitchButton(): ?\Vtiger_Link_Model
	{
		return null;
	}

	/**
	 * Get expiration date.
	 *
	 * @return string|null
	 */
	public function getExpirationDate(): ?string
	{
		return $this->expirationDate ?? null;
	}

	/**
	 * Check if the service is active.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->active;
	}

	/**
	 * Check if product is available.
	 *
	 * @return bool
	 */
	public function isAvailable(): bool
	{
		return !empty($this->getFitPackage());
	}
}
