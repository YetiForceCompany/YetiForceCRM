<?php

/**
 * Comarch accounts synchronization file.
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

namespace App\Integrations\Comarch\Xl\Synchronizer;

/**
 * Comarch accounts synchronization class.
 */
class Products extends \App\Integrations\Comarch\Synchronizer
{
	/** @var string The name of the configuration parameter for rows limit */
	const LIMIT_NAME = 'products_limit';

	/** {@inheritdoc} */
	public function process(): void
	{
		$mapModel = $this->getMapModel();
		if (\App\Module::isModuleActive($mapModel->getModule())) {
			$direction = (int) $this->config->get('direction_products');
			if ($this->config->get('master')) {
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_YF_TO_API === $direction) {
					$this->runQueue('export');
					$this->export();
				}
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_API_TO_YF === $direction) {
					$this->runQueue('import');
					$this->import();
				}
			} else {
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_API_TO_YF === $direction) {
					$this->runQueue('import');
					$this->import();
				}
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_YF_TO_API === $direction) {
					$this->runQueue('export');
					$this->export();
				}
			}
		}
	}

	/**
	 * Import accounts from Comarch.
	 *
	 * @return void
	 */
	public function import(): void
	{
		$this->lastScan = $this->config->getLastScan('import' . $this->name);
		if (
			!$this->lastScan['start_date']
			|| (0 === $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		) {
			$this->config->setScan('import' . $this->name);
			$this->lastScan = $this->config->getLastScan('import' . $this->name);
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('Start import ' . $this->name, [
				'lastScan' => $this->lastScan,
			]);
		}
		$i = 0;
		try {
			$page = $this->lastScan['page'] ?? 1;
			$load = true;
			$finish = false;
			$limit = $this->config->get(self::LIMIT_NAME);
			while ($load) {
				if ($rows = $this->getFromApi('Product/GetFiltered?&page=' . $page . '&' . $this->getFromApiCond())) {
					foreach ($rows as $id => $row) {
						$this->importItem($row);
						$this->config->setScan('import' . $this->name, 'id', $id);
						++$i;
					}
					++$page;
					if (\is_callable($this->controller->bathCallback)) {
						$load = \call_user_func($this->controller->bathCallback, 'import' . $this->name);
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
						$this->config->setEndScan('import' . $this->name, $this->lastScan['start_date']);
					} else {
						$this->config->setScan('import' . $this->name, 'page', $page);
					}
				}
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Import ' . $this->name, null, $ex);
			\App\Log::error("Error during import {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('End import ' . $this->name, ['imported' => $i]);
		}
	}

	/** {@inheritdoc} */
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
						$mapModel->getModule(), $apiId,
						$yfId ?: $mapModel->getRecordModel()->getId()
					);
				}
				$status = true;
			} catch (\Throwable $ex) {
				$this->controller->log($this->name . ' ' . __FUNCTION__, ['YF' => $dataYf, 'API' => $row], $ex);
				\App\Log::error('Error during ' . __FUNCTION__ . ': ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
				$this->addToQueue('import', $apiId);
			}
		} else {
			\App\Log::error('Empty map details in ' . __FUNCTION__, self::LOG_CATEGORY);
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

	/** {@inheritdoc} */
	public function importById(int $apiId): int
	{
		$id = 0;
		try {
			$row = $this->getFromApi('Product/GetById/' . $apiId);
			if ($row) {
				$this->importItem($row);
				$mapModel = $this->getMapModel();
				$id = $this->imported[$row[$mapModel::API_NAME_ID]] ?? 0;
			} else {
				$this->controller->log("Import {$this->name} by id [Empty details]", ['apiId' => $apiId]);
				\App\Log::error("Import during export {$this->name}: Empty details", self::LOG_CATEGORY);
			}
		} catch (\Throwable $ex) {
			$this->controller->log("Import {$this->name} by id", ['apiId' => $apiId, 'API' => $row], $ex);
			\App\Log::error("Error during import by id {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
		}
		return $id;
	}

	// {@inheritdoc}
	// public function getApiId(int $yfId, ?string $moduleName = null): int
	// {
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
		// return $apiId;
	// }
}
