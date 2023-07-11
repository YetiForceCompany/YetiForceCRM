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
	/** @var string Category name used for the log mechanism */
	const LOG_CATEGORY = 'Integrations/Comarch';
	/** @var string The name of the configuration parameter for rows limit */
	const LIMIT_NAME = '';
	/** @var int Synchronization direction: one-way from Comarch to YetiForce */
	const DIRECTION_API_TO_YF = 0;
	/** @var int Synchronization direction: one-way from YetiForce to Comarch */
	const DIRECTION_YF_TO_API = 1;
	/** @var int Synchronization direction: two-way */
	const DIRECTION_TWO_WAY = 2;
	/** @var \App\Integrations\Comarch\Config Config instance. */
	public $config;
	/** @var \App\Integrations\Comarch Controller instance. */
	public $controller;
	/** @var string Synchronizer name. */
	protected $name;
	/** @var \App\Integrations\Comarch\Connector\Base Connector. */
	protected $connector;
	/** @var \App\Integrations\Comarch\Map[] Map synchronizer instances. */
	protected $maps;
	/** @var array Last scan config data. */
	protected $lastScan = [];
	/** @var int[] Imported ids */
	protected $imported = [];
	/** @var int[] Exported ids */
	protected $exported = [];

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
	 * Main process function.
	 * Required for master synchronizers, not required for dependent ones.
	 *
	 * @return void
	 */
	public function process(): void
	{
		throw new \App\Exceptions\AppException('Function not implemented');
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
		$cacheKey = $this::LOG_CATEGORY . '/API';
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
	 * Method to get search conditions in the Comarch API.
	 *
	 * @return string
	 */
	public function getFromApiCond(): string
	{
		$searchCriteria = [];
		if (!empty($this->lastScan['start_date'])) {
			$searchCriteria[] = 'dataCzasModyfikacjiDo=' . $this->getFormattedTime($this->lastScan['start_date']);
		}
		if (!empty($this->lastScan['end_date'])) {
			$searchCriteria[] = 'dataCzasModyfikacjiOd=' . $this->getFormattedTime($this->lastScan['end_date']);
		}
		$searchCriteria[] = 'limit=' . $this->config->get($this::LIMIT_NAME);
		$searchCriteria = implode('&', $searchCriteria);
		return $searchCriteria ?? '';
	}

	/**
	 * Get YF id by API id.
	 *
	 * @param int         $apiId
	 * @param string|null $moduleName
	 *
	 * @return int|null
	 */
	public function getYfId(int $apiId, ?string $moduleName = null): ?int
	{
		$moduleName ??= $this->getMapModel()->getModule();
		$cacheKey = 'Integrations/Comarch/CRM_ID/' . $moduleName;
		if (\App\Cache::staticHas($cacheKey, $apiId)) {
			return \App\Cache::staticGet($cacheKey, $apiId);
		}
		$queryGenerator = $this->getFromYf($moduleName);
		$queryGenerator->addCondition($this->getMapModel()::FIELD_NAME_ID, $apiId, 'e');
		$yfId = $queryGenerator->createQuery()->scalar() ?: null;
		if (null !== $yfId) {
			$this->updateMapIdCache($moduleName, $apiId, $yfId);
		}
		return $yfId;
	}

	/**
	 * Get YF id by API id.
	 *
	 * @param int     $yfId
	 * @param ?string $moduleName
	 * @param mixed   $apiValue
	 * @param array   $field
	 *
	 * @return int
	 */
	public function getApiId(int $yfId, ?string $moduleName = null): int
	{
		$moduleName ??= $this->getMapModel()->getModule();
		$cacheKey = 'Integrations/Comarch/API_ID/' . $moduleName;
		if (\App\Cache::staticHas($cacheKey, $yfId)) {
			return \App\Cache::staticGet($cacheKey, $yfId);
		}
		$apiId = 0;
		try {
			$recordModel = \Vtiger_Record_Model::getInstanceById($yfId, $moduleName);
			$apiId = $recordModel->get('comarch_id') ?: 0;
		} catch (\Throwable $th) {
			$this->controller->log('GetApiId', ['comarch_id' => $yfId, 'moduleName' => $moduleName], $th);
			\App\Log::error('Error GetApiId: ' . PHP_EOL . $th->__toString(), $this::LOG_CATEGORY);
		}
		$this->updateMapIdCache($moduleName, $apiId, $yfId);
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
		return \DateTimeField::convertTimeZone($value, \App\Fields\DateTime::getTimeZone(), 'GMT+2')->format('Y-m-d\\TH:i:s');
	}

	/**
	 * Export items to Comarch.
	 *
	 * @return void
	 */
	public function export(): void
	{
		$this->lastScan = $this->config->getLastScan('export' . $this->name);
		if (
			!$this->lastScan['start_date']
			|| (0 === $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		) {
			$this->config->setScan('export' . $this->name);
			$this->lastScan = $this->config->getLastScan('export' . $this->name);
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('Start export ' . $this->name, [
				'lastScan' => $this->lastScan,
			]);
		}
		$i = 0;
		try {
			$page = $this->lastScan['page'] ?? 0;
			$load = true;
			$finish = false;
			$query = $this->getExportQuery();
			$limit = $this->config->get($this::LIMIT_NAME);
			while ($load) {
				$query->offset($page);
				if ($rows = $query->all()) {
					foreach ($rows as $row) {
						$this->exportItem($row['id']);
						$this->config->setScan('export' . $this->name, 'id', $row['id']);
						++$i;
					}
					++$page;
					if (\is_callable($this->controller->bathCallback)) {
						$load = \call_user_func($this->controller->bathCallback, 'export' . $this->name);
					}
					if ($limit !== \count($rows)) {
						$finish = true;
					}
				} else {
					$finish = true;
				}
				if ($finish || !$load) {
					$load = false;
					if ($finish) {
						$this->config->setEndScan('export' . $this->name, $this->lastScan['start_date']);
					} else {
						$this->config->setScan('export' . $this->name, 'page', $page);
					}
				}
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Export ' . $this->name, ['API' => $rows ?? ''], $ex);
			\App\Log::error("Error during export {$this->name}: \n{$ex->__toString()}", $this::LOG_CATEGORY);
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('End export ' . $this->name, ['exported' => $i]);
		}
	}

	/**
	 * Export item to Comarch from YetiFoce.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function exportItem(int $id): bool
	{
		$mapModel = $this->getMapModel();
		$mapModel->setDataApi([]);
		$mapModel->setDataYfById($id);
		$mapModel->loadModeApi();
		$row = $mapModel->getDataYf('fieldMap', false);
		$dataApi = $mapModel->getDataApi();
		if ($mapModel->skip) {
			if ($this->config->get('log_all')) {
				$this->controller->log($this->name . ' ' . __FUNCTION__ . ' | skipped , inconsistent data', ['YF' => $row, 'API' => $dataApi ?? []]);
			}
		} elseif (empty($dataApi)) {
			\App\Log::error(__FUNCTION__ . ' | Empty map details', $this::LOG_CATEGORY);
			$this->controller->log($this->name . ' ' . __FUNCTION__ . ' | Empty map details', ['YF' => $row, 'API' => $dataApi ?? []], null, true);
		} else {
			try {
				if ('create' === $mapModel->getModeApi() || empty($this->imported[$row[$mapModel::FIELD_NAME_ID]])) {
					$mapModel->saveInApi();
					$dataApi = $mapModel->getDataApi(false);
					$this->exported[$id] = $mapModel->getRecordModel()->get($mapModel::FIELD_NAME_ID);
				} else {
					$this->updateMapIdCache(
						$mapModel->getRecordModel()->getModuleName(),
						$mapModel->getRecordModel()->get($mapModel::FIELD_NAME_ID),
						$id
					);
				}
				$status = true;
			} catch (\Throwable $ex) {
				$this->controller->log($this->name . ' ' . __FUNCTION__, ['YF' => $row, 'API' => $dataApi], $ex);
				\App\Log::error('Error during ' . __FUNCTION__ . ': ' . PHP_EOL . $ex->__toString(), $this::LOG_CATEGORY);
				$this->addToQueue('export', $id);
			}
		}
		if ($this->config->get('log_all')) {
			$this->controller->log(
				$this->name . ' ' . __FUNCTION__ . ' | ' .
				(\array_key_exists($id, $this->exported) ? 'exported' : 'skipped'),
				[
					'YF' => $row,
					'API' => $dataApi ?? [],
				]
			);
		}
		return $status ?? false;
	}

	/**
	 * Import account from Comarch to YetiFoce.
	 *
	 * @param array $row
	 *
	 * @return bool
	 */
	public function importItem(array $row): bool
	{
		$mapModel = $this->getMapModel();
		$mapModel->setDataApi($row);
		$apiId = $row[$mapModel::API_NAME_ID];
		if ($dataYf = $mapModel->getDataYf()) {
			try {
				$yfId = $mapModel->findRecordInYf();
				if (empty($yfId) || empty($this->exported[$yfId])) {
					$mapModel->loadRecordModel($yfId);
					$mapModel->loadAdditionalData();
					$mapModel->saveInYf();
					$dataYf['id'] = $this->imported[$apiId] = $mapModel->getRecordModel()->getId();
				}
				if (!empty($apiId)) {
					$this->updateMapIdCache(
						$mapModel->getModule(),
						$apiId,
						$yfId ?: $mapModel->getRecordModel()->getId()
					);
				}
				$status = true;
			} catch (\Throwable $ex) {
				$this->controller->log($this->name . ' ' . __FUNCTION__, ['YF' => $dataYf, 'API' => $row], $ex);
				\App\Log::error('Error during ' . __FUNCTION__ . ': ' . PHP_EOL . $ex->__toString(), $this::LOG_CATEGORY);
				$this->addToQueue('import', $apiId);
			}
		} else {
			\App\Log::error('Empty map details in ' . __FUNCTION__, $this::LOG_CATEGORY);
		}
		if ($this->config->get('log_all')) {
			$this->controller->log($this->name . ' ' . __FUNCTION__ . ' | ' .
			 (\array_key_exists($apiId, $this->imported) ? 'imported' : 'skipped'), [
			 	'API' => $row,
			 	'YF' => $dataYf ?? [],
			 ]);
		}
		return $status ?? false;
	}

	/**
	 * Import by API id.
	 *
	 * @param int $apiId
	 *
	 * @return int
	 */
	public function importById(int $apiId): int
	{
		throw new \App\Exceptions\AppException('Function not implemented');
	}

	/**
	 * Add import/export jobs to the queue.
	 *
	 * @param string $type
	 * @param int    $id
	 *
	 * @return void
	 */
	public function addToQueue(string $type, int $id): void
	{
		$data = ['server_id' => $this->config->get('id'),
			'name' => $this->name, 'type' => $type,	'value' => $id,
		];
		$db = \App\Db::getInstance('admin');
		if ((new \App\Db\Query())->from(\App\Integrations\Comarch::QUEUE_TABLE_NAME)
			->where(['server_id' => $this->config->get('id'), 'name' => $this->name, 'type' => $type])->exists($db)) {
			return;
		}
		$db->createCommand()->insert(\App\Integrations\Comarch::QUEUE_TABLE_NAME, $data)->execute();
	}

	/**
	 * Run import/export jobs from the queue.
	 *
	 * @param string $type
	 *
	 * @return void
	 */
	public function runQueue(string $type): void
	{
		$db = \App\Db::getInstance('admin');
		$dataReader = (new \App\Db\Query())->from(\App\Integrations\Comarch::QUEUE_TABLE_NAME)
			->where(['server_id' => $this->config->get('id'), 'name' => $this->name, 'type' => $type])
			->createCommand(\App\Db::getInstance('admin'))->query();
		while ($row = $dataReader->read()) {
			switch ($type) {
				case 'export':
					$status = $this->exportItem($row['value']);
					break;
				case 'import':
					$status = empty($this->importById($row['value']));
					break;
				default:
					break;
			}
			$delete = false;
			if ($status) {
				$delete = true;
			} else {
				$counter = ((int) $row['counter']) + 1;
				if (4 === $counter) {
					$delete = true;
				} else {
					$db->createCommand()->update(
						\App\Integrations\Comarch::QUEUE_TABLE_NAME,
						['counter' => $counter],
						['id' => $row['id']]
					)->execute();
				}
			}
			if ($delete) {
				$db->createCommand()->delete(
					\App\Integrations\Comarch::QUEUE_TABLE_NAME,
					['id' => $row['id']]
				)->execute();
			}
		}
	}

	/**
	 * Get export query.
	 *
	 * @return \App\Db\Query
	 */
	protected function getExportQuery(): \App\Db\Query
	{
		$queryGenerator = $this->getFromYf($this->getMapModel()->getModule(), true);
		$queryGenerator->setLimit($this->config->get($this::LIMIT_NAME));
		return $queryGenerator->createQuery();
	}
}
