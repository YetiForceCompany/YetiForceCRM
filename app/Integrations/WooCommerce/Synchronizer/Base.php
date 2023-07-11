<?php
/**
 * WooCommerce base synchronization file.
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

namespace App\Integrations\WooCommerce\Synchronizer;

/**
 * WooCommerce base synchronization class.
 */
abstract class Base
{
	/** @var string Category name used for the log mechanism */
	const LOG_CATEGORY = 'Integrations/WooCommerce';
	/** @var int Synchronization direction: one-way from WooCommerce to YetiForce */
	const DIRECTION_API_TO_YF = 0;
	/** @var int Synchronization direction: one-way from YetiForce to WooCommerce */
	const DIRECTION_YF_TO_API = 1;
	/** @var int Synchronization direction: two-way */
	const DIRECTION_TWO_WAY = 2;
	/** @var \App\Integrations\WooCommerce\Config Config instance. */
	public $config;
	/** @var \App\Integrations\WooCommerce Controller instance. */
	public $controller;
	/** @var \App\Integrations\WooCommerce\Connector\Base Connector. */
	protected $connector;
	/** @var \App\Integrations\WooCommerce\Synchronizer\Maps\Base Map synchronizer instance. */
	protected $maps;
	/** @var array Last scan config data. */
	protected $lastScan = [];

	/**
	 * Constructor.
	 *
	 * @param \App\Integrations\WooCommerce $controller
	 */
	public function __construct(\App\Integrations\WooCommerce $controller)
	{
		$this->connector = $controller->getConnector();
		$this->controller = $controller;
		$this->config = $controller->config;
	}

	/**
	 * Main process function.
	 *
	 * @return void
	 */
	abstract public function process(): void;

	/**
	 * Get map model instance.
	 *
	 * @param string $name
	 *
	 * @return \App\Integrations\WooCommerce\Synchronizer\Maps\Base
	 */
	public function getMapModel(string $name = ''): Maps\Base
	{
		if (empty($name)) {
			$name = rtrim(substr(strrchr(static::class, '\\'), 1), 's');
		}
		if (isset($this->maps[$name])) {
			return $this->maps[$name];
		}
		$className = "App\\Integrations\\WooCommerce\\Synchronizer\\Maps\\{$name}";
		if (isset($this->config->get('maps')[$name])) {
			$className = $this->config->get('maps')[$name];
		}
		return $this->maps[$name] = new $className($this);
	}

	/**
	 * Get data by path from API.
	 *
	 * @param string $path
	 * @param bool   $cache
	 *
	 * @return array
	 */
	public function getFromApi(string $path, bool $cache = true): array
	{
		$cacheKey = self::LOG_CATEGORY . '/API';
		if ($cache && \App\Cache::staticHas($cacheKey, $path)) {
			return \App\Cache::staticGet($cacheKey, $path);
		}
		$data = \App\Json::decode($this->connector->request('GET', $path));
		foreach ($data as &$item) {
			if (isset($item['_links'])) {
				unset($item['_links']);
			}
		}
		\App\Cache::staticSave($cacheKey, $path, $data);
		if ($this->config->get('logAll')) {
			$this->controller->log('Get from API', [
				'path' => $path,
				'rows' => \count($data),
			]);
		}
		return $data;
	}

	/**
	 * Get QueryGenerator to retrieve data from YF.
	 *
	 * @param string $moduleName
	 *
	 * @return \App\QueryGenerator
	 */
	public function getFromYf(string $moduleName): \App\QueryGenerator
	{
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id'])->permissions = false;
		$queryGenerator->addCondition('woocommerce_server_id', $this->config->get('id'), 'e');
		return $queryGenerator;
	}

	/**
	 * Method to get search criteria WooCommerce records.
	 *
	 * @param int $pageSize
	 *
	 * @return string
	 */
	public function getSearchCriteria(int $pageSize = 10): string
	{
		$searchCriteria = ['dates_are_gmt=true'];
		if (!empty($this->lastScan['start_date'])) {
			$searchCriteria[] = 'modified_before=' . $this->getFormattedTime($this->lastScan['start_date']);
		}
		if (!empty($this->lastScan['end_date'])) {
			$searchCriteria[] = 'modified_after=' . $this->getFormattedTime($this->lastScan['end_date']);
		}
		$searchCriteria[] = 'per_page=' . $pageSize;
		$searchCriteria = implode('&', $searchCriteria);
		return $searchCriteria ?? 'searchCriteria';
	}

	/**
	 * Get YF id by API id.
	 *
	 * @param int         $apiId
	 * @param string|null $moduleName
	 *
	 * @return int
	 */
	public function getYfId(int $apiId, ?string $moduleName = null): int
	{
		$moduleName ??= $this->getMapModel()->getModule();
		$cacheKey = 'Integrations/WooCommerce/CRM_ID/' . $moduleName;
		if (\App\Cache::staticHas($cacheKey, $apiId)) {
			return \App\Cache::staticGet($cacheKey, $apiId);
		}
		$queryGenerator = $this->getFromYf($moduleName);
		$queryGenerator->addCondition('woocommerce_id', $apiId, 'e');
		$yfId = $queryGenerator->createQuery()->scalar() ?: 0;
		$this->updateMapIdCache($moduleName, $apiId, $yfId);
		return $yfId;
	}

	/**
	 * Get YF id by API id.
	 *
	 * @param int     $yfId
	 * @param ?string $moduleName
	 *
	 * @return int
	 */
	public function getApiId(int $yfId, ?string $moduleName = null): int
	{
		$moduleName ??= $this->getMapModel()->getModule();
		$cacheKey = 'Integrations/WooCommerce/API_ID/' . $moduleName;
		if (\App\Cache::staticHas($cacheKey, $yfId)) {
			return \App\Cache::staticGet($cacheKey, $yfId);
		}
		$apiId = 0;
		try {
			$recordModel = \Vtiger_Record_Model::getInstanceById($yfId, $moduleName);
			$apiId = $recordModel->get('woocommerce_id') ?: 0;
		} catch (\Throwable $th) {
			$this->controller->log('GetApiId', ['woocommerce_id' => $yfId, 'moduleName' => $moduleName], $th);
			\App\Log::error('Error GetApiId: ' . PHP_EOL . $th->__toString(), self::LOG_CATEGORY);
		}
		$this->updateMapIdCache($moduleName, $apiId, $yfId);
		return $apiId;
	}

	/**
	 * Update the identifier mapping of both systems.
	 *
	 * @param string $moduleName
	 * @param int    $apiId
	 * @param int    $yfId
	 *
	 * @return void
	 */
	public function updateMapIdCache(string $moduleName, int $apiId, int $yfId): void
	{
		\App\Cache::staticSave('Integrations/WooCommerce/API_ID/' . $moduleName, $yfId, $apiId);
		\App\Cache::staticSave('Integrations/WooCommerce/CRM_ID/' . $moduleName, $apiId, $yfId);
	}

	/**
	 * Return parsed time to WooCommerce time zone.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function getFormattedTime(string $value): string
	{
		return \DateTimeField::convertTimeZone($value, \App\Fields\DateTime::getTimeZone(), 'UTC')->format('Y-m-d\\TH:i:s');
	}
}
