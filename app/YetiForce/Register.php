<?php
/**
 * YetiForce register file.
 * Modifying this file or functions that affect the footer appearance will violate the license terms!!!
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\YetiForce;

/**
 * YetiForce register class.
 */
final class Register extends AbstractBase
{
	/** @var string URL */
	public const URL = 'https://api.yetiforce.eu/registrations';

	/** @var string[] Status messages. */
	public const STATUS_MESSAGES = [
		-2 => 'ERR_NO_INTERNET_CONNECTION',
		-1 => 'ERR_IT_HAS_NO_BEEN_6_HOURS_SINCE_THE_LAST_CHECK',
		0 => 'LBL_NOT_REGISTERED',
		1 => 'LBL_WAITING_FOR_ACCEPTANCE',
		2 => 'LBL_INCORRECT_DATA',
		3 => 'LBL_INCOMPLETE_DATA',
		8 => 'LBL_SPECIAL_REGISTRATION',
		9 => 'LBL_REGISTERED',
	];

	/** @var string Registration file path. */
	public const REGISTRATION_FILE = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'app_data' . \DIRECTORY_SEPARATOR . 'registration.php';

	/** @var array Products */
	private static $products;

	/**
	 * Registration config cache.
	 *
	 * @var array
	 */
	private static array $config;

	/** @var array Company data */
	private array $rawCompanyData = [];

	/**
	 * Generate a unique key for the crm.
	 *
	 * @return string
	 */
	public static function getCrmKey(): string
	{
		return sha1(\App\Config::main('application_unique_key'));
	}

	/**
	 * Generate a unique key for the instance.
	 *
	 * @return string
	 */
	public static function getInstanceKey(): string
	{
		return sha1(\App\Config::main('application_unique_key') . trim(preg_replace("(^https?://)", '', \App\Config::main('site_URL')), '/'));
	}

	/**
	 * Send registration data.
	 *
	 * @return bool
	 */
	public function register(): bool
	{
		$this->success = false;
		$client = new ApiClient();
		$method = (new Config())->getToken() ? 'PUT' : 'POST';
		$client->send(self::URL, $method, ['form_params' => $this->getData()]);
		$this->error = $client->getError();
		if (!$this->error && ($code = $client->getStatusCode())) {
			$content = $client->getResponseBody();
			$this->success = 204 === $code || (\in_array($code, [200, 201]) && $content && (new Config())->setToken(\App\Json::decode($content)));
		} elseif (409 === $client->getStatusCode() && false !== stripos($this->error, 'app')) {
			throw new \App\Exceptions\AppException('ERR_RECREATE_APP_ACCESS');
		}

		return $this->success;
	}

	/**
	 * Check registration status.
	 *
	 * @return bool
	 */
	public function check(): bool
	{
		try {
			$client = new ApiClient();
			$this->success = $client->send(self::URL . '/status', 'GET');
			$this->error = $client->getError();
			if (!$this->error && 200 === $client->getStatusCode()) {
				$this->updateMetaData(\App\Json::decode($client->getResponseBody()));
				$this->success = true;
			} elseif (409 === $client->getStatusCode() && false !== stripos($this->error, 'app')) {
				(new self())->recreate();
				throw new \App\Exceptions\AppException('ERR_RECREATE_APP_ACCESS');
			}
		} catch (\Throwable $e) {
			$this->success = false;
			$this->error = $e->getMessage();
			\App\Log::error($e->getMessage(), __METHOD__);
		}

		return $this->success;
	}

	/**
	 * Recreate access.
	 *
	 * @return void
	 */
	public function recreate()
	{
		try {
			$client = new ApiClient();
			$this->success = $client->send(self::URL . '/copy', 'POST', ['form_params' => ['newAppId' => self::getInstanceKey()]]);
			$this->error = $client->getError();
			if (!$this->error && ($code = $client->getStatusCode())) {
				$content = $client->getResponseBody();
				$this->success = \in_array($code, [200, 201]) && $content && (new Config())->setToken(\App\Json::decode($content));
			}
		} catch (\Throwable $e) {
			$this->success = false;
			$this->error = $e->getMessage();
			\App\Log::error($e->getMessage(), __METHOD__);
		}
	}

	/**
	 * Set company data.
	 *
	 * @param array $data
	 *
	 * @return self
	 */
	public function setRawCompanyData(array $data): self
	{
		$this->rawCompanyData = $data;

		return $this;
	}

	/**
	 * Get registration status.
	 *
	 * @param bool $force
	 *
	 * @return int
	 */
	public function getStatus(bool $force = false): int
	{
		if ($force) {
			$this->check();
		}
		return (int) (self::getConf()['status'] ?? 0);
	}

	/**
	 * Is the system is properly registered.
	 *
	 * @return bool
	 */
	public static function isRegistered(): bool
	{
		return (new self())->getStatus() >= 6 && self::getConfValue('appId') === self::getInstanceKey();
	}

	/**
	 * Get registration product.
	 *
	 * @param string $name
	 *
	 * @return array
	 */
	public static function getProduct(string $name): array
	{
		if (!isset(self::$products)) {
			self::$products = [];
			foreach (self::getConf()['subscriptions'] ?? [] as $row) {
				self::$products[$row['product']] = $row;
			}
		}

		return self::$products[$name] ?? [];
	}

	/**
	 * Gets products.
	 *
	 * @return array
	 */
	public static function getProducts(): array
	{
		$products = [];
		foreach (self::getConf()['subscriptions'] ?? [] as $row) {
			$products[] = array_intersect_key($row, array_flip(['product', 'expiresAt']));
		}

		return $products;
	}

	/**
	 * Get provider.
	 *
	 * @return string
	 */
	public static function getProvider(): string
	{
		$path = ROOT_DIRECTORY . '/app_data/installSource.txt';
		if (file_exists($path)) {
			return trim(file_get_contents($path));
		}
		return getenv('PROVIDER') ?: getenv('provider') ?: '';
	}

	/**
	 * Should enforce registration.
	 *
	 * @return bool
	 */
	public static function shouldEnforceRegistration(): bool
	{
		$interval = null;
		if ($registrationDate = (self::getConf()['last_forced_reg_time'] ?? null)) {
			$interval = (new \DateTime('now', new \DateTimeZone('GMT')))->diff(new \DateTime($registrationDate, new \DateTimeZone('GMT')));
		}

		return !self::isRegistered() && (self::getInstanceKey() !== self::getConfValue('appId') || ($interval && $interval->days > 6));
	}

	/**
	 * Was registration attempted.
	 *
	 * @return bool
	 */
	public static function isPreRegistered(): bool
	{
		return self::isRegistered() || self::getInstanceKey() === self::getConfValue('appId');
	}

	/**
	 * Get parsed company registration data.
	 *
	 * @return array
	 */
	public function getCompanyData(): array
	{
		if (!$this->rawCompanyData) {
			$this->setRawCompanyData(\App\Company::getCompany());
		}

		return [
			'name' => $this->rawCompanyData['name'] ?: null,
			'vatId' => $this->rawCompanyData['vat_id'] ?: null,
			'country' => $this->rawCompanyData['country'] ?: null,
			'industry' => $this->rawCompanyData['industry'] ?: null,
			'webpage' => $this->rawCompanyData['website'] ?: null,
		];
	}

	/**
	 * Send statistics.
	 *
	 * @return bool
	 */
	public function sendStats(): bool
	{
		$this->success = false;
		$client = new ApiClient();
		$client->send(self::URL . '/stats', 'PUT', ['form_params' => ['crmVersion' => \App\Version::get(), 'stats' => $this->getStats()]]);
		$this->error = $client->getError();

		return $this->success = !$this->error && \in_array($client->getStatusCode(), [200, 204]);
	}

	/**
	 * Check statuses and send statistics.
	 *
	 * @return void
	 */
	public function send()
	{
		if (($date = $this->getConfValue('last_check_time'))
			&& (new \DateTime(date('Y-m-d')))->diff(new \DateTime(date('Y-m-d', strtotime($date))))->days > 0
			&& $this->check()) {
			$this->sendStats();
		}
	}

	/**
	 * Get the number of records by modules.
	 *
	 * @return array
	 */
	private function getStats(): array
	{
		$modules = [
			'Accounts', 'Campaigns', 'SSalesProcesses', 'SQuotes', 'SSingleOrders', 'Project', 'HelpDesk', 'FInvoice', 'PaymentsIn', 'PaymentsOut', 'FInvoiceCost', 'ISTN', 'IGRN', 'Products', 'Assets', 'Services', 'OSSMailView', 'Documents', 'Notification', 'Calendar'
		];
		$stats['Modules'] = (new \App\Db\Query())->select(['setype', 'count' => new \yii\db\Expression('count(1)'), 'last_create' => new \yii\db\Expression('MAX(CAST(createdtime AS DATE))')])->from('vtiger_crmentity')->where(['setype' => $modules])->groupBy('setype')->all();
		$stats['Crons'] = (new \App\Db\Query())->from('vtiger_cron_task')->count();
		$stats['Workflows'] = (new \App\Db\Query())->from('com_vtiger_workflows')->count();

		$usersData = (new \App\Db\Query())->select(['count' => new \yii\db\Expression('COUNT(*)'), 'status', 'last_create' => new \yii\db\Expression('MAX(CAST(date_entered AS DATE))')])->from('vtiger_users')->groupBy(['status'])->all();
		$users = array_column($usersData, 'count', 'status');
		$dates = array_column($usersData, 'last_create');
		$users['last_create'] = max($dates);
		$stats['Users'] = $users;

		return $stats;
	}

	/**
	 * Get registration data.
	 *
	 * @return string[]
	 */
	private function getData(): array
	{
		return array_merge([
			'language' => \App\Language::getLanguage() ?: null,
			'timezone' => date_default_timezone_get(),
			'appId' => self::getInstanceKey(),
			'crmKey' => self::getCrmKey(),
			'crmVersion' => \App\Version::get(),
			'provider' => self::getProvider() ?: null,
		], $this->getCompanyData());
	}

	/**
	 * Update registration data.
	 *
	 * @param string[] $data
	 */
	private function updateMetaData(array $data): bool
	{
		$products = $data['subscriptions'] ?? [];
		foreach ($products as $product) {
			$children = $product['children'] ?? [];
			['product' => $product,'expiresAt' => $date] = $product;
			foreach ($children as $child) {
				$products[] = ['product' => $child['product'], 'expiresAt' => $date];
			}
		}
		$data['subscriptions'] = $products;
		$conf = self::getConf();

		self::$config = array_merge($conf, $data);
		self::$config['last_check_time'] = date('Y-m-d H:i:s');
		self::$config['appId'] = self::getInstanceKey();

		if (self::isRegistered()) {
			unset(self::$config['last_forced_reg_time']);
		} else {
			self::$config['last_forced_reg_time'] ??= gmdate('Y-m-d H:i:s');
		}

		$data = (new Encryption())->encrypt(self::$config, true);
		return \App\Utils::saveToFile(
			self::REGISTRATION_FILE,
			$data,
			'Modifying this file or functions that affect the footer appearance will violate the license terms!!!',
			0,
		);
	}

	/**
	 * Get registration config.
	 *
	 * @return array
	 */
	private static function getConf(): array
	{
		if (isset(self::$config)) {
			return self::$config;
		}
		if (!file_exists(self::REGISTRATION_FILE)) {
			return self::$config = [];
		}
		\App\Cache::resetFileCache(self::REGISTRATION_FILE);

		try {
			$data = file_get_contents(self::REGISTRATION_FILE);
			[, ,$registyData] = explode("\n", $data);
			self::$config = (new Encryption())->decrypt($registyData);
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
			self::$config = [];
		}

		return self::$config;
	}

	/**
	 * Get configuration value by key.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	private static function getConfValue(string $key)
	{
		return self::getConf()[$key] ?? null;
	}
}
