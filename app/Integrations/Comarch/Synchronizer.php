<?php
/**
 * Comarch base method synchronization file.
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

namespace App\Integrations\Comarch;

/**
 * Comarch abstract base method synchronization class.
 */
class Synchronizer
{
	/** @var string Synchronizer name. */
	protected $name;
	/** @var \App\Integrations\Comarch\Connector\Base Connector. */
	protected $connector;
	/** @var \App\Integrations\Comarch\Map[] Map synchronizer instances. */
	protected $maps;
	/** @var \App\Integrations\Comarch\Config Config instance. */
	public $config;
	/** @var \App\Integrations\Comarch Controller instance. */
	public $controller;
	/** @var array Last scan config data. */
	protected $lastScan = [];
	/** @var string Category name used for the log mechanism */
	const LOG_CATEGORY = 'Integrations/Comarch';
	/** @var int Synchronization direction: one-way from Comarch to YetiForce */
	const DIRECTION_API_TO_YF = 0;
	/** @var int Synchronization direction: one-way from YetiForce to Comarch */
	const DIRECTION_YF_TO_API = 1;
	/** @var int Synchronization direction: two-way */
	const DIRECTION_TWO_WAY = 2;

	/**
	 * Constructor.
	 *
	 * @param \App\Integrations\Comarch $controller
	 */
	public function __construct(\App\Integrations\Comarch $controller)
	{
		$this->name = substr(strrchr(static::class, '\\'), 1);
		$this->connector = $controller->getConnector();
		$this->controller = $controller;
		$this->config = $controller->config;
	}

	/**
	 * Get map model instance.
	 *
	 * @param string $name
	 *
	 * @return \App\Integrations\Comarch\Map
	 */
	public function getMapModel(string $name = ''): Map
	{
		if (empty($name)) {
			$name = rtrim($this->name, 's');
		}
		if (isset($this->maps[$name])) {
			return $this->maps[$name];
		}
		$className = 'App\\Integrations\\Comarch\\' . $this->config->get('connector') . "\\Maps\\{$name}";
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
		\App\Cache::staticSave($cacheKey, $path, $data);
		if ($this->config->get('log_all')) {
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
	 * @param bool   $filterByDate
	 *
	 * @return \App\QueryGenerator
	 */
	public function getFromYf(string $moduleName, bool $filterByDate = false): \App\QueryGenerator
	{
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id'])->permissions = false;
		$queryGenerator->addCondition('comarch_server_id', $this->config->get('id'), 'e');
		if ($filterByDate) {
			if (!empty($this->lastScan['start_date'])) {
				$queryGenerator->addNativeCondition(['<', 'vtiger_crmentity.modifiedtime', $this->lastScan['start_date']]);
			}
			if (!empty($this->lastScan['end_date'])) {
				$queryGenerator->addNativeCondition(['>', 'vtiger_crmentity.modifiedtime', $this->lastScan['end_date']]);
			}
		}
		return $queryGenerator;
	}

	/**
	 * Method to get search criteria Comarch records.
	 *
	 * @param int $pageSize
	 *
	 * @return string
	 */
	public function getSearchCriteria(int $pageSize = 10): string
	{
		// $searchCriteria = ['dates_are_gmt=true'];
		// if (!empty($this->lastScan['start_date'])) {
		// 	$searchCriteria[] = 'modified_before=' . $this->getFormattedTime($this->lastScan['start_date']);
		// }
		// if (!empty($this->lastScan['end_date'])) {
		// 	$searchCriteria[] = 'modified_after=' . $this->getFormattedTime($this->lastScan['end_date']);
		// }
		// $searchCriteria[] = 'per_page=' . $pageSize;
		// $searchCriteria = implode('&', $searchCriteria);
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
		$moduleName = $moduleName ?? $this->getMapModel()->getModule();
		$cacheKey = 'Integrations/Comarch/CRM_ID/' . $moduleName;
		if (\App\Cache::staticHas($cacheKey, $apiId)) {
			return \App\Cache::staticGet($cacheKey, $apiId);
		}
		$queryGenerator = $this->getFromYf($moduleName);
		$queryGenerator->addCondition($this->getMapModel()::FIELD_NAME_ID, $apiId, 'e');
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
		// $moduleName = $moduleName ?? $this->getMapModel()->getModule();
		// $cacheKey = 'Integrations/Comarch/API_ID/' . $moduleName;
		// if (\App\Cache::staticHas($cacheKey, $yfId)) {
		// 	return \App\Cache::staticGet($cacheKey, $yfId);
		// }
		// $apiId = 0;
		// try {
		// 	$recordModel = \Vtiger_Record_Model::getInstanceById($yfId, $moduleName);
		// 	$apiId = $recordModel->get('comarch_id') ?: 0;
		// } catch (\Throwable $th) {
		// 	$this->controller->log('GetApiId', ['comarch_id' => $yfId, 'moduleName' => $moduleName], $th);
		// 	\App\Log::error('Error GetApiId: ' . PHP_EOL . $th->__toString(), self::LOG_CATEGORY);
		// }
		// $this->updateMapIdCache($moduleName, $apiId, $yfId);
		return $apiId;
	}

	/**
	 * Get YF value by API value.
	 *
	 * @param mixed $apiValue
	 * @param array $field
	 *
	 * @return mixed
	 */
	public function getYfValue($apiValue, array $field)
	{
		return '';
	}

	/**
	 * Get YF value by API value.
	 *
	 * @param mixed $yfValue
	 * @param array $field
	 *
	 * @return mixed
	 */
	public function getApiValue($yfValue, array $field)
	{
		return '';
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
		\App\Cache::staticSave('Integrations/Comarch/API_ID/' . $moduleName, $yfId, $apiId);
		\App\Cache::staticSave('Integrations/Comarch/CRM_ID/' . $moduleName, $apiId, $yfId);
	}

	/**
	 * Return parsed time to Comarch time zone.
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
